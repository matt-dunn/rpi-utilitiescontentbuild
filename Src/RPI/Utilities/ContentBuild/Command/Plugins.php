<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class Plugins implements \RPI\Console\ICommand
{
    protected $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "plugins",
            "option" => array(
                "p",
                "plugins",
                Getopt::NO_ARGUMENT, "Display a list of all available plugins"
            )
        );
    }
    
    public function getOption()
    {
        return $this->optionDetails;
    }

    public function getOptionName()
    {
        return $this->optionDetails["name"];
    }

    public function exec(
        \Psr\Log\LoggerInterface $logger,
        \Ulrichsg\Getopt $getopt,
        $value,
        array $operands
    ) {
        if (isset($value)) {
            displayHeader($logger);

            $logger->info("Processors:");
            $this->getDetails($logger, __DIR__."/../Processors");
            
            $logger->info("Resolvers:");
            $this->getDetails($logger, __DIR__."/../UriResolvers");
            
            $logger->info("Other:");
            $this->getDetails($logger, __DIR__."/../Plugins");
            
            return false;
        }
    }
    
    protected function getDetails(\Psr\Log\LoggerInterface $logger, $basePath)
    {
        $classes = \RPI\Foundation\Helpers\FileUtils::find($basePath, "*.php", null, false);
        $classes = array_keys($classes);
        asort($classes);
        
        foreach ($classes as $class) {
            $className = self::getClassName($class);
            $reflectionClass = new \ReflectionClass($className);
            if (in_array("RPI\Utilities\ContentBuild\Lib\Model\IPlugin", class_implements($className))
                && !$reflectionClass->isAbstract()) {
                $logger->info("    $className: ".$className::getVersion());
            }
        }
    }
    
    protected static function getClassName($classPath)
    {
        $fullPath = \RPI\Foundation\Helpers\FileUtils::realPath($classPath);
        $basePath = \RPI\Foundation\Helpers\FileUtils::realPath(__DIR__."/../../../../");
        
        return str_replace(
            ".php",
            "",
            substr(str_replace(DIRECTORY_SEPARATOR, "\\", $fullPath), strlen($basePath))
        );
    }
}
