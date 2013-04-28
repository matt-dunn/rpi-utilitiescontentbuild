<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Configuration;

interface IProcessor
{
    /**
     * @return string
     */
    public function getType();
    
    /**
     * @return array|null
     */
    public function getParams();
}
