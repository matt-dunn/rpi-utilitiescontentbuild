<?php

namespace RPI\Utilities\ContentBuild\Lib\Configuration\Xml;

use \RPI\Foundation\Helpers\Object;

/**
 * @property-read array $options
 * @property-read string $name
 * @property-read string $prefix
 * @property-read string $appRoot
 * @property-read string $basePath
 * @property-read string $configurationFile
 * @property-read \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild[] $builds
 * @property-read \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProcessor[] $processors
 * @property-read \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IResolver[] $resolvers
 * @property-read \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IPlugin[] $plugins
 * @property-read boolean $includeDebug
 * @property-read \Psr\Log\LoggerInterface $logger
 */
class Project extends Object implements \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
{
    /**
     *
     * @var string
     */
    protected $name = null;
    
    /**
     *
     * @var string
     */
    protected $prefix = null;
    
    /**
     *
     * @var string 
     */
    protected $appRoot = null;
    
    /**
     *
     * @var string
     */
    protected $basePath = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild[]
     */
    protected $builds = array();
    
    /**
     *
     * @var string
     */
    protected $configurationFile = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProcessor[]
     */
    protected $processors = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IResolver[]
     */
    protected $resolvers = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IPlugin[]
     */
    protected $plugins = null;
    
    /**
     *
     * @var boolean
     */
    protected $includeDebug = true;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger = null;

    /**
     *
     * @var array
     */
    protected $options = null;
    
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $configurationFile
     * @param array $options
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        $configurationFile,
        array $options = null
    ) {
        if (!file_exists($configurationFile)) {
            throw new \RPI\Foundation\Exceptions\RuntimeException(
                "Unable to locate configuration file '{$configurationFile}'"
            );
        }
        
        $this->logger = $logger;
        $this->configurationFile = $configurationFile;

        $this->validate();
        
        $doc = new \DOMDocument();
        $doc->load($configurationFile);
        
        $config = \RPI\Foundation\Helpers\Dom::deserialize(simplexml_import_dom($doc));
        
        $this->options = $options;

        if (isset($this->options["debug-include"])) {
            $this->includeDebug = $this->options["debug-include"];
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
        
        $this->processors = $this->getPluginCollection(
            $config,
            "processors",
            "processor",
            "\RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Processor"
        );
        
        $this->resolvers = $this->getPluginCollection(
            $config,
            "uriResolvers",
            "uriResolver",
            "\RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Processor"
        );
        
        $this->plugins = $this->getPluginCollection(
            $config,
            "plugins",
            "plugin",
            "\RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Plugin"
        );
    }
    
    protected function getPluginCollection(array $config, $collectionName, $itemName, $itemType)
    {
        $plugins = null;
        
        if (isset($config[$collectionName], $config[$collectionName][$itemName])) {
            if (!is_array($config[$collectionName][$itemName]) || !isset($config[$collectionName][$itemName][0])) {
                $config[$collectionName][$itemName] = array($config[$collectionName][$itemName]);
            }
            
            $plugins = array();
            
            foreach ($config[$collectionName][$itemName] as $plugin) {
                $params = null;
                if (isset($plugin["param"])) {
                    if (!is_array($plugin["param"]) || !isset($plugin["param"][0])) {
                        $plugin["param"] = array($plugin["param"]);
                    }
                    $params = array();
                    foreach ($plugin["param"] as $param) {
                        $params[] = $param;
                    }
                }
                if (isset($plugin["@"]["interface"])) {
                    $plugins[$plugin["@"]["interface"]] =
                        new $itemType(
                            $plugin["@"]["interface"],
                            $plugin["@"]["type"],
                            $params
                        );
                } else {
                    $plugins[$plugin["@"]["type"]] =
                        new $itemType(
                            $plugin["@"]["type"],
                            $params
                        );
                }
            }
        }
        
        return $plugins;
    }

    public function validate()
    {
        try {
            $doc = new \DOMDocument();
            $doc->load($this->configurationFile);
            return \RPI\Foundation\Helpers\Dom::validateSchema(
                $doc,
                dirname(__FILE__)."/Model/Schema.xsd"
            );
        } catch (\Exception $ex) {
            throw new \RPI\Foundation\Exceptions\RuntimeException(
                "Invalid config file '{$this->configurationFile}'",
                null,
                $ex
            );
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
     * @return \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IPlugin[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * 
     * @return boolean
     */
    public function getIncludeDebug()
    {
        return $this->includeDebug;
    }
    
    /**
     * 
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
