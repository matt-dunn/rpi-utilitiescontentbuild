<?php

namespace RPI\Utilities\ContentBuild\Lib;

class Build
{
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
     * @var \RPI\Utilities\ContentBuild\Plugins\Compressor
     */
    private $compressor = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Plugins\DebugWriter
     */
    private $debugWriter = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Plugins\DependencyBuilder 
     */
    private $dependencyBuilder = null;
    
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
    
    /**
     *
     * @var string
     */
    private $webroot = null;
    
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
        
        $this->dependencyBuilder = new \RPI\Utilities\ContentBuild\Plugins\DependencyBuilder($project, $options);
        $this->compressor = new \RPI\Utilities\ContentBuild\Plugins\Compressor($project, $options);
        if ($this->includeDebug) {
            $this->debugWriter = new \RPI\Utilities\ContentBuild\Plugins\DebugWriter($project, $options);
        }
        
        $this->webroot = realpath($this->project->basePath."/".$this->project->appRoot);
    }
    
    public function run()
    {
        $this->logger->info(
            "Config read from '{$this->configurationFile}'"
        );
                
        $buildFiles = $this->dependencyBuilder->build($this->resolver);

        $this->processor->init();
        
        foreach ($this->project->builds as $build) {
            $outputFilename =
                $this->project->basePath."/".
                $this->project->appRoot."/".$build->outputDirectory.
                $this->project->prefix.".".
                $this->project->name."-".
                $build->name.".".
                $build->type;
            
            $this->processBuild(
                $this->project,
                $build,
                $buildFiles,
                $outputFilename
            );
        }
        
        $this->processor->complete();
    }
    
    private function processBuild(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        array $buildFiles,
        $outputFilename
    ) {
        if (isset($buildFiles[$build->name."_".$build->type])) {
            if (is_file($outputFilename)) {
                unlink($outputFilename);
            }
            
            $files = $buildFiles[$build->name."_".$build->type];

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
                \RPI\Foundation\Helpers\FileUtils::makeRelativePath(
                    dirname($outputFilename),
                    realpath($this->webroot)
                )."/".pathinfo($outputFilename, PATHINFO_FILENAME)."-min.".
                pathinfo($outputFilename, PATHINFO_EXTENSION),
                $outputFilename
            );

            if ($this->includeDebug) {
                $this->debugWriter->writeDebugFile($build, $files, $outputFilename, $this->webroot);

                $this->writeIncludeFile(
                    $build,
                    $build->debugPath,
                    \RPI\Foundation\Helpers\FileUtils::makeRelativePath(
                        $build->debugPath,
                        realpath($this->webroot)
                    )."/".pathinfo($outputFilename, PATHINFO_FILENAME)."-min.".
                    pathinfo($outputFilename, PATHINFO_EXTENSION)
                );
            }

            $parts = pathinfo($outputFilename);
            if (!isset($build->outputFilename)) {
                $outputMiniFilename = $parts["dirname"]."/".$parts["filename"]."-min.".$parts["extension"];
            } else {
                $outputMiniFilename = $parts["dirname"]."/".$build->outputFilename;
            }
            $this->compressor->compressFile($outputFilename, $build->type, $outputMiniFilename);
        } else {
            throw new \RPI\Foundation\Exceptions\RuntimeException(
                "No build details found for build '{$build->name}' type '{$build->type}'"
            );
        }
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
        
        $this->logger->debug(
            "Generating include file '$outputTargetFilename'"
        );
            
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
}
