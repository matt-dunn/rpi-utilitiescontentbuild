<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class Help implements \RPI\Console\ICommand
{
    protected $optionDetails = null;
    
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
            
            displayHeader($logger);
            $getopt->showHelp();
            return false;
        }
    }
}
