<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Configuration;

interface IProject
{
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
    );
    
    /**
     * @return boolean
     */
    public function getIncludeDebug();
    
    /**
     * @return string
     */
    public function getConfigurationFile();
    
    /**
     * @return string
     */
    public function getName();
    
    /**
     * @return string
     */
    public function getPrefix();
    
    /**
     * @return string
     */
    public function getAppRoot();
    
    /**
     * @return \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild[]
     */
    public function getBuilds();
    
    /**
     * @return string
     */
    public function getBasePath();
    
    /**
     * @return \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProcessor[]
     */
    public function getProcessors();
    
    /**
     * @return \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IResolver[]
     */
    public function getResolvers();
    
    /**
     * 
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger();
    
    /**
     * 
     * @return boolean
     */
    public function validate();
}
