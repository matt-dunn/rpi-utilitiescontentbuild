<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Configuration;

interface IResolver
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
