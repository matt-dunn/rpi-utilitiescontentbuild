<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Configuration;

interface IPlugin
{
    /**
     * @return string
     */
    public function getInterface();
    
    /**
     * @return string
     */
    public function getType();
    
    /**
     * @return array|null
     */
    public function getParams();
}
