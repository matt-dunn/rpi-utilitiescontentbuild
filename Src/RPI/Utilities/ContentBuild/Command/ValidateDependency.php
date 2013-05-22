<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class ValidateDependency implements \RPI\Console\ICommand
{
    protected $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "validate-dep",
            "option" => array(
                null,
                "validate-dep",
                Getopt::REQUIRED_ARGUMENT, "Validate dependency file <arg>"
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
        $dependencyFile,
        array $operands,
        array $commandValues
    ) {
        if (isset($dependencyFile)) {
            if (realpath($dependencyFile) === false) {
                $logger->error("Dependency file '$dependencyFile' not found");
                return false;
            }
            
            $dependendency = new \RPI\Utilities\ContentBuild\Lib\Dependency(
                $logger,
                $dependencyFile
            );

            if ($dependendency->dependencies->validate()) {
                $logger->setLogLevel(
                    array(
                        \Psr\Log\LogLevel::INFO
                    )
                );

                $logger->info("Congratulations, the dependency file '$dependencyFile' is valid");
            } else {
                $logger->info("Dependency file '$dependencyFile' is invalid");
            }
    
            return false;
        }
    }
}
