<?php

namespace RPI\Utilities\ContentBuild\Lib\Logger\Handler;

class Stdout implements \RPI\Foundation\App\Logger\Handler\IHandler
{
    /**
     * {@inherit-doc}
     */
    public function log($level, $message, array $context = array(), \Exception $exception = null)
    {
        echo "$message\n";
    }
}
