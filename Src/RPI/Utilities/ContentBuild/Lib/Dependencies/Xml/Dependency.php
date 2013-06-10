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
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger = null;
    
    /**
     *
     * @var array
     */
    protected $files = array();
    
    /**
     *
     * @var string
     */
    protected $filename = null;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        $filename
    ) {
        if (!file_exists($filename)) {
            throw new \RPI\Foundation\Exceptions\RuntimeException(
                "Unable to locate dependencies file '$filename'"
            );
        }
        
        $this->logger = $logger;
        $this->filename = $filename;
        
        $this->validate();
        
        $doc = new \DOMDocument();
        $doc->load($this->filename);
        
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
    
    public function validate()
    {
        try {
            $doc = new \DOMDocument();
            $doc->load($this->filename);
            return \RPI\Foundation\Helpers\Dom::validateSchema(
                $doc,
                dirname(__FILE__)."/Model/Schema.xsd"
            );
        } catch (\Exception $ex) {
            throw new \RPI\Foundation\Exceptions\RuntimeException(
                "Invalid dependency file '{$this->filename}'",
                null,
                $ex
            );
        }
    }
}
