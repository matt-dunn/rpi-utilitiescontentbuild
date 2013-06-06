<?php

namespace RPI\Utilities\ContentBuild\UriResolvers;

class Composer implements \RPI\Utilities\ContentBuild\Lib\Model\UriResolver\IUriResolver
{
    const VERSION = "1.0.3";

    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    /**
     *
     * @var string
     */
    protected $vendorPath = null;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
        
        if (isset($options, $options["vendorPath"])) {
            $this->vendorPath = realpath(
                dirname($this->project->configurationFile).DIRECTORY_SEPARATOR.$options["vendorPath"]
            );
            
            if ($this->vendorPath === false) {
                throw new \RPI\Foundation\Exceptions\InvalidArgument(
                    $options["vendorPath"],
                    null,
                    "Unable to locate vendor path - check vendorPath parameter in your config."
                );
            }
        } else {
            throw new \RPI\Foundation\Exceptions\RuntimeException(
                "Param 'vendorPath' must be specified in the config for uriResolver '".__CLASS__."'"
            );
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
        if (isset($pathParts["scheme"], $pathParts["host"], $pathParts["path"])) {
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
    
    public function getRelativePath(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $uri
    ) {
        $pathParts = parse_url($uri);
        if (isset($pathParts["fragment"])) {
            $relativePath = $pathParts["fragment"];
            if (isset($pathParts["scheme"], $pathParts["host"], $pathParts["path"])) {
                $package = $pathParts["host"].$pathParts["path"];
                $packagePath = $this->vendorPath.DIRECTORY_SEPARATOR.$package;
                $packageDetails = "$packagePath/composer.json";

                if (file_exists($packageDetails)) {
                    $details = json_decode(file_get_contents($packageDetails), true);
                    if (isset($details["autoload"], $details["autoload"]["psr-0"])) {
                        $codePath = ltrim(
                            rtrim(
                                reset($details["autoload"]["psr-0"]),
                                DIRECTORY_SEPARATOR
                            ),
                            DIRECTORY_SEPARATOR
                        ).DIRECTORY_SEPARATOR;

                        if (substr($relativePath, 0, strlen($codePath)) == $codePath) {
                            $relativePath = substr($relativePath, strlen($codePath));
                        }
                    }
                }
            }
            
            return $relativePath;
        }
        
        return false;
    }
}
