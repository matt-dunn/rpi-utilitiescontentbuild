<?php

namespace RPI\Utilities\ContentBuild\Lib\Model;

interface IPlugin
{
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
     * @param array $options
     */
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    );
    
    /**
     * Return processor version
     * 
     * @return string
     */
    public static function getVersion();
}
