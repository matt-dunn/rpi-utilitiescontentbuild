<?php

namespace RPI\Utilities\ContentBuild\Lib;

class Processor
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\IProcessor[]
     */
    private $processors = array();
    
    /**
     *
     * @var string
     */
    private $metadataFilename = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    private $project = null;
    
    /**
     *
     * @var array
     */
    private $metadata = null;
    
    public function __construct(\RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project)
    {
        $this->project = $project;
        
        $this->metadataFilename = dirname($project->configurationFile)."/build/metadata";
        
        if (isset($project->processors)) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Processors configured:'", LOG_INFO);
            foreach ($project->processors as $processor) {
                \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("  Creating '{$processor->type}'", LOG_INFO);
                $instance = new \ReflectionClass($processor->type);
                $constructor = $instance->getConstructor();
                if (isset($constructor)) {
                    $this->add($instance->newInstanceArgs($processor->params));
                } else {
                    $this->add($instance->newInstance());
                }
            }
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
    
    public function init()
    {
        foreach($this->processors as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Init '".get_class($processor)."'", LOG_DEBUG);
            $processor->init($this, $this->project);
        }
        
        return $this;
    }
    
    public function preProcess(\RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build, $inputFilename, $outputFilename, $buffer)
    {
        foreach($this->processors as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Preprocess '".get_class($processor)."'", LOG_DEBUG);
            $buffer = $processor->preProcess($this, $build, $inputFilename, $outputFilename, $buffer);
        }
        
        return $buffer;
    }
    
    public function process($inputFilename, $buffer)
    {
        foreach($this->processors as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Process '".get_class($processor)."'", LOG_DEBUG);
            $buffer = $processor->process($this, $inputFilename, $buffer);
        }
        
        return $buffer;
    }
    
    public function complete()
    {
        foreach($this->processors as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Complete '".get_class($processor)."'", LOG_DEBUG);
            $processor->complete($this, $this->project);
        }
        
        return $this;
    }
    
    public function getMetadata($name)
    {
        if (!isset($this->metadata)) {
            if (file_exists($this->metadataFilename)) {
                $this->metadata = unserialize(file_get_contents($this->metadataFilename));
            } else {
                $this->metadata = false;
            }
        }
        
        if ($this->metadata !== false && isset($this->metadata, $this->metadata[$name])) {
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
