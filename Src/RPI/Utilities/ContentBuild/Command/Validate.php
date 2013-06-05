<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class Validate implements \RPI\Console\ICommand
{
    protected $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "validate",
            "option" => array(
                null,
                "validate",
                Getopt::NO_ARGUMENT, "Validate configuration file"
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
            if (!isset($commandValues["configurationFile"])) {
                $logger->error("Configuration file not found");
                return false;
            }

            $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
                $logger,
                $commandValues["configurationFile"]
            );

            if ($configuration->project->validate()) {
                $logger->setLogLevel(
                    array(
                        \Psr\Log\LogLevel::INFO
                    )
                );

                $logger->info("Congratulations, the config file '{$commandValues["configurationFile"]}' is valid");
            }
    
            return false;
        }
    }
}
