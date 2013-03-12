<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class Help implements \RPI\Utilities\ContentBuild\ICommand
{
    private $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "help",
            "option" => array(
                "h",
                "help",
                Getopt::NO_ARGUMENT, "Show this help"
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
        if (isset($value)) {
            displayHeader();
            $getopt->showHelp();
            return false;
        }
    }
}
