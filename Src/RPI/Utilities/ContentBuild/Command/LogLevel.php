<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class LogLevel implements \RPI\Console\ICommand
{
    protected $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "loglevel",
            "option" => array(
                "l",
                "loglevel",
                Getopt::REQUIRED_ARGUMENT, "Set log level. 0 = silent, 1 = information, 2 = verbose, 3 = debug"
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
        array $operands
    ) {
        if (isset($value)) {
            try {
                $logLevels = array(
                    array(
                        \Psr\Log\LogLevel::ERROR
                    ),
                    array(
                        \Psr\Log\LogLevel::INFO,
                        \Psr\Log\LogLevel::ERROR,
                        \Psr\Log\LogLevel::WARNING
                    ),
                    array(
                        \Psr\Log\LogLevel::INFO,
                        \Psr\Log\LogLevel::ERROR,
                        \Psr\Log\LogLevel::NOTICE,
                        \Psr\Log\LogLevel::WARNING
                    ),
                    null
                );

                $logger->setLogLevel($logLevels[$value]);
                
                return array("logLevel" => $value);
            } catch (\Exception $ex) {
                $logger->error("Invalid logging level '$value'");
                return false;
            }
        }
    }
}
