<?php

namespace RPI\Utilities\ContentBuild\Lib;

class Processor
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\IProcessor[]
     */
    public $processors = array();
    
    public function __construct()
    {
    }
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor $processor
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Processor
     */
    public function add(\RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor $processor)
    {
        $this->processors[] = $processor;
        
        return $this;
    }
    
    /**
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Model\IProcessor[]
     */
    public function getProcessors()
    {
        return $this->processors;
    }
    
    public function process()
    {
        foreach($this->processors as $processor) {
            $processor->process();
        }
    }
}
