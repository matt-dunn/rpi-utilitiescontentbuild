<?php

namespace RPI\Utilities\ContentBuild\Lib;

use \RPI\Foundation\Helpers\Object;

/**
 * @property-read \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
 */
class Configuration extends Object
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger = null;
    
    /**
     *
     * @var string
     */
    protected $configurationFile = null;
    
    /**
     *
     * @var array
     */
    protected $options = null;
    
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $configurationFile
     * @param array $options
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        $configurationFile,
        array $options = null
    ) {
        $this->logger = $logger;
        $this->configurationFile = $configurationFile;
        $this->options = $options;
    }
    
    /**
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    public function getProject()
    {
        if (!isset($this->project)) {
            $configType = ucfirst(pathinfo($this->configurationFile, PATHINFO_EXTENSION));
            
            if ($configType === "") {
                throw new \RPI\Foundation\Exceptions\RuntimeException("Invalid config file");
            }
            
            $projectClass = __NAMESPACE__."\Configuration\\".$configType."\Project";
            
            if (!class_exists($projectClass)) {
                throw new \RPI\Foundation\Exceptions\RuntimeException(
                    "Configuration files of type '$configType' are not supported"
                );
            }
            
            $this->project = new $projectClass(
                $this->logger,
                $this->configurationFile,
                $this->options
            );
        }
        
        return $this->project;
    }
}
