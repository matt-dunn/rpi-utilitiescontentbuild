<?php

namespace RPI\Utilities\ContentBuild\UriResolvers;

class Composer implements \RPI\Utilities\ContentBuild\Lib\Model\UriResolver\IUriResolver
{
    const VERSION = "1.0.3";

    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    private $project = null;
    
    /**
     *
     * @var string
     */
    private $vendorPath = null;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
        
        if (isset($options, $options["vendorPath"])) {
            $this->vendorPath = realpath(dirname($this->project->configurationFile).DIRECTORY_SEPARATOR.$options["vendorPath"]);
            
            if ($this->vendorPath === false) {
                throw new \Exception("Unable to locate vendor path '".$options["vendorPath"]."'. Check vendorPath parameter in your config.");
            }
        } else {
            throw new \Exception("Param 'vendorPath' must be specified in the config for uriResolver '".__CLASS__."'");
        }
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    /**
     * {@inherit-doc}
     */
    public function getScheme()
    {
        return "composer";
    }
    
    /**
     * {@inherit-doc}
     */
    public function realpath(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $uri
    ) {
        $pathParts = parse_url($uri);
        if (isset($pathParts["scheme"], $pathParts["host"], $pathParts["path"])
            && $pathParts["scheme"] == $this->getScheme()) {
            $package = $pathParts["host"].$pathParts["path"];
            $fragment = $pathParts["fragment"];
            return realpath(
                $this->vendorPath.DIRECTORY_SEPARATOR.
                $package.DIRECTORY_SEPARATOR.
                $fragment
            );
        }
        
        return false;
    }
}
