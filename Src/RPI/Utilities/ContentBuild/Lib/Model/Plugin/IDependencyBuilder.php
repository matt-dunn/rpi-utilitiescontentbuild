<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Plugin;

interface IDependencyBuilder extends \RPI\Utilities\ContentBuild\Lib\Model\IPlugin
{
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver
     * 
     * @return array
     */
    public function build(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver
    );
}
