<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Configuration;

interface IProject
{
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
}
