<?php

namespace RPI\Utilities\ContentBuild\Processors;

class LESSPHP extends \RPI\Utilities\ContentBuild\Processors\Leafo\ProcessorBase
{
    const VERSION = "1.0.5";

    public static function getVersion()
    {
        return "v".self::VERSION." - lessphp ".
            \RPI\Utilities\ContentBuild\Processors\Leafo\LESSPHP\LessCompiler::$VERSION;
    }

    protected function getCompiler()
    {
        return new \RPI\Utilities\ContentBuild\Processors\Leafo\LESSPHP\LessCompiler();
    }

    protected function getFileExtension()
    {
        return "less";
    }
}
