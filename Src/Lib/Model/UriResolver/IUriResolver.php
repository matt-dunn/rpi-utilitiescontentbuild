<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\UriResolver;

interface IUriResolver extends \RPI\Utilities\ContentBuild\Lib\Model\IPlugin
{
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
