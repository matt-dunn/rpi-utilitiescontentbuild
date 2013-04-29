<?php

namespace RPI\Utilities\ContentBuild;

interface ICommand
{
    public function __construct();
    
    public function getOption();
    
    public function getOptionName();
    
    public function exec(
        \Psr\Log\LoggerInterface $logger,
        \Ulrichsg\Getopt $getopt,
        $value,
        array $operands
    );
}
