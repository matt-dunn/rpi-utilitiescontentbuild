<?php

namespace RPI\Utilities\ContentBuild\Lib;

use Ulrichsg\Getopt;

class Build
{
    const COMPRESSOR_JAR = "yuicompressor-2.4.7.jar";
    CONST MAX_CSS_IMPORTS = 30;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor
     */
    private $processor = null;
    
    /**
     *
     * @var string
     */
    private $configurationFile = null;
    
    /**
     *
     * @var string
     */
    private $yuicompressorLocation = null;
    
    /**
     *
     * @var string
     */
    private $basePath = null;
    
    /**
     *
     * @var array
     */
    private $buildFiles = array();
    
    /**
     *
     * @var boolean
     */
    private $includeDebug = true;
    
    public function __construct(\RPI\Utilities\ContentBuild\Lib\Processor $processor)
    {
        $this->processor = $processor;
        
        $getopt = new Getopt(
            array(
                array("h", "help", Getopt::NO_ARGUMENT, "Show this help"),
                array("l", "loglevel", Getopt::REQUIRED_ARGUMENT, "Define the log level"),
                array("c", "config", Getopt::REQUIRED_ARGUMENT, "Location of the configuration file")
            )
        );
        
        try {
            $getopt->parse();
        } catch (\UnexpectedValueException $ex) {
            echo $ex->getMessage()."\r\n";
            exit(1);
        }
        
        if ($getopt->getOption("help")) {
            $getopt->showHelp();
            exit;
        }
        
        $logLevel = $getopt->getOption("loglevel");
        if (isset($logLevel)) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::setLogLevel($logLevel);
        }

        $this->configurationFile = $getopt->getOption("config");
        
        $this->yuicompressorLocation = dirname(__FILE__)."/../../vendor/yui/yuicompressor/build/".self::COMPRESSOR_JAR;
        if (!file_exists($this->yuicompressorLocation)) {
            throw new \Exception("Unable to find yuicompressor (".$this->yuicompressorLocation.")");
        }
    }
    
    public function run()
    {
        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::$displayShutdownInformation = true;

        $project = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project($this->configurationFile);
        
        $this->basePath = realpath(dirname($this->configurationFile).$project->basePath);
        $this->webroot = realpath($this->basePath."/".$project->appRoot);

        foreach ($project->builds as $build) {
            $this->buildDependencies($project, $build);
        }
        
        foreach ($project->builds as $build) {
            $outputFilename =
                $this->basePath."/".
                $build->outputDirectory.
                $project->prefix.".".
                $build->version."-".
                $project->name."-".
                $build->name.".".
                $build->type;
            $this->processBuild($project, $build, $outputFilename, $build->outputDirectory);
        }
        
//        var_dump($this->buildFiles);
    }
    
    private function processBuild(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $outputFilename,
        $outputDirectory
    ) {
        if (is_file($outputFilename)) {
            unlink($outputFilename);
        }

        $debugPath = null;
        if ($this->includeDebug) {
            // Debug support
            $debugPath = self::getDebugPath(dirname($outputFilename), $build->type);
            if (!file_exists($debugPath)) {
                \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Creating debug path: ".$debugPath, LOG_DEBUG);
                $oldumask = umask(0);
                mkdir($debugPath, 0755, true);
                umask($oldumask);
            }
        }

        $files = $this->buildFiles[$build->name."_".$build->type];

        if (count($files) == 0) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                "[".$build->name."] No dependencies found -
                    check if external dependencies have already loaded all specified resources",
                LOG_WARNING
            );
        }

        foreach ($files as $file) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                "Processing: [".$build->name."] ".$file." => ".$outputFilename,
                LOG_NOTICE
            );

            if (!file_exists(pathinfo($outputFilename, PATHINFO_DIRNAME))) {
                \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                    "creating path: ".pathinfo($outputFilename, PATHINFO_DIRNAME),
                    LOG_DEBUG
                );
                $oldumask = umask(0);
                mkdir(pathinfo($outputFilename, PATHINFO_DIRNAME), 0755, true);
                umask($oldumask);
            }

            $buffer = file_get_contents($file);
            
            // TODO: process CSS
            $this->processor->process();
            
            file_put_contents($outputFilename, $buffer, FILE_APPEND);
        }

        if ($this->includeDebug) {
            $parts = pathinfo($outputFilename);
            $debugFilename = $parts["dirname"]."/".$parts["filename"]."-min.".$parts["extension"];
            switch ($build->type) {
                case "css":
                    $this->writeDebugFileCss($project, $build, $files, $debugFilename, $debugPath);
                    break;
                case "js":
                    $this->writeDebugFileJs($project, $build, $files, $debugFilename, $debugPath);
                    break;
            }
        }

        $parts = pathinfo($outputFilename);
        if (!isset($build->outputFilename)) {
            $outputMiniFilename = $parts["dirname"]."/".$parts["filename"]."-min.".$parts["extension"];
        } else {
            $outputMiniFilename = $parts["dirname"]."/".$build->outputFilename;
        }
        $this->miniFile($outputFilename, $build->type, $outputMiniFilename);
    }
    
    private function buildDependencies(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build
    ) {
        if (isset($build->files)) {
            foreach ($build->files as $file) {
                $dependentFiles = array();
                $this->buildFileList($build, $this->getInputFileName($project, $build, $file), $dependentFiles);
            }
        }
    }
    
    private function getInputFileName(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $file
    ) {
        if (parse_url($file, PHP_URL_SCHEME) == "http") {
            return $file;
        } else {
            return $this->basePath."/".$build->buildDirectory.$file;
        }
    }
    
    private function buildFileList($build, $inputFilename, $dependentFiles)
    {
        if (!self::fileExists($inputFilename)) {
            throw new \Exception("Unable to locate input file '$inputFilename'");
        }
        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Building dependencies: [".$build->name."] ".$inputFilename, LOG_NOTICE);

        $type = pathinfo($inputFilename, PATHINFO_EXTENSION);
        if (!isset($type) || $type === false || $type == "") {
            $type = $build->type;
        }
        $buildType = $build->name."_".$type;

        if (!isset($this->buildFiles[$buildType])) {
            $this->buildFiles[$buildType] = Array();
        }

        $dependencesFile = dirname($inputFilename)."/".pathinfo($inputFilename, PATHINFO_FILENAME).".dependencies.xml";
        if (file_exists($dependencesFile)) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Found dependencies file: ".$dependencesFile, LOG_NOTICE);

            if (array_search($inputFilename, $dependentFiles) !== false) {
                $dependencesFile = dirname(
                    end($dependentFiles)
                )."/".pathinfo(end($dependentFiles), PATHINFO_FILENAME).".dependencies.xml";
                throw new \Exception(
                    "Circular reference detected in [".$build->name."] - Problem file: ".
                    $inputFilename." in ".$dependencesFile
                );
            }

            array_push($dependentFiles, $inputFilename);

            $dependency = new \RPI\Utilities\ContentBuild\Lib\Dependencies\Xml\Dependency($dependencesFile);
        
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                "Processing ".count($dependency->files)." dependencies",
                LOG_DEBUG
            );

            foreach ($dependency->files as $dependency) {
                $filename = realpath(dirname($inputFilename)."/".$dependency);
                if (file_exists($filename)) {
                    \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                        "Found dependency '".$filename."' - ".$inputFilename,
                        LOG_DEBUG
                    );
                    $this->buildFileList($build, $filename, $dependentFiles);
                    $buildTypeDependency = $build->name."_".pathinfo($filename, PATHINFO_EXTENSION);
                    $this->addUniqueFileToList($build, $filename, $buildTypeDependency);
                } else {
                    throw new \Exception(
                        "Cannot find file '".dirname($inputFilename)."/".$dependency."'"
                    );
                }
            }
        }

        $this->addUniqueFileToList($build, $inputFilename, $buildType);
    }
    
    private function addUniqueFileToList($build, $filename, $buildType)
    {
        if (isset($build->externalDependenciesNames)) {
            $names = explode(",", $build->externalDependenciesNames);
            for ($i = 0; $i < count($names); $i++) {
                if (isset($this->buildFiles[$names[$i]."_".$build->type])) {
                    if (array_search($filename, $this->buildFiles[$names[$i]."_".$build->type]) !== false) {
                        return false;
                    }
                } else {
                    \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                        "[".$build->name."] Unable to locate external dependency '".$names[$i]."' for ".$filename,
                        LOG_WARNING
                    );
                }
            }
        }

        if (array_search($filename, $this->buildFiles[$buildType]) === false) {
            array_push($this->buildFiles[$buildType], $filename);

            return true;
        }
    }
    
    private function writeDebugFileJs(
            \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
            \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
            array $files,
            $outputFilename,
            $outputPath
    ) {
        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Creating JavaScript debug code: ".$outputFilename, LOG_DEBUG);
        $proxyFileScript = dirname(__FILE__)."/../Scripts/Proxy.js.php";
        if (!file_exists($proxyFileScript)) {
            throw new \Exception("Unable to locate proxy script file: ".$proxyFileScript);
        }
        $proxyFile = $outputPath."/proxy.php";
        file_put_contents($proxyFile, file_get_contents($proxyFileScript));

        $jsCode = <<<CONTENT
if(!document.inlineScript) {
    document.inlineScript = [];
}

if(!window._) {
	function _(o) {
		if(o) {
            try {
                o.call(document);
			} catch(ex) {
                document.inlineScript.push(o);
            }
        }
    }
}

if(!window.console) {	// Support for browsers that do not support console
    window.console = { log : function(){}, dir : function(){} };
}
if(!document.prepareScript) {
    document.prepareScript = function(filename) {
		if(!document._scripts) {
            document._scripts = [];
        }
        document._scripts.push(filename);
    }
}
if(!document.importScripts) {
    document.importScripts = function() {
		if(document._scripts.length > 0) {
            load(document._scripts);
        }

        var isBound = false, readyList = [];

		function load(scripts) {
			if(scripts.length > 0) {
                var filename = scripts.splice(0, 1);
                var script = document.createElement("script");
                script.onload = script.onreadystatechange = function() {
                    if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
                        console.log("LOADED SCRIPT: " + this.getAttribute("src"));
						if(window.jQuery && !isBound) {
                            isBound = true;
                            jQuery.fn.extend({
                                ready: function(fn) {
                                    readyList.push(fn);
                                }
                            });
                        }
                        load(scripts);
                    }
                };
                script.setAttribute("type","text/javascript");
                script.setAttribute("src", filename.toString());
                document.getElementsByTagName("head")[0].appendChild(script);
            } else {
				for(var i = 0; i < document.inlineScript.length; i++) {
                    document.inlineScript[i].call(document);
                }
				for(var i = 0; i < readyList.length; i++) {
                    readyList[i].call(document);
                }
}
        }
    }
}
if(!window.onload) {
    window.onload = function() {
        document.importScripts();
    };
}

CONTENT;

        $debugFilename =
            $outputPath."/".pathinfo($outputFilename, PATHINFO_FILENAME).".".
            pathinfo($outputFilename, PATHINFO_EXTENSION);
        if (file_exists($debugFilename)) {
            unlink($debugFilename);
        }

        if (count($files) > 0) {
            $fh = fopen($debugFilename, 'w');
            fwrite($fh, $jsCode);

            $proxyUrl = str_replace("\\", "/", substr(realpath($proxyFile), strlen($this->webroot)));
            for ($i = 0; $i < count($files); $i++) {
                $fullFilename = $files[$i];
                if (parse_url($fullFilename, PHP_URL_SCHEME) == "http") {
                    fwrite($fh, "@import url(\"$proxyUrl?t=css&f=".urlencode($fullFilename)."\");\r\n");
                } else {
                    fwrite(
                        $fh,
                        "document.prepareScript(\"$proxyUrl?t=js&f=".
                        urlencode(
                            self::makeRelativePath(
                                dirname($fullFilename),
                                realpath($this->webroot)
                            )."/".
                            pathinfo(
                                $files[$i],
                                PATHINFO_FILENAME
                            ).".".
                            pathinfo(
                                $files[$i],
                                PATHINFO_EXTENSION
                            )
                        )."\");\r\n"
                    );
                }
            }

            fclose($fh);
        }
    }

    private function writeDebugFileCss(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        array $files,
        $outputFilename,
        $outputPath
    ) {
        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Creating CSS debug code: ".$outputFilename, LOG_DEBUG);

        $proxyFileScript = dirname(__FILE__)."/../Scripts/Proxy.css.php";
        if (!file_exists($proxyFileScript)) {
            throw new \Exception("Unable to locate proxy script file: ".$proxyFileScript);
        }

        $proxyFileScriptProcessCSS = null;//
        //dirname(__FILE__)."/scripts/processCSS.php";
//        if (!file_exists($proxyFileScriptProcessCSS)
//                && \RPI\Utilities\Build\Content\scripts\ProcessCSS::hasPostProcessing()) {
//            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
//                "CSS variables and blocks are not supported for ".$proxyType." (".
//                basename($outputFilename)."). Ensure 'build/scripts/".
//                basename($proxyFileScriptProcessCSS)."' exists.",
//                LOG_ERR
//            );
//        }

        $proxyFile = $outputPath."/proxy.php";
        if (file_exists($proxyFileScriptProcessCSS)) {
            file_put_contents($proxyFile, file_get_contents($proxyFileScriptProcessCSS));
            file_put_contents($proxyFile, "\RPI\Utilities\Build\Content\scripts\ProcessCSS::\$buildPath = \"{$this->buildPath}\";", FILE_APPEND);
            file_put_contents($proxyFile, "?>", FILE_APPEND);
            file_put_contents($proxyFile, file_get_contents($proxyFileScript), FILE_APPEND);
        } else {
            file_put_contents($proxyFile, file_get_contents($proxyFileScript));
        }

        $debugFilename = $outputPath."/".
            pathinfo($outputFilename, PATHINFO_FILENAME).".".
            pathinfo($outputFilename, PATHINFO_EXTENSION);
        $cssfiles = glob(
            dirname($debugFilename)."/".
            pathinfo($debugFilename, PATHINFO_FILENAME)."*.".
            pathinfo($debugFilename, PATHINFO_EXTENSION)
        );
        foreach ($cssfiles as $cssfile) {
            unlink($cssfile);
        }

        // IE does not allow more than 31 @imports in a css file... so we need to break
        // this out into seperate proxy css files...
        if (count($files) > self::MAX_CSS_IMPORTS) {
            $this->writeDebugFileCssProxy($project, $build, $debugFilename, $proxyFile, $outputFilename, $files);
        } elseif (count($files) > 0) {
            $this->writeDebugFileCssIndividual($project, $build, $debugFilename, $proxyFile, $outputFilename, $files);
        }
    }

    private function writeDebugFileCssProxy(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $debugFilename,
        $proxyFile,
        $outputFilename,
        array $files
    ) {
        $fh = fopen($debugFilename, "w");
        $proxyUrl = str_replace("\\", "/", substr(realpath($proxyFile), strlen($this->webroot)));

        fwrite(
            $fh,
            "/* CSS proxy file - the @import statements have been broken down to
                separate files to overcome IE's limitation of a maximum of 31 css files */\r\n"
        );
        $index = 1;
        $filesets = array_chunk($files, self::MAX_CSS_IMPORTS);
        foreach ($filesets as $fileset) {
            $proxyCSSFile = pathinfo($outputFilename, PATHINFO_FILENAME)."_part".
                sprintf("%03d", $index).".".pathinfo($outputFilename, PATHINFO_EXTENSION);
            fwrite($fh, "@import url(\"".$proxyCSSFile."\");\r\n");

            $this->writeDebugFileCssIndividual(
                $project,
                $build,
                dirname($debugFilename)."/".$proxyCSSFile,
                $proxyFile,
                $outputFilename,
                $fileset
            );

            $index++;
        }

        fclose($fh);
    }

    private function writeDebugFileCssIndividual(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $debugFilename,
        $proxyFile,
        $outputFilename,
        array $files
    ) {
        $fh = fopen($debugFilename, "w");

        $proxyUrl = str_replace("\\", "/", substr(realpath($proxyFile), strlen($this->webroot)));
        for ($i = 0; $i < count($files); $i++) {
            $fullFilename = $files[$i];
            if (parse_url($fullFilename, PHP_URL_SCHEME) == "http") {
                fwrite($fh, "@import url(\"$proxyUrl?t=css&f=".urlencode($fullFilename)."\");\r\n");
            } else {
                fwrite(
                    $fh,
                    "@import url(\"$proxyUrl?t=css&f=".
                    urlencode(
                        self::makeRelativePath(
                            dirname($fullFilename),
                            realpath($this->webroot)
                        )."/".pathinfo($files[$i], PATHINFO_FILENAME).".".pathinfo($files[$i], PATHINFO_EXTENSION)
                    )."\");\r\n"
                );
            }
        }

        fclose($fh);
    }

    
    
    
    private function miniFile($filename, $type, $outputFilename)
    {
        if (file_exists($outputFilename)) {
            unlink($outputFilename);
        }
        if (file_exists($filename)) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Compressing: ".$outputFilename."...", LOG_NOTICE);

            $options = "";
            if (\RPI\Utilities\ContentBuild\Lib\Exception\Handler::getLogLevel() == LOG_DEBUG) {
                $options = " --verbose";
            }
            system(
                "java -jar ".$this->yuicompressorLocation.$options." --type ".$type." ".
                $filename." -o ".$outputFilename,
                $ret
            );
            if ($ret != 0) {
                throw new \Exception("ERROR COMPRESSING FILE (returned $ret): ".$filename);
            }
            unlink($filename);
        } else {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Nothing to compress: ".$outputFilename, LOG_DEBUG);
        }
    }
    
    private static function fileExists($uri)
    {
        if (parse_url($uri, PHP_URL_SCHEME) == "http") {
            // Version 4.x supported
            $handle   = curl_init($uri);
            if (false === $handle) {
                return false;
            }
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
            // request as if Firefox
            curl_setopt(
                $handle,
                CURLOPT_HTTPHEADER,
                array(
                    "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1;".
                    " en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15"
                )
            );
            curl_setopt($handle, CURLOPT_NOBODY, true);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
            $connectable = curl_exec($handle);
            $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            return $connectable && ($status == 200 || $status == 304 || $status == 302);
        } else {
            return file_exists($uri);
        }
    }
    
    private static function getDebugPath($outputPath, $buildType)
    {
        if (substr($outputPath, strlen($outputPath) - 1, 1) == "/") {
            $outputPath = substr($outputPath, 0, strlen($outputPath) - 1);
        }
        
        $debugPathParts = explode("/", $outputPath);
        unset($debugPathParts[count($debugPathParts) - 1]);
        return join("/", $debugPathParts)."/__debug/".$buildType;
    }
    
    private static function makeRelativePath($referencePath, $actualPath)
    {
        $ref = explode("/", str_replace("\\", "/", $referencePath));
        $act = explode("/", str_replace("\\", "/", $actualPath));
        if ($ref[0] == "C:") {
            array_splice($ref, 0, 1);
        }
        if ($act[0] == "C:") {
            array_splice($act, 0, 1);
        }

        $relativePathOffset = 0;
        for ($i = 0; $i < count($act) && $i < count($ref); $i++) {
            if ($act[$i] == $ref[$i]) {
                $relativePathOffset++;
            } else {
                break;
            }
        }

        array_splice($ref, 0, $i);
        $relativePathLevel = count($act) - $relativePathOffset;
        $relativePath = "/".str_repeat("../", $relativePathLevel).join("/", $ref);

        return $relativePath;
    }
}
