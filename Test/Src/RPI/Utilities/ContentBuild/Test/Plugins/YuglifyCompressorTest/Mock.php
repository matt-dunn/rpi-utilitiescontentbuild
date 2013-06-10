<?php

namespace RPI\Utilities\ContentBuild\Test\Processors\SASSTest;

class Mock extends \RPI\Utilities\ContentBuild\Processors\SASS
{
    public static $sassCommand = "sass";
    
    protected static function runCommand($command, $arguments, $helpInstallation = null)
    {
        if ($command == self::$sassCommand) {
            if ($arguments == "-v") {
                return array(
                    "SASS MOCK"
                );
            } else {
                return array(
                    "PROCESSED: $arguments"
                );
            }
        }
        
        throw new \RPI\Console\Exceptions\Console\NotInstalled();
    }
}
