<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class Extensions implements \RPI\Utilities\ContentBuild\ICommand
{
    private $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "extensions",
            "option" => array(
                "x",
                "extensions",
                Getopt::NO_ARGUMENT, "Display a list of all available extensions"
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
            return false;
        }
    }
    
    private function getDetails(\Psr\Log\LoggerInterface $logger, $basePath)
    {
        $classes = \RPI\Foundation\Helpers\FileUtils::find($basePath, "*.php");
        $classes = array_keys($classes);
        asort($classes);
        
        foreach ($classes as $class) {
            $className = self::getClassName($class);
            $logger->info("    $className: ".$className::getVersion());
        }
    }
    
    private static function getClassName($classPath)
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
