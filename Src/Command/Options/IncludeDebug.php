<?php

namespace RPI\Utilities\ContentBuild\Command\Options;

use Ulrichsg\Getopt;

class IncludeDebug implements \RPI\Utilities\ContentBuild\ICommand
{
    private $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "debug-include",
            "option" => array(
                "d",
                "debug-include",
                Getopt::REQUIRED_ARGUMENT, "Include debug information - 0 or 1"
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
            if (!is_numeric($value) || ($value != 0 && $value != 1)) {
                echo "Invalid value '$value'. Must be 0 or 1.\n";
                return false;
            }
            
            return array("debug-include" => ($value == 1));
        }
    }
}
