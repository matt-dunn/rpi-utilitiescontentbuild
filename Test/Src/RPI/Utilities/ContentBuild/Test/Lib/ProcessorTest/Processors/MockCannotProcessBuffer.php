<?php

namespace RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors;

class MockCannotProcessBuffer implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.0";

    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor 
     */
    protected $processor = null;
    
    /**
     *
     * @var array
     */
    public $options = null;
    
    public $hasInit = false;
    public $hasPreProcess = false;
    public $hasProcess = false;
    public $hasComplete = false;

    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->processor = $processor;
        $this->project = $project;
        $this->options = $options;
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    public function init(
        $processorIndex
    ) {
        $this->hasInit = true;
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    ) {
        $this->hasPreProcess = true;
        
        return true;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    ) {
        $this->hasProcess = true;
        
        return $buffer;
    }
    
    public function complete()
    {
        $this->hasComplete = true;
    }
    
    public function canProcessBuffer()
    {
        return false;
    }
}
