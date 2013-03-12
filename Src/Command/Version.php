<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class Version implements \RPI\Utilities\ContentBuild\ICommand
{
    private $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "version",
            "option" => array(
                "v",
                "version",
                Getopt::NO_ARGUMENT, "Display version information"
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
            echo "ContentBuild v".CONTENT_BUILD_VERSION."\n";
            return false;
        }
    }
}
