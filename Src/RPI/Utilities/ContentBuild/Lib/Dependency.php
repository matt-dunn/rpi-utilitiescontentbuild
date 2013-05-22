<?php

namespace RPI\Utilities\ContentBuild\Lib;

use \RPI\Foundation\Helpers\Object;

/**
 * @property-read \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $dependencies
 */
class Dependency extends Object
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\IDependency
     */
    protected $dependencies = null;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger = null;
    
    /**
     *
     * @var string
     */
    protected $filename = null;
    
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $filename
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        $filename
    ) {
        $this->logger = $logger;
        $this->filename = $filename;
    }
    
    /**
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Model\IDependency
     */
    public function getDependencies()
    {
        if (!isset($this->dependencies)) {
            $configType = ucfirst(pathinfo($this->filename, PATHINFO_EXTENSION));
            
            if ($configType === "") {
                throw new \RPI\Foundation\Exceptions\RuntimeException("Invalid config file");
            }
            
            $dependenciesClass = __NAMESPACE__."\Dependencies\\".$configType."\Dependency";
            
            if (!class_exists($dependenciesClass)) {
                throw new \RPI\Foundation\Exceptions\RuntimeException(
                    "Dependency files of type '$configType' are not supported"
                );
            }
            
            $this->dependencies = new $dependenciesClass(
                $this->logger,
                $this->filename
            );
        }
        
        return $this->dependencies;
    }
}
