<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Configuration;

interface IProcessor
{
    /**
     * @return string
     */
    public function getType();
    
    public function getParams();
}
