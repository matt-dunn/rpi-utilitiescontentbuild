<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class Version implements \RPI\Console\ICommand
{
    protected $optionDetails = null;
    
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

    public function exec(
        \Psr\Log\LoggerInterface $logger,
        \Ulrichsg\Getopt $getopt,
        $value,
        array $operands,
        array $commandValues
    ) {
        if (isset($value)) {
            $logger->setLogLevel(
                array(
                    \Psr\Log\LogLevel::INFO
                )
            );
            
            $logger->info("ContentBuild v".CONTENT_BUILD_VERSION);
            return false;
        }
    }
}
