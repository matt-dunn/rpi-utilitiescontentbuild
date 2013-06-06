<?php

namespace RPI\Utilities\ContentBuild\Test\Command;

use Ulrichsg\Getopt;

abstract class Base extends \RPI\Test\Harness\Base
{
    protected function execCommand($command, $commandValues = array())
    {
        $getopt = new Getopt();
        
        $optionsDetails = $this->object->getOption();
        $getopt->addOptions(array($optionsDetails["option"]));
        
        $getopt->parse($command);

        $options = $getopt->getOptions();
        $operands = $getopt->getOperands();

        $value = null;
        if (isset($options[$this->object->getOptionName()])) {
            $value = $options[$this->object->getOptionName()];
        }
        
        return $this->object->exec(
            $this->logger,
            $getopt,
            $value,
            $operands,
            $commandValues
        );
    }
}
