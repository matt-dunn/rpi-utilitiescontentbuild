<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class Config implements \RPI\Console\ICommand
{
    protected $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "config",
            "option" => array(
                "c",
                "config",
                Getopt::REQUIRED_ARGUMENT, "Location of the configuration file <arg>. ".
                "Defaults to a file called 'ui.build.xml' in the current directory or the directory supplied."
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
        $configurationFile,
        array $operands,
        array $commandValues
    ) {
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
                $logger->error("Configuration file '$configurationFile' not found");
            } else {
                $logger->error("Configuration file not found");
            }
            return false;
        }

        return array("configurationFile" => realpath($configurationFile));
    }
}
