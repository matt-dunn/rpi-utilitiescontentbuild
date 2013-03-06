<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Comments implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    private $hasProcessed = false;
    
    public function getOptions()
    {
        return null;
    }

    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $processorIndex
    ) {
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
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
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
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
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
        
    }
}
