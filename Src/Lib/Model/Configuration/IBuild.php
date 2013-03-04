<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Configuration;

interface IBuild
{
    /**
     * @return string
     */
    public function getBuildDirectory();
    
    /**
     * @return string
     */
    public function getOutputDirectory();
    
    /**
     * @return string
     */
    public function getType();
    
    /**
     * @return string
     */
    public function getVersion();
    
    /**
     * @return string
     */
    public function getName();
    
    /**
     * @return array
     */
    public function getFiles();
}
