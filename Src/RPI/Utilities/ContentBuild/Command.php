<?php

namespace RPI\Utilities\ContentBuild;

use Ulrichsg\Getopt;

class Command
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\ICommand[]
     */
    private $commands = null;
    
    /**
     *
     * @var \Ulrichsg\Getopt
     */
    private $getopt = null;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    private $logger = null;
    
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param \RPI\Utilities\ContentBuild\ICommand[] $commands
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        array $commands
    ) {
        $this->logger = $logger;
        $this->commands = $commands;
        
        if (count($this->commands) > 0) {
            $this->getopt = new Getopt();
            
            foreach ($this->commands as $command) {
                $optionsDetails = $command->getOption();
                $this->getopt->addOptions(array($optionsDetails["option"]));
            }
        }
    }
    
    public function parse()
    {
        if (isset($this->getopt)) {
            try {
                $this->getopt->parse();
            } catch (\UnexpectedValueException $ex) {
                displayHeader($this->logger);
                $this->logger->error($ex->getMessage());
                $this->getopt->showHelp();
                exit(1);
            }

            $options = $this->getopt->getOptions();
            $operands = $this->getopt->getOperands();
            $commandValues = array();

            foreach ($this->commands as $command) {
                $value = null;
                if (isset($options[$command->getOptionName()])) {
                    $value = $options[$command->getOptionName()];
                }

                $ret = $command->exec($this->logger, $this->getopt, $value, $operands);
                if ($ret === false) {
                    return false;
                } elseif (isset($ret)) {
                    if (is_array($ret)) {
                        $commandValues = array_merge($commandValues, $ret);
                    } else {
                        $commandValues[] = $ret;
                    }
                }
            }

            return $commandValues;
        }
        
        return false;
    }
}
