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
    
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
     * @param boolean $debug
     */
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
        
        if (!$processor->canProcessBuffer() && count($this->processors) > 0) {
            throw new \RPI\Foundation\Exceptions\RuntimeException(
                "'".get_class($processor)."' must be configured as the first processor"
            );
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
                    $params = array($this, $this->project);
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
    
    /**
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Processor
     */
    public function init()
    {
        $index = 0;
        foreach ($this->getProcessors() as $processor) {
            $this->logger->debug("Init '".get_class($processor)."'");
            $processor->init($index);
            $index++;
        }
        
        return $this;
    }
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build
     * @param \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver
     * @param string $inputFilename
     * @param string $buffer
     * @param array $skipProcessors
     * 
     * @return boolean
     */
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $buffer,
        array $skipProcessors = null
    ) {
        $buffer = $this->removeComments($buffer);
            
        $inputFilename = realpath($inputFilename);
        
        foreach ($this->getProcessors() as $processor) {
            if (!isset($skipProcessors) || !in_array(get_class($processor), $skipProcessors)) {
                if ($build->type == "css") {
                    $this->logger->debug("Preprocess '$inputFilename'\n   ".get_class($processor));
                    $processor->preProcess(
                        $resolver,
                        $build,
                        $inputFilename,
                        $buffer
                    );
                }
            }
        }
        
        return true;
    }
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build
     * @param \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver
     * @param string $inputFilename
     * @param string $buffer
     * @param array $skipProcessors
     * 
     * @return string
     */
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $buffer,
        array $skipProcessors = null
    ) {
        $buffer = $this->removeComments($buffer);
            
        $inputFilename = realpath($inputFilename);
        
        foreach ($this->getProcessors() as $processor) {
            if (!isset($skipProcessors) || !in_array(get_class($processor), $skipProcessors)) {
                if ($build->type == "css") {
                    $this->logger->debug("Process '$inputFilename'\n   ".get_class($processor));
                    $buffer = $processor->process(
                        $resolver,
                        $build,
                        $inputFilename,
                        $buffer
                    );
                }
            }
        }
        
        return $buffer;
    }
    
    /**
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Processor
     */
    public function complete()
    {
        foreach ($this->getProcessors() as $processor) {
            $this->logger->debug("Complete '".get_class($processor)."'");
            $processor->complete();
        }
        
        return $this;
    }
    
    /**
     * 
     * @param string $name
     * 
     * @return boolean|mixed
     */
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

    /**
     * 
     * @param string $name
     * @param mixed $value
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Processor
     */
    public function setMetadata($name, $value)
    {
        if (!isset($this->metadata)) {
            $this->metadata = array();
        }
        
        $this->metadata[$name] = $value;
        
        if (!file_exists($this->metadataFilename)) {
            touch($this->metadataFilename);
            chmod($this->metadataFilename, 0777);
        }

        file_put_contents(
            $this->metadataFilename,
            serialize($this->metadata)
        );
        
        return $this;
    }
    
    /**
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Processor
     */
    public function deleteMetadata()
    {
        if (file_exists($this->metadataFilename)) {
            unlink($this->metadataFilename);
        }
        
        return $this;
    }
    
    /**
     * Remove block comments from buffer. Comments which open with ! are kept.
     * 
     * @param string $buffer
     * 
     * @return string
     */
    protected function removeComments($buffer)
    {
        return preg_replace_callback(
            "/\/\*.*?\*\//sim",
            function ($matches) {
                if (substr($matches[0], 0, 3) == "/*!") {
                    return $matches[0];
                } else {
                    return str_repeat("\n", substr_count($matches[0], "\n"));
                }
            },
            $buffer
        );
    }
}
