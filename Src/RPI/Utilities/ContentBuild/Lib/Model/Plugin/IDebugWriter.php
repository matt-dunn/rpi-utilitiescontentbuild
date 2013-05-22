<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Plugin;

interface IDebugWriter extends \RPI\Utilities\ContentBuild\Lib\Model\IPlugin
{
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build
     * @param array $files
     * @param string $outputFilename
     * @param string $webroot
     * 
     * @return bool
     */
    public function writeDebugFile(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        array $files,
        $outputFilename,
        $webroot
    );
    
}
