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

    public function exec(\Ulrichsg\Getopt $getopt, $value, array $operands)
    {
        if (isset($value)) {
            displayHeader();

            echo "Processors:\n";
            $this->getDetails(__DIR__."/../Processors");
            
            echo "\nURI Resolvers:\n";
            $this->getDetails(__DIR__."/../UriResolvers");
            return false;
        }
    }
    
    private function getDetails($basePath)
    {
        $classes = \RPI\Utilities\ContentBuild\Lib\Helpers\FileUtils::find($basePath, "*.php");
        $classes = array_keys($classes);
        asort($classes);
        
        foreach($classes as $class) {
            $className = \RPI\Utilities\ContentBuild\Autoload::getClassName($class);
            echo "    $className: ".$className::getVersion()."\n";
        }
    }
}
