<?php

namespace RPI\Utilities\ContentBuild\Lib;

use \RPI\Foundation\Helpers\Object;

class Processor extends Object
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor[]
     */
    protected $processors = null;
    
    /**
     *
     * @var string
     */
    protected $metadataFilename = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    /**
     *
     * @var array
     */
    protected $metadata = null;
    
    /**
     *
     * @var boolean
     */
    public $debug = false;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger = null;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $debug = false
    ) {
        $this->logger = $logger;
        $this->project = $project;
        $this->debug = $debug;
        
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
        $this->logger->info(
            "Creating '".get_class($processor)."' ({$processor->getVersion()})"
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
                $this->logger->debug(
                    "Reading processors from configuration '{$this->project->configurationFile}'"
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
            $this->logger->debug("Init '".get_class($processor)."'");
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
            $this->logger->debug("Preprocess '".get_class($processor)."'");
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
            $this->logger->debug("Process '".get_class($processor)."'");
            $buffer = $processor->process($this, $resolver, $inputFilename, $buffer);
        }
        
        return $buffer;
    }
    
    public function complete()
    {
        foreach ($this->getProcessors() as $processor) {
            $this->logger->debug("Complete '".get_class($processor)."'");
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
