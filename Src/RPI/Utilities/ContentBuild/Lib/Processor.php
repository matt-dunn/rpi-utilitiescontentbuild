<?php

namespace RPI\Utilities\ContentBuild\Lib;

use \RPI\Foundation\Helpers\Object;

class Processor extends Object
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor[]
     */
    private $processors = null;
    
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
        
        $this->metadataFilename = dirname($project->configurationFile)."/.metadata";
    }
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor $processor
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Processor
     */
    public function add(\RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor $processor)
    {
        if (!isset($this->processors)) {
            $this->getProcessors();
        }
        
        $this->processors[get_class($processor)] = $processor;
        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
            "Creating '".get_class($processor)."' ({$processor->getVersion()})",
            LOG_INFO
        );
        
        return $this;
    }
    
    /**
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor[]
     */
    public function getProcessors()
    {
        if (!isset($this->processors)) {
            $this->processors = array();
            
            if (isset($this->project->processors)) {
                \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                    "Reading processors from configuration'",
                    LOG_DEBUG
                );
                foreach ($this->project->processors as $processor) {
                    $params = array($this->project);
                    if (isset($processor->params)) {
                        $params = array_merge($params, $processor->params);
                    }
                    $instance = new \ReflectionClass($processor->type);
                    $this->add($instance->newInstanceArgs($params));
                }
            }
        }
        
        return $this->processors;
    }
    
    public function init()
    {
        $index = 0;
        foreach ($this->getProcessors() as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Init '".get_class($processor)."'", LOG_DEBUG);
            $processor->init($this, $index);
            $index++;
        }
        
        return $this;
    }
    
    public function build(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $outputFilename,
        $buffer
    ) {
        foreach ($this->getProcessors() as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Preprocess '".get_class($processor)."'", LOG_DEBUG);
            if ($build->type == "css") {
                $buffer = $processor->preProcess(
                    $this,
                    $resolver,
                    $build,
                    $inputFilename,
                    $outputFilename,
                    $buffer
                );
            
                $buffer = $processor->process($this, $resolver, $inputFilename, $buffer);
            }
        }
        
        return $buffer;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $buffer
    ) {
        foreach ($this->getProcessors() as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Process '".get_class($processor)."'", LOG_DEBUG);
            $buffer = $processor->process($this, $resolver, $inputFilename, $buffer);
        }
        
        return $buffer;
    }
    
    public function complete()
    {
        foreach ($this->getProcessors() as $processor) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Complete '".get_class($processor)."'", LOG_DEBUG);
            $processor->complete($this);
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
