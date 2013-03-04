<?php

namespace RPI\Utilities\ContentBuild\Lib\Configuration\Xml;

/**
 * @property-read string $name
 * @property-read string $prefix
 * @property-read string $appRoot
 * @property-read \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild[] $basePath
 * @property-read string $builds
 */
class Project extends \RPI\Utilities\ContentBuild\Lib\Helpers\Object implements \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
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
    
    function __construct($configurationFile)
    {
        if (!file_exists($configurationFile)) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Unable to locate configuration file '{$configurationFile}'", LOG_ERR);
            exit(2);
        }
        
        $this->configurationFile = $configurationFile;

        $this->validateConfigurationFile($configurationFile);
        
        $doc = new \DOMDocument();
        $doc->load($configurationFile);
        
        $config = \RPI\Utilities\ContentBuild\Lib\Helpers\Dom::deserialize(simplexml_import_dom($doc));
        
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
            $this->basePath = $config["@"]["basePath"];
        }
        
        if (!isset($config["build"][0])) {
            $config["build"] = array($config["build"]);
        }
        
        foreach ($config["build"] as $build) {
            $this->builds[] = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Build($build);
        }
    }

    private function validateConfigurationFile($configurationFile)
    {
        $doc = new \DOMDocument();
        $doc->load($configurationFile);
        if (!\RPI\Utilities\ContentBuild\Lib\Helpers\Dom::validateSchema($doc, dirname(__FILE__)."/Configuration/Schema.xsd")) {
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
}
