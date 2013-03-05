<?php

namespace RPI\Utilities\ContentBuild\Lib\Configuration\Xml;

use \RPI\Utilities\ContentBuild\Lib\Helpers\Object;

/**
 * @property-read string $name
 * @property-read string $prefix
 * @property-read string $appRoot
 * @property-read \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild[] $basePath
 * @property-read string $builds
 */
class Project extends Object implements \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
{
    /**
     *
     * @var string
     */
    private $name = null;
    
    /**
     *
     * @var string
     */
    private $prefix = null;
    
    /**
     *
     * @var string 
     */
    private $appRoot = null;
    
    /**
     *
     * @var string
     */
    private $basePath = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild[]
     */
    private $builds = array();
    
    /**
     *
     * @var string
     */
    private $configurationFile = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProcessor[]
     */
    private $processors = null;
    
    /**
     *
     * @var boolean
     */
    private $includeDebug = true;
    
    public function __construct($configurationFile)
    {
        if (!file_exists($configurationFile)) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                "Unable to locate configuration file '{$configurationFile}'",
                LOG_ERR
            );
            exit(2);
        }
        
        $this->configurationFile = $configurationFile;

        $this->validateConfigurationFile($configurationFile);
        
        $doc = new \DOMDocument();
        $doc->load($configurationFile);
        
        $config = \RPI\Utilities\ContentBuild\Lib\Helpers\Dom::deserialize(simplexml_import_dom($doc));

        if (isset($config["@"]["includeDebug"])) {
            $this->includeDebug = $config["@"]["includeDebug"];
        }
        if (isset($config["@"]["name"])) {
            $this->name = $config["@"]["name"];
        }
        if (isset($config["@"]["prefix"])) {
            $this->prefix = $config["@"]["prefix"];
        }
        if (isset($config["@"]["appRoot"])) {
            $this->appRoot = $config["@"]["appRoot"];
        }
        if (isset($config["@"]["basePath"])) {
            $this->basePath = realpath(dirname($this->configurationFile).$config["@"]["basePath"]);
        }
        
        if (!isset($config["build"][0])) {
            $config["build"] = array($config["build"]);
        }
        
        foreach ($config["build"] as $build) {
            $this->builds[] = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Build($this, $build);
        }
        
        if (isset($config["processors"], $config["processors"]["processor"])) {
            if (!isset($config["processors"]["processor"][0])) {
                $config["processors"]["processor"] = array($config["processors"]["processor"]);
            }
            
            $this->processors = array();
            
            foreach ($config["processors"]["processor"] as $processor) {
                $params = null;
                if (isset($processor["param"])) {
                    if (!isset($processor["param"][0])) {
                        $processor["param"] = array($processor["param"]);
                    }
                    $params = array();
                    foreach ($processor["param"] as $param) {
                        $params[] = $param;
                    }
                }
                $this->processors[] = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Processor(
                    $processor["@"]["type"],
                    $params
                );
            }
        }
    }

    private function validateConfigurationFile($configurationFile)
    {
        $doc = new \DOMDocument();
        $doc->load($configurationFile);
        if (!\RPI\Utilities\ContentBuild\Lib\Helpers\Dom::validateSchema(
            $doc,
            dirname(__FILE__)."/Configuration/Schema.xsd"
        )) {
            exit(2);
        }
    }
    
    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild[]
     */
    public function getBuilds()
    {
        return $this->builds;
    }

    /**
     * 
     * @return string
     */
    public function getAppRoot()
    {
        return $this->appRoot;
    }

    /**
     * 
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * 
     * @return string
     */
    public function getConfigurationFile()
    {
        return $this->configurationFile;
    }

    /**
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProcessor[]
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * 
     * @return boolean
     */
    public function getIncludeDebug()
    {
        return $this->includeDebug;
    }
}
