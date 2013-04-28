<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Comments implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    private $hasProcessed = false;
    const VERSION = "1.0.2";

    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
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
        $buffer = preg_replace_callback(
            "/\/\*.*?\*\//sim",
            function ($matches) {
                return "";
            },
            $buffer
        );
            
        $this->hasProcessed = true;
            
        return $buffer;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $buffer
    ) {
        if (!$this->hasProcessed) {
            $buffer = preg_replace_callback(
                "/\/\*.*?\*\//sim",
                function ($matches) {
                    return "";
                },
                $buffer
            );
        }
        
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor
    ) {
        
    }
}
