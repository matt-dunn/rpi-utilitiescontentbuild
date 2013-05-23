<?php

namespace RPI\Utilities\ContentBuild\Processors;

class SASS implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.3";

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
    }
    
    public static function getVersion()
    {
        $output = \RPI\Console\Helpers\Console::run(
            "sass",
            "-v"
        );
        
        return "v".self::VERSION.(is_array($output) && count($output) > 0 ? " - {$output[0]}" : null);
    }
    
    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        $processorIndex
    ) {
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $buffer
    ) {
        return $buffer;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $buffer
    ) {
        if (pathinfo($inputFilename, PATHINFO_EXTENSION) == "scss") {
            $this->project->getLogger()->info("Compiling SASS '$inputFilename'");
            
            $cachepath = dirname($this->project->configurationFile)."/.sass-cache";
            \RPI\Console\Helpers\Console::run("sass", "--update $inputFilename --cache-location $cachepath");
            
            $buffer = file_get_contents(str_replace(".scss", ".css", $inputFilename));
        }
        
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor
    ) {
        
    }
}
