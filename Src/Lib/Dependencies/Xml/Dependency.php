<?php

namespace RPI\Utilities\ContentBuild\Lib\Dependencies\Xml;

/**
 * @property-read array $files
 */
class Dependency extends \RPI\Utilities\ContentBuild\Lib\Helpers\Object implements \RPI\Utilities\ContentBuild\Lib\Model\IDependency
{
    /**
     *
     * @var array
     */
    private $files = array();
    
    public function __construct($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception("Unable to locate dependencies file '$filename'");
        }
        
        $doc = new \DOMDocument();
        $doc->load($filename);
        
        $dependencies = \RPI\Utilities\ContentBuild\Lib\Helpers\Dom::deserialize(simplexml_import_dom($doc));
        
        if (!isset($dependencies["dependency"][0])) {
            $dependencies["dependency"] = array($dependencies["dependency"]);
        }
        
        foreach ($dependencies["dependency"] as $dependency) {
            $this->files[] = $dependency["@"]["name"];
        }
    }
    
    public function getFiles()
    {
        return $this->files;
    }
}
