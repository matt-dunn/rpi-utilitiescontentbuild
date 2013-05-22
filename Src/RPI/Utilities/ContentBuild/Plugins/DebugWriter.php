<?php

namespace RPI\Utilities\ContentBuild\Plugins;

class DebugWriter implements \RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDebugWriter
{
    const VERSION = "2.0.1";
    
    const MAX_CSS_IMPORTS = 30;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
        
        $project->getLogger()->info("Creating '".__CLASS__."' ({$this->getVersion()})");
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build
     * @param array $files
     * @param string $outputFilename
     * 
     * @return bool
     */
    public function writeDebugFile(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        array $files,
        $outputFilename,
        $webroot
    ) {
        $debugPath = $build->debugPath;
        if (isset($debugPath) && !file_exists($debugPath)) {
            $this->project->getLogger()->debug("Creating debug path: ".$debugPath);
            $oldumask = umask(0);
            mkdir($debugPath, 0755, true);
            umask($oldumask);
        }

        $parts = pathinfo($outputFilename);
        $debugFilename = $parts["dirname"]."/".$parts["filename"]."-min.".$parts["extension"];
        switch ($build->type) {
            case "css":
                $this->writeDebugFileCss($build, $files, $debugFilename, $debugPath, $webroot);
                break;
            case "js":
                $this->writeDebugFileJs($build, $files, $debugFilename, $debugPath, $webroot);
                break;
        }
        
        return true;
    }
    
    protected function writeDebugFileJs(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        array $files,
        $outputFilename,
        $outputPath,
        $webroot
    ) {
        $this->project->getLogger()->debug(
            "Creating JavaScript debug code: ".$outputFilename
        );
        $proxyFileScript = __DIR__."/DebugWriter/Proxy.js.php";
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

            $proxyUrl = str_replace("\\", "/", substr(realpath($proxyFile), strlen($webroot)));
            
            foreach ($files as $file) {
                if (parse_url($file, PHP_URL_SCHEME) == "http") {
                    fwrite($fh, "@import url(\"$proxyUrl?t=css&f=".urlencode($file)."\");\r\n");
                } else {
                    fwrite(
                        $fh,
                        "document.prepareScript(\"$proxyUrl?t=js&f=".
                        urlencode(
                            \RPI\Foundation\Helpers\FileUtils::makeRelativePath(
                                dirname($file),
                                realpath($webroot)
                            )."/".
                            pathinfo(
                                $file,
                                PATHINFO_FILENAME
                            ).".".
                            pathinfo(
                                $file,
                                PATHINFO_EXTENSION
                            )
                        )."\");\r\n"
                    );
                }
            }

            fclose($fh);
        }
    }

    protected function writeDebugFileCss(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        array $files,
        $outputFilename,
        $outputPath,
        $webroot
    ) {
        $this->project->getLogger()->debug("Creating CSS debug code: ".$outputFilename);

        $proxyFileScript = dirname(__FILE__)."/DebugWriter/Proxy.css.php";
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
            $scriptPath = realpath(
                $outputPath.\RPI\Foundation\Helpers\FileUtils::makeRelativePath(
                    __DIR__."/../../../../../",
                    $outputPath
                )
            );
            $bootstrap .= <<<EOT
\$GLOBALS["autoloader"] = "{$scriptPath}/vendor/autoload.php";

EOT;
        }
        
        $bootstrap .= <<<EOT
\$GLOBALS["configuration-file"] = "{$this->project->configurationFile}";
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
            $this->writeDebugFileCssProxy($build, $debugFilename, $proxyFile, $outputFilename, $files, $webroot);
        } elseif (count($files) > 0) {
            $this->writeDebugFileCssIndividual($build, $debugFilename, $proxyFile, $outputFilename, $files, $webroot);
        }
    }

    protected function writeDebugFileCssProxy(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $debugFilename,
        $proxyFile,
        $outputFilename,
        array $files,
        $webroot
    ) {
        $fh = fopen($debugFilename, "w");

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
                $build,
                dirname($debugFilename)."/".$proxyCSSFile,
                $proxyFile,
                $outputFilename,
                $fileset,
                $webroot
            );

            $index++;
        }

        fclose($fh);
    }

    protected function writeDebugFileCssIndividual(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $debugFilename,
        $proxyFile,
        $outputFilename,
        array $files,
        $webroot
    ) {
        $fh = fopen($debugFilename, "w");

        $proxyUrl = str_replace("\\", "/", substr(realpath($proxyFile), strlen($webroot)));
        foreach ($files as $file) {
            if (parse_url($file, PHP_URL_SCHEME) == "http") {
                fwrite($fh, "@import url(\"$proxyUrl?t=css&f=".urlencode($file)."\");\r\n");
            } else {
                fwrite(
                    $fh,
                    "@import url(\"$proxyUrl?t=css&f=".
                    urlencode(
                        \RPI\Foundation\Helpers\FileUtils::makeRelativePath(
                            dirname($file),
                            realpath($webroot)
                        )."/".pathinfo($file, PATHINFO_FILENAME).".".pathinfo($file, PATHINFO_EXTENSION)
                    )."\");\r\n"
                );
            }
        }

        fclose($fh);
    }
}
