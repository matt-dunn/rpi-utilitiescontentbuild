<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class LogLevel implements \RPI\Utilities\ContentBuild\ICommand
{
    private $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "loglevel",
            "option" => array(
                "l",
                "loglevel",
                Getopt::REQUIRED_ARGUMENT, "Set log level"
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
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::setLogLevel($value);
            return array("logLevel" => $value);
        }
    }
}
