<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Processor;

interface IProcessor
{
    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    );
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $debugPath,
        $buffer
    );
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        $inputFilename,
        $buffer
    );
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    );
    
    public function getOptions();
}
