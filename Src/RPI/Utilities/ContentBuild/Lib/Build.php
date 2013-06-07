<?php

namespace RPI\Utilities\ContentBuild\Lib;

class Build
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor
     */
    protected $processor = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\UriResolver
     */
    protected $resolver = null;
    
    /**
     *
     * @var string
     */
    protected $configurationFile = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Plugin\ICompressor
     */
    protected $compressor = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDebugWriter
     */
    protected $debugWriter = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDependencyBuilder 
     */
    protected $dependencyBuilder = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    /**
     *
     * @var boolean
     */
    protected $includeDebug = true;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger = null;
    
    /**
     *
     * @var string
     */
    protected $webroot = null;
    
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
     * @param \RPI\Utilities\ContentBuild\Lib\Processor $processor
     * @param \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver
     * @param array $options
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Plugin\ICompressor $compressor
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDependencyBuilder $dependencyBuilder
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDebugWriter $debugWriter
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        array $options = null,
        \RPI\Utilities\ContentBuild\Lib\Model\Plugin\ICompressor $compressor = null,
        \RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDependencyBuilder $dependencyBuilder = null,
        \RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDebugWriter $debugWriter = null
    ) {
        $this->logger = $logger;
        $this->project = $project;
        $this->configurationFile = realpath($this->project->configurationFile);
        $this->processor = $processor;
        $this->resolver = $resolver;
        
        if (isset($options["debug-include"])) {
            $this->includeDebug = $options["debug-include"];
        }
        
        if (isset($dependencyBuilder)) {
            $this->dependencyBuilder = $dependencyBuilder;
        } else {
            $this->dependencyBuilder = $this->getPlugin(
                "RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDependencyBuilder",
                "RPI\Utilities\ContentBuild\Plugins\DependencyBuilder",
                $project,
                $processor,
                $options
            );
        }
        
        if (isset($compressor)) {
            $this->compressor = $compressor;
        } else {
            $this->compressor = $this->getPlugin(
                "RPI\Utilities\ContentBuild\Lib\Model\Plugin\ICompressor",
                "RPI\Utilities\ContentBuild\Plugins\YUICompressor",
                $project,
                $processor,
                $options
            );
        }
        
        if (isset($debugWriter)) {
            $this->debugWriter = $debugWriter;
        } elseif ($this->includeDebug) {
            $this->debugWriter = $this->getPlugin(
                "RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDebugWriter",
                "RPI\Utilities\ContentBuild\Plugins\DebugWriter",
                $project,
                $processor,
                $options
            );
        }
        
        $this->webroot = realpath($this->project->basePath."/".$this->project->appRoot);
    }
    
    /**
     * 
     * @param string $type
     * @param string $defaultClass
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
     * @param \RPI\Utilities\ContentBuild\Lib\Processor $processor
     * @param array $options
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Model\IPlugin
     * 
     * @throws \RPI\Foundation\Exceptions\RuntimeException
     */
    protected function getPlugin(
        $type,
        $defaultClass,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        array $options = null
    ) {
        $type = ltrim($type, "\\");
        
        if (isset($this->project->plugins[$type])) {
            $plugin = new $this->project->plugins[
                $type
            ]->type($processor, $project, $options);

            if (!$plugin
                instanceof $type) {
                throw new \RPI\Foundation\Exceptions\RuntimeException(
                    "'{$this->project->plugins[$type]->type}' ".
                    "must be of type '$type'"
                );
            }
            
            return $plugin;
        } else {
            return new $defaultClass(
                $processor,
                $project,
                $options
            );
        }
    }

    /**
     * @return bool
     */
    public function run()
    {
        $startTime = microtime(true);
        
        $this->logger->info(
            "Config read from '{$this->configurationFile}'"
        );
                
        $buildFiles = $this->dependencyBuilder->build($this->resolver);

        $this->processor->init();
        
        foreach ($this->project->builds as $build) {
            $files = $buildFiles[$build->name."_".$build->type];
            foreach ($files as $file) {
                $this->processor->preProcess(
                    $build,
                    $this->resolver,
                    $file,
                    file_get_contents($file)
                );
            }
        }
        
        foreach ($this->project->builds as $build) {
            $this->processBuild(
                $this->project,
                $build,
                $buildFiles
            );
        }
        
        $this->processor->complete();
        
        $this->logger->info("\nBUILD SUCCESSFUL");
        $this->logger->info("Total time: ".round(microtime(true) - $startTime, 1)." seconds");
        
        return true;
    }
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build
     * @param array $buildFiles
     * 
     * @throws \RPI\Foundation\Exceptions\RuntimeException
     */
    protected function processBuild(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        array $buildFiles
    ) {
        $outputFilename = $build->outputFilename;
        
        if (isset($buildFiles[$build->name."_".$build->type])) {
            if (is_file($outputFilename)) {
                unlink($outputFilename);
            }
            
            $files = $buildFiles[$build->name."_".$build->type];

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

                $buffer = $this->processor->process(
                    $build,
                    $this->resolver,
                    $file,
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
                )."/".pathinfo($outputFilename, PATHINFO_FILENAME).".min.".
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
                    )."/".pathinfo($outputFilename, PATHINFO_FILENAME).".min.".
                    pathinfo($outputFilename, PATHINFO_EXTENSION)
                );
            }

            $parts = pathinfo($outputFilename);
            $outputMiniFilename = $parts["dirname"]."/".$parts["filename"].".min.".$parts["extension"];
            
            $this->compressor->compressFile($outputFilename, $build->type, $outputMiniFilename);
        } else {
            throw new \RPI\Foundation\Exceptions\RuntimeException(
                "No build details found for build '{$build->name}' type '{$build->type}'"
            );
        }
    }
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build
     * @param string $outputPath
     * @param string $fileSource
     * @param string $outputFilename
     */
    protected function writeIncludeFile(
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
