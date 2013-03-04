<?php

namespace RPI\Utilities\ContentBuild\Lib\Configuration\Xml;

/**
 * @property-read string $buildDirectory
 * @property-read array $files
 * @property-read $name $buildDirectory
 * @property-read $outputDirectory $buildDirectory
 * @property-read $type $buildDirectory
 * @property-read $version $buildDirectory
 */
class Build extends \RPI\Utilities\ContentBuild\Lib\Helpers\Object implements \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild
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
    private $type = null;
    
    /**
     *
     * @var string
     */
    private $version = null;
    
    public function __construct(array $buildDetails)
    {
        $this->name = $buildDetails["@"]["name"];
        $this->version = $buildDetails["@"]["version"];
        $this->type = $buildDetails["@"]["type"];
        $this->outputDirectory = $buildDetails["@"]["outputDirectory"];
        $this->buildDirectory = $buildDetails["@"]["buildDirectory"];
        
        if (!isset($buildDetails["files"][0])) {
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
}
