<?php

namespace RPI\Utilities\ContentBuild\Command\Options;

use Ulrichsg\Getopt;

class NoDev implements \RPI\Console\ICommand
{
    protected $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "no-dev",
            "option" => array(
                null,
                "no-dev",
                Getopt::NO_ARGUMENT, "Do not generate any debug code"
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
            return array("debug-include" => ($value != 1));
        }
    }
}
