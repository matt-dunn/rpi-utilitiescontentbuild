<?php

namespace RPI\Utilities\ContentBuild\Processors;

class SCSSPHP extends CompilerBase
{
    const VERSION = "1.0.0";

    public static function getVersion()
    {
        return "v".self::VERSION." - scssphp ".\RPI\Utilities\ContentBuild\Processors\SCSSPHP\ScssCompiler::$VERSION;
    }
    
    protected function getCompiler()
    {
        return new \RPI\Utilities\ContentBuild\Processors\SCSSPHP\ScssCompiler();
    }

    protected function getFileExtension()
    {
        return "scss";
    }
}
