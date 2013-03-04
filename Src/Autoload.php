<?php

namespace RPI\Utilities\ContentBuild;

class Autoload
{
    private static function autoload($className)
    {
        $classPath = self::getClassPath($className);
        // Do nothing if the file does not exist to allow class_exists etc. to work as expected
        if (file_exists($classPath)) {
            require($classPath);
        }
    }
    
    public static function getClassPath($className)
    {
        $classPostfix = str_replace("\\", DIRECTORY_SEPARATOR, str_replace(__NAMESPACE__, "", $className));
        return __DIR__.$classPostfix.".php";
    }

    public static function init()
    {
        spl_autoload_register("\\".__NAMESPACE__.'\\'."Autoload"."::autoload");
    }
}
