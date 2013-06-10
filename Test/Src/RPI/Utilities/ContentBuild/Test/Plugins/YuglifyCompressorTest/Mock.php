<?php

namespace RPI\Utilities\ContentBuild\Test\Plugins\YuglifyCompressorTest;

class Mock extends \RPI\Utilities\ContentBuild\Plugins\YuglifyCompressor
{
    public static $yuglifyCommand = "yuglify";
    
    protected static function runCommand($command, $arguments, $helpInstallation = null)
    {
        if ($command == self::$yuglifyCommand) {
            if ($arguments == "-v") {
                return array(
                    "YUGLIFY MOCK"
                );
            } else {
                $parts = pathinfo($arguments);
                copy($arguments, $parts["dirname"]."/".$parts["filename"].".min.".$parts["extension"]);
                
                return array(
                    "PROCESSED: $arguments"
                );
            }
        }
        
        throw new \RPI\Console\Exceptions\Console\NotInstalled();
    }
}
