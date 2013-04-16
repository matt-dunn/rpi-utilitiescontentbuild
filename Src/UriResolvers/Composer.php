<?php

namespace RPI\Utilities\ContentBuild\UriResolvers;

class Composer implements \RPI\Utilities\ContentBuild\Lib\Model\UriResolver\IUriResolver
{
    const VERSION = "1.0.1";

    public function getVersion()
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
                $project->basePath.DIRECTORY_SEPARATOR.
                "vendor".DIRECTORY_SEPARATOR.
                $package.DIRECTORY_SEPARATOR.
                $fragment
            );
        }
        
        return false;
    }
}
