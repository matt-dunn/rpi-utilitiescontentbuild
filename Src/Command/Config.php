<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class Config implements \RPI\Utilities\ContentBuild\ICommand
{
    private $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "config",
            "option" => array(
                "c",
                "config",
                Getopt::REQUIRED_ARGUMENT, "Location of the configuration file"
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

    public function exec(\Ulrichsg\Getopt $getopt, $value)
    {
        $configurationFile = $value;
        
        if (!isset($configurationFile)) {
            if (file_exists(getcwd()."/"."ui.build.xml")) {
                $configurationFile = getcwd()."/"."ui.build.xml";
            }
        } else {
            if (!file_exists($configurationFile)) {
                $configurationFile = getcwd()."/".$configurationFile;
            }
        }

        if (!file_exists($configurationFile)) {
            if (isset($configurationFile) && $configurationFile != "") {
                echo "Configuration file '$configurationFile' not found\n";
            } else {
                echo "Configuration file not found\n";
            }
            return false;
        }
        
        return array("configurationFile" => $configurationFile);
    }
}
