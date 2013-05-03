<?php

namespace RPI\Utilities\ContentBuild\Lib;

class Build
{
    const COMPRESSOR_JAR = "yuicompressor-2.4.7.jar";
    const MAX_CSS_IMPORTS = 30;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor
     */
    private $processor = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\UriResolver
     */
    private $resolver = null;
    
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
     * @var array
     */
    private $buildFiles = array();
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    private $project = null;
    
    /**
     *
     * @var boolean
     */
    private $includeDebug = true;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    private $logger = null;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        array $options = null
    ) {
        $this->logger = $logger;
        $this->project = $project;
        $this->configurationFile = realpath($this->project->configurationFile);
        $this->processor = $processor;
        $this->resolver = $resolver;
        
        if (isset($options["debug-include"])) {
            $this->includeDebug = $options["debug-include"];
        }
        
        $this->yuicompressorLocation = __DIR__."/../../../../../vendor/yui/yuicompressor/build/".self::COMPRESSOR_JAR;
        if (!file_exists($this->yuicompressorLocation)) {
            throw new \Exception("Unable to find yuicompressor (".$this->yuicompressorLocation.")");
        }
    }
    
    public function run()
    {
        $cleanupYuiCompressor = false;
        
        $this->logger->info(
            "Config read from '{$this->configurationFile}'"
        );
                
        if (\Phar::running() !== "") {
            $this->logger->notice("Extracting yuicompressor");
            $tempYuiCompressorLocation = sys_get_temp_dir()."/".self::COMPRESSOR_JAR;
            copy($this->yuicompressorLocation, $tempYuiCompressorLocation);
            $this->yuicompressorLocation = $tempYuiCompressorLocation;
            $cleanupYuiCompressor = true;
        }
        
        $this->webroot = realpath($this->project->basePath."/".$this->project->appRoot);

        // Ensure the following processors are always run:
        if (!isset($this->processor->processors["RPI\Utilities\ContentBuild\Processors\Comments"])) {
            $this->processor->add(new \RPI\Utilities\ContentBuild\Processors\Comments($this->project));
        }
        
        if (!isset($this->processor->processors["RPI\Utilities\ContentBuild\Processors\Images"])) {
            $this->processor->add(new \RPI\Utilities\ContentBuild\Processors\Images($this->project));
        }
        
        foreach ($this->project->builds as $build) {
            $this->buildDependencies($this->project, $build);
        }

        $this->processor->init();
        
        foreach ($this->project->builds as $build) {
            $outputFilename =
                $this->project->basePath."/".
                $this->project->appRoot."/".$build->outputDirectory.
                $this->project->prefix.".".
                $this->project->name."-".
                $build->name.".".
                $build->type;
            
            $this->processBuild($this->project, $build, $outputFilename);
        }
        
        $this->processor->complete();
        
        if ($cleanupYuiCompressor) {
            $this->logger->debug(
                "Deleting  yuicompressor '{$this->yuicompressorLocation}'"
            );
            unlink($this->yuicompressorLocation);
        }
    }
    
    private function processBuild(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $outputFilename
    ) {
        if (is_file($outputFilename)) {
            unlink($outputFilename);
        }

        $debugPath = $build->debugPath;
        if (isset($debugPath) && !file_exists($debugPath)) {
            $this->logger->debug("Creating debug path: ".$debugPath);
            $oldumask = umask(0);
            mkdir($debugPath, 0755, true);
            umask($oldumask);
        }

        $files = $this->buildFiles[$build->name."_".$build->type];

        if (count($files) == 0) {
            $this->logger->warning(
                "[".$build->name."] No dependencies found -
                    check if external dependencies have already loaded all specified resources"
            );
        }

        foreach ($files as $file) {
            $this->logger->notice(
                "Processing: [".$build->name."] ".$file." => ".$outputFilename
            );

            if (!file_exists(pathinfo($outputFilename, PATHINFO_DIRNAME))) {
                $this->logger->debug(
                    "creating path: ".pathinfo($outputFilename, PATHINFO_DIRNAME)
                );
                $oldumask = umask(0);
                mkdir(pathinfo($outputFilename, PATHINFO_DIRNAME), 0755, true);
                umask($oldumask);
            }

            $buffer = $this->processor->build(
                $build,
                $this->resolver,
                $file,
                $outputFilename,
                file_get_contents($file)
            );
            
            file_put_contents($outputFilename, $buffer, FILE_APPEND);
        }

        $this->writeIncludeFile(
            $build,
            dirname($outputFilename),
            self::makeRelativePath(
                dirname($outputFilename),
                realpath($this->webroot)
            )."/".pathinfo($outputFilename, PATHINFO_FILENAME)."-min.".pathinfo($outputFilename, PATHINFO_EXTENSION),
            $outputFilename
        );
        
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
            
            $this->writeIncludeFile(
                $build,
                $build->debugPath,
                self::makeRelativePath(
                    $build->debugPath,
                    realpath($this->webroot)
                )."/".pathinfo($outputFilename, PATHINFO_FILENAME)."-min.".pathinfo($outputFilename, PATHINFO_EXTENSION)
            );
        }
        
        $parts = pathinfo($outputFilename);
        if (!isset($build->outputFilename)) {
            $outputMiniFilename = $parts["dirname"]."/".$parts["filename"]."-min.".$parts["extension"];
        } else {
            $outputMiniFilename = $parts["dirname"]."/".$build->outputFilename;
        }
        $this->miniFile($outputFilename, $build->type, $outputMiniFilename);
    }
    
    private function writeIncludeFile(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $outputPath,
        $fileSource,
        $outputFilename = null
    ) {
        static $processedPaths = array();

        $target = $build->target;
        if (!isset($target)) {
            $target = "head-all";
        }
        
        $outputTargetFilename = $outputPath."/$target.html";
        
        if (!isset($processedPaths[$outputTargetFilename]) && file_exists($outputTargetFilename)) {
            unlink($outputTargetFilename);
        }
 
        if (isset($outputFilename)) {
            $fileSource .= "?".hash_file("md5", $outputFilename);
        }
        
        $html = null;
        if ($build->type == "css") {
            $media = "";
            if (isset($build->media)) {
                $media = " media=\"{$build->media}\"";
            }
            
            $html = <<<EOT
<link rel="stylesheet" type="text/css" href="{$fileSource}"{$media} />\r\n
EOT;
        } elseif ($build->type == "js") {
            $html = <<<EOT
<script type="text/javascript" src="{$fileSource}"> </script>\r\n
EOT;
        }
        
        if (isset($html)) {
            file_put_contents($outputTargetFilename, $html, FILE_APPEND);
            $processedPaths[$outputTargetFilename] = true;
        }
    }
    
    private function buildDependencies(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build
    ) {
        if (isset($build->files)) {
            foreach ($build->files as $file) {
                $dependentFiles = array();
                $this->buildFileList(
                    $project,
                    $build,
                    $this->getInputFileName($project, $build, $file),
                    $dependentFiles
                );
            }
        }
    }
    
    private function getInputFileName(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $file,
        $basePath = null
    ) {
        $realpath = $this->resolver->realpath($project, $file);
        if ($realpath === false) {
            $realpath = realpath((isset($basePath) ? $basePath : $project->basePath)."/".$build->buildDirectory.$file);
        }
        
        if (!self::fileExists($realpath)) {
            throw new \Exception("Unable to locate input file '$file'");
        }
        return $realpath;
    }
    
    private function getDependencyFileType($inputFilename)
    {
        $filesSearch = \RPI\Foundation\Helpers\FileUtils::find(
            dirname($inputFilename),
            "*".pathinfo($inputFilename, PATHINFO_FILENAME).".dependencies.*",
            null,
            false
        );
        
        $type = null;
        $files = array_keys($filesSearch);
        $dependenciesFile = reset($files);
        if ($dependenciesFile !== false) {
            $type = pathinfo($dependenciesFile, PATHINFO_EXTENSION);
        }
        
        return $type;
    }
    
    private function buildFileList(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $dependentFiles
    ) {
        if (!self::fileExists($inputFilename)) {
            throw new \Exception("Unable to locate input file '$inputFilename'");
        }
        $this->logger->notice(
            "Building dependencies: [".$build->name."] ".$inputFilename
        );

        $type = pathinfo($inputFilename, PATHINFO_EXTENSION);
        if (!isset($type) || $type === false || $type == "") {
            $type = $build->type;
        }
        $buildType = $build->name."_".$type;

        if (!isset($this->buildFiles[$buildType])) {
            $this->buildFiles[$buildType] = Array();
        }

        $dependenciesFileType = $this->getDependencyFileType($inputFilename);
        if (isset($dependenciesFileType)) {
            $dependenciesFile =
                dirname($inputFilename)."/".pathinfo($inputFilename, PATHINFO_FILENAME).
                ".dependencies.".$dependenciesFileType;
            if (file_exists($dependenciesFile)) {
                $this->logger->notice(
                    "Found dependencies file: ".$dependenciesFile
                );

                if (array_search($inputFilename, $dependentFiles) !== false) {
                    $dependenciesFile = dirname(
                        end($dependentFiles)
                    )."/".pathinfo(end($dependentFiles), PATHINFO_FILENAME).".dependencies.".$dependenciesFileType;
                    throw new \Exception(
                        "Circular reference detected in [".$build->name."] - Problem file: ".
                        $inputFilename." in ".$dependenciesFile
                    );
                }

                array_push($dependentFiles, $inputFilename);

                $dependencyClassname =
                    "\\RPI\\Utilities\\ContentBuild\\Lib\\Dependencies\\".ucfirst($dependenciesFileType)."\\Dependency";
                if (!class_exists($dependencyClassname)) {
                    throw new \Exception("Dependency type '$dependenciesFileType' not supported");
                }
                
                $dependency = new $dependencyClassname($dependenciesFile);

                $this->logger->debug(
                    "Processing ".count($dependency->files)." dependencies"
                );

                foreach ($dependency->files as $dependency) {
                    $filename = $this->getInputFileName($project, $build, $dependency["name"], dirname($inputFilename));
                    if (file_exists($filename)) {
                        $this->logger->debug(
                            "Found dependency '".$filename."' - ".$inputFilename
                        );
                        $this->buildFileList($project, $build, $filename, $dependentFiles);
                        
                        $buildTypeDependency = null;
                        if (isset($dependency["type"])) {
                            $buildTypeDependency = $build->name."_".$dependency["type"];
                        } else {
                            $buildTypeDependency = $build->name."_".pathinfo($filename, PATHINFO_EXTENSION);
                        }
                        
                        $this->addUniqueFileToList($build, $filename, $buildTypeDependency);
                    } else {
                        throw new \Exception(
                            "Cannot find file '$filename' in '$dependenciesFile'"
                        );
                    }
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
                    $this->logger->warning(
                        "[".$build->name."] Unable to locate external dependency '".$names[$i]."' for ".$filename
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
        $this->logger->debug(
            "Creating JavaScript debug code: ".$outputFilename
        );
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
        $this->logger->debug("Creating CSS debug code: ".$outputFilename);

        $proxyFileScript = dirname(__FILE__)."/../Scripts/Proxy.css.php";
        if (!file_exists($proxyFileScript)) {
            throw new \Exception("Unable to locate proxy script file: ".$proxyFileScript);
        }

        $bootstrap = "<?php\n// Version: ".CONTENT_BUILD_VERSION."\n\n";
        $proxyFile = $outputPath."/proxy.php";
        if (\Phar::running() !== "") {
            $pharname = pathinfo($_SERVER["PHP_SELF"], PATHINFO_FILENAME).".phar";
            $pharPath = realpath($_SERVER["PHP_SELF"]);
            $bootstrap .= <<<EOT
Phar::loadPhar("{$pharPath}", "{$pharname}");
\$GLOBALS["autoloader"] = "phar://{$pharname}/vendor/autoload.php";

EOT;
        } else {
            $scriptPath = realpath($outputPath.self::makeRelativePath(__DIR__."/../../../../../", $outputPath));
            $bootstrap .= <<<EOT
\$GLOBALS["autoloader"] = "{$scriptPath}/vendor/autoload.php";

EOT;
        }
        
        $bootstrap .= <<<EOT
\$GLOBALS["configuration-file"] = "{$project->configurationFile}";
?>
EOT;
        
        file_put_contents($proxyFile, $bootstrap);
        file_put_contents($proxyFile, file_get_contents($proxyFileScript), FILE_APPEND);

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
            $this->logger->notice("Compressing: ".$outputFilename."...");

            $options = "";
//            if (\RPI\Utilities\ContentBuild\Lib\Exception\Handler::getLogLevel() == LOG_DEBUG) {
//                $options = " --verbose";
//            }
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
            $this->logger->debug("Nothing to compress: ".$outputFilename);
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
