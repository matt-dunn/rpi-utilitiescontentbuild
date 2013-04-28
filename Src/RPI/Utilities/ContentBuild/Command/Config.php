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

    public function exec(\Ulrichsg\Getopt $getopt, $configurationFile, array $operands)
    {
        if (!isset($configurationFile) && isset($operands[0])) {
            $configurationFile = $operands[0];
        }

        if (!isset($configurationFile) || $configurationFile == "") {
            $configurationFile = getcwd()."/"."ui.build.xml";
        }

        if (!is_file($configurationFile) && file_exists($configurationFile."/"."ui.build.xml")) {
            $configurationFile = $configurationFile."/"."ui.build.xml";
        }
        
        if (!file_exists($configurationFile)) {
            if (isset($configurationFile) && $configurationFile != "") {
                echo "Configuration file '$configurationFile' not found\n";
            } else {
                echo "Configuration file not found\n";
            }
            return false;
        }

        return array("configurationFile" => realpath($configurationFile));
    }
}
