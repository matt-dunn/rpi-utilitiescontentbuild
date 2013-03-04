<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Processor;

interface IProcessor
{
    public function process();
    
    public function getOptions();
}
