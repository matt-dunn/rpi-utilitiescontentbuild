<?php

namespace RPI\Utilities\ContentBuild\Lib\Configuration\Xml;

use \RPI\Foundation\Helpers\Object;

/**
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
            $this->logger->error(
                "Unable to locate configuration file '{$configurationFile}'"
            );
            exit(2);
        }
        
        $this->logger = $logger;
        $this->configurationFile = $configurationFile;

        $this->validate();
        
        $doc = new \DOMDocument();
        $doc->load($configurationFile);
        
        $config = \RPI\Foundation\Helpers\Dom::deserialize(simplexml_import_dom($doc));

        if (isset($options["debug-include"])) {
            $this->includeDebug = $options["debug-include"];
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
                $this->processors[$processor["@"]["type"]] =
                    new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Processor(
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
                $this->resolvers[$uriResolver["@"]["type"]] =
                    new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Resolver(
                        $uriResolver["@"]["type"],
                        $params
                    );
            }
        }
        
        if (isset($config["plugins"], $config["plugins"]["plugin"])) {
            if (!is_array($config["plugins"]["plugin"])
                || !isset($config["plugins"]["plugin"][0])) {
                $config["plugins"]["plugin"] = array($config["plugins"]["plugin"]);
            }
            
            $this->plugins = array();
            
            foreach ($config["plugins"]["plugin"] as $plugin) {
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
                $this->plugins[$plugin["@"]["interface"]] =
                    new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Plugin(
                        $plugin["@"]["interface"],
                        $plugin["@"]["type"],
                        $params
                    );
            }
        }
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
}
