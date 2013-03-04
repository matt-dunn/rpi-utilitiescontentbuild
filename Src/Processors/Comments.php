<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Comments implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    public function getOptions()
    {
        return null;
    }

    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $debugPath,
        $buffer
    ) {
        $buffer = preg_replace_callback(
            "/\/\*.*?\*\//sim",
            function ($matches) {
                return "";            
            },
            $buffer
        );
            
        return $buffer;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        $inputFilename,
        $outputFilename,
        $debugPath,
        $buffer
    ) {
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
        
    }
}
