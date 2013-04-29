<?php

namespace RPI\Utilities\ContentBuild\Lib\Dependencies\Xml;

use \RPI\Foundation\Helpers\Object;

/**
 * @property-read array $files
 */
class Dependency extends Object implements \RPI\Utilities\ContentBuild\Lib\Model\IDependency
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
        
        $dependencies = \RPI\Foundation\Helpers\Dom::deserialize(simplexml_import_dom($doc));
        
        if (!isset($dependencies["dependency"][0])) {
            $dependencies["dependency"] = array($dependencies["dependency"]);
        }
        
        foreach ($dependencies["dependency"] as $dependency) {
            $this->files[] = array(
                "name" => $dependency["@"]["name"],
                "type" => (isset($dependency["@"]["type"]) ? $dependency["@"]["type"] : null)
            );
        }
    }
    
    public function getFiles()
    {
        return $this->files;
    }
}
