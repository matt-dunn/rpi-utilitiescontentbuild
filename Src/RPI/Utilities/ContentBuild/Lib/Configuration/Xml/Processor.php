<?php

namespace RPI\Utilities\ContentBuild\Lib\Configuration\Xml;

use \RPI\Framework\Helpers\Object;

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
    private $type = null;

    /**
     *
     * @var array
     */
    private $params = null;
    
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
