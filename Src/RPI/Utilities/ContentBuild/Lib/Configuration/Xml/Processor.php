<?php

namespace RPI\Utilities\ContentBuild\Lib\Configuration\Xml;

use \RPI\Foundation\Helpers\Object;

/**
 * @property-read string $type
 * @property-read array $params
 */
class Processor extends Object implements \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProcessor
{
    /**
     *
     * @var string
     */
    protected $type = null;

    /**
     *
     * @var array
     */
    protected $params = null;
    
    /**
     * 
     * @param string $type
     * @param array $params
     */
    public function __construct($type, array $params = null)
    {
        $this->type = $type;
        $this->params = $params;
    }
    
    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
