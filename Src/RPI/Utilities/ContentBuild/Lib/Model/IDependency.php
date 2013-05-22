<?php

namespace RPI\Utilities\ContentBuild\Lib\Model;

interface IDependency
{
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $filename
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        $filename
    );
    
    /**
     * @return array
     */
    public function getFiles();
    
    /**
     * 
     * @return boolean
     */
    public function validate();
}
