<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\UriResolver;

interface IUriResolver
{
    /**
     * Return processor version
     * 
     * @return string
     */
    public function getVersion();
    
    /**
     * @return string
     */
    public function getScheme();
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
     * @param string $uri
     * 
     * @return boolean|string
     */
    public function realpath(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $uri
    );
}
