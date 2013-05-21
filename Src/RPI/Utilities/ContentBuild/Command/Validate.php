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
                Getopt::NO_ARGUMENT, "Validate build file"
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
            $options = $getopt->getOptions();
            if (!isset($commandValues["configurationFile"])) {
                $logger->error("Configuration file not found");
            }

            $project = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project(
                $logger,
                $commandValues["configurationFile"]
            );

            if ($project->validate()) {
                $logger->setLogLevel(
                    array(
                        \Psr\Log\LogLevel::INFO
                    )
                );

                $logger->info("Config file is valid");
            }
    
            return false;
        }
    }
}
