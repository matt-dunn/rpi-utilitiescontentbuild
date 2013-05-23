<?php

namespace RPI\Utilities\ContentBuild\Lib\Configuration\Xml;

use \RPI\Foundation\Helpers\Object;

/**
 * @property-read string $interface
 * @property-read string $type
 * @property-read array $params
 */
class Plugin extends Object implements \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IPlugin
{
    /**
     *
     * @var string
     */
    protected $interface = null;

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
     * @param string $interface
     * @param string $type
     * @param array $params
     */
    public function __construct($interface, $type, array $params = null)
    {
        $this->interface = $interface;
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
    public function getInterface()
    {
        return $this->interface;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
