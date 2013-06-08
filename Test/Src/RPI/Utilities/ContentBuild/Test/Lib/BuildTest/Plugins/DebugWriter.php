<?php

namespace RPI\Utilities\ContentBuild\Test\Lib\BuildTest\Plugins;

class DebugWriter implements \RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDebugWriter
{
    const VERSION = "1.0.0";
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    public function writeDebugFile(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        array $files,
        $outputFilename,
        $webroot
    ) {
        
    }
}
