<?php

namespace RPI\Utilities\ContentBuild\Lib;

class Processor
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\IProcessor[]
     */
    private $processors = array();
    
    private $configurationPath = null;
    
    private $metadataFilename = null;
    
    /**
     *
     * @var array
     */
    private $metadata = null;
    
    public function __construct($configurationPath)
    {
        $this->configurationPath = $configurationPath;
        
        $this->metadataFilename = $this->configurationPath."/build/metadata";
        if (file_exists($this->metadataFilename)) {
            $this->metadata = unserialize(file_get_contents($this->metadataFilename));
        }
    }
    
    public function __destruct()
    {
        if (isset($this->metadata)) {
            if (!file_exists(dirname($this->metadataFilename))) {
                $oldumask = umask(0);
                mkdir(dirname($this->metadataFilename), 0755, true);
                umask($oldumask);
            }

            file_put_contents(
                $this->metadataFilename,
                serialize($this->metadata)
            );
        }
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
    
    public function init(\RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project)
    {
        foreach($this->processors as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Init '".get_class($processor)."'", LOG_DEBUG);
            $processor->init($this, $project);
        }
        
        return $this;
    }
    
    public function preProcess(\RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build, $inputFilename, $outputFilename, $debugPath, $buffer)
    {
        foreach($this->processors as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Preprocess '".get_class($processor)."'", LOG_DEBUG);
            $buffer = $processor->preProcess($this, $build, $inputFilename, $outputFilename, $debugPath, $buffer);
        }
        
        return $buffer;
    }
    
    public function process($inputFilename, $outputFilename, $debugPath, $buffer)
    {
        foreach($this->processors as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Process '".get_class($processor)."'", LOG_DEBUG);
            $buffer = $processor->process($this, $inputFilename, $outputFilename, $debugPath, $buffer);
        }
        
        return $buffer;
    }
    
    public function complete(\RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project)
    {
        foreach($this->processors as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Complete '".get_class($processor)."'", LOG_DEBUG);
            $processor->complete($this, $project);
        }
        
        return $this;
    }
    
    public function getMetadata($name)
    {
        if (isset($this->metadata, $this->metadata[$name])) {
            return $this->metadata[$name];
        }
        
        return false;
    }

    public function setMetadata($name, $value)
    {
        if (!isset($this->metadata)) {
            $this->metadata = array();
        }
        
        $this->metadata[$name] = $value;
    }
}
