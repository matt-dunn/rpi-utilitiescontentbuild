<?php

namespace RPI\Utilities\ContentBuild\Lib\Configuration\Xml;

use \RPI\Foundation\Helpers\Object;

/**
 * @property-read string $buildDirectory
 * @property-read array $files
 * @property-read string $name
 * @property-read string $outputDirectory
 * @property-read string $outputFilename
 * @property-read string $externalDependenciesNames
 * @property-read string $type
 * @property-read string $version
 * @property-read string $target
 * @property-read string $media
 * @property-read string $debugPath
 */
class Build extends Object implements \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild
{
    /**
     *
     * @var string
     */
    private $buildDirectory = null;
    
    /**
     *
     * @var array
     */
    private $files = array();
    
    /**
     *
     * @var string
     */
    private $name = null;
    
    /**
     *
     * @var string
     */
    private $outputDirectory = null;
    
    /**
     *
     * @var string
     */
    private $outputFilename = null;
    
    /**
     *
     * @var string
     */
    private $externalDependenciesNames = null;
    
    /**
     *
     * @var string
     */
    private $type = null;
    
    /**
     *
     * @var string
     */
    private $version = null;
    
    /**
     *
     * @var string
     */
    private $target = null;
    
    /**
     *
     * @var string
     */
    private $media = null;
    
    /**
     *
     * @var string
     */
    private $debugPath = null;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $buildDetails
    ) {
        if (isset($buildDetails["@"]["name"])) {
            $this->name = $buildDetails["@"]["name"];
        }
        if (isset($buildDetails["@"]["version"])) {
            $this->version = $buildDetails["@"]["version"];
        }
        if (isset($buildDetails["@"]["type"])) {
            $this->type = $buildDetails["@"]["type"];
        }
        if (isset($buildDetails["@"]["outputDirectory"])) {
            $this->outputDirectory = $buildDetails["@"]["outputDirectory"];
        }
        if (isset($buildDetails["@"]["outputFilename"])) {
            $this->outputFilename = $buildDetails["@"]["outputFilename"];
        }
        if (isset($buildDetails["@"]["buildDirectory"])) {
            $this->buildDirectory = $buildDetails["@"]["buildDirectory"];
        }
        if (isset($buildDetails["@"]["externalDependenciesNames"])) {
            $this->externalDependenciesNames = $buildDetails["@"]["externalDependenciesNames"];
        }
        if (isset($buildDetails["@"]["target"])) {
            $this->target = $buildDetails["@"]["target"];
        }
        if (isset($buildDetails["@"]["media"])) {
            $this->media = $buildDetails["@"]["media"];
        }
        
        if ($project->includeDebug) {
            $outputPath = $project->basePath."/".$project->appRoot."/".$this->outputDirectory;

            if (substr($outputPath, strlen($outputPath) - 1, 1) == "/") {
                $outputPath = substr($outputPath, 0, strlen($outputPath) - 1);
            }

            $debugPathParts = explode("/", $outputPath);
            unset($debugPathParts[count($debugPathParts) - 1]);
            $this->debugPath = join("/", $debugPathParts)."/__debug/".$this->type;
        }
        
        if (!is_array($buildDetails["files"]) || !isset($buildDetails["files"][0])) {
            $buildDetails["files"] = array($buildDetails["files"]);
        }
        
        foreach ($buildDetails["files"] as $file) {
            $this->files[] = $file["@"]["name"];
        }
    }
    
    /**
     * 
     * @return string
     */
    public function getBuildDirectory()
    {
        return $this->buildDirectory;
    }

    /**
     * 
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
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
    public function getOutputDirectory()
    {
        return $this->outputDirectory;
    }

    /**
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * 
     * @return string
     */
    public function getOutputFilename()
    {
        return $this->outputFilename;
    }

    /**
     * 
     * @return string
     */
    public function getExternalDependenciesNames()
    {
        return $this->externalDependenciesNames;
    }

    /**
     * 
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * 
     * @return string
     */
    public function getMedia()
    {
        return $this->media;
    }


    /**
     * 
     * @return string
     */
    public function getDebugPath()
    {
        return $this->debugPath;
    }
}
