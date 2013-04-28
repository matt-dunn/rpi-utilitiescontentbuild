<?php

namespace RPI\Utilities\ContentBuild\Lib\Configuration\Xml;

use \RPI\Framework\Helpers\Object;

/**
 * @property-read string $name
 * @property-read string $prefix
 * @property-read string $appRoot
 * @property-read string $basePath
 * @property-read \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild[] $builds
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
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IResolver[]
     */
    private $resolvers = null;
    
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
        
        $config = \RPI\Framework\Helpers\Dom::deserialize(simplexml_import_dom($doc));

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
        } else {
            $this->basePath = realpath(dirname($this->configurationFile)."/../../");
        }
        
        if (!is_array($config["build"]) || !isset($config["build"][0])) {
            $config["build"] = array($config["build"]);
        }
        
        foreach ($config["build"] as $build) {
            $this->builds[] = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Build($this, $build);
        }
        
        if (isset($config["processors"], $config["processors"]["processor"])) {
            if (!is_array($config["processors"]["processor"]) || !isset($config["processors"]["processor"][0])) {
                $config["processors"]["processor"] = array($config["processors"]["processor"]);
            }
            
            $this->processors = array();
            
            foreach ($config["processors"]["processor"] as $processor) {
                $params = null;
                if (isset($processor["param"])) {
                    if (!is_array($processor["param"]) || !isset($processor["param"][0])) {
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
        
        if (isset($config["uriResolvers"], $config["uriResolvers"]["uriResolver"])) {
            if (!is_array($config["uriResolvers"]["uriResolver"])
                || !isset($config["uriResolvers"]["uriResolver"][0])) {
                $config["uriResolvers"]["uriResolver"] = array($config["uriResolvers"]["uriResolver"]);
            }
            
            $this->resolvers = array();
            
            foreach ($config["uriResolvers"]["uriResolver"] as $uriResolver) {
                $params = null;
                if (isset($uriResolver["param"])) {
                    if (!is_array($uriResolver["param"]) || !isset($uriResolver["param"][0])) {
                        $uriResolver["param"] = array($uriResolver["param"]);
                    }
                    $params = array();
                    foreach ($uriResolver["param"] as $param) {
                        $params[] = $param;
                    }
                }
                $this->resolvers[] = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Resolver(
                    $uriResolver["@"]["type"],
                    $params
                );
            }
        }
    }

    private function validateConfigurationFile($configurationFile)
    {
        try {
            $doc = new \DOMDocument();
            $doc->load($configurationFile);
            if (!\RPI\Framework\Helpers\Dom::validateSchema(
                $doc,
                dirname(__FILE__)."/Configuration/Schema.xsd"
            )) {
                exit(2);
            }
        } catch (\Exception $ex) {
            echo "Invalid config file '$configurationFile'. In addition:\n";
            echo "{$ex->getMessage()}\n";
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
     * @return \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IResolver[]
     */
    public function getResolvers()
    {
        return $this->resolvers;
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
