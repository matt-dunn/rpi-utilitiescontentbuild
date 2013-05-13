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
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        $filename
    ) {
        if (!file_exists($filename)) {
            throw new \Exception("Unable to locate dependencies file '$filename'");
        }
        
        $this->logger = $logger;
        
        $this->validateConfigurationFile($filename);
        
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
    
    protected function validateConfigurationFile($filename)
    {
        try {
            $doc = new \DOMDocument();
            $doc->load($filename);
            if (!\RPI\Foundation\Helpers\Dom::validateSchema(
                $doc,
                dirname(__FILE__)."/Model/Schema.xsd"
            )) {
                exit(2);
            }
        } catch (\Exception $ex) {
            $this->logger->error("Invalid dependency file '$filename'", array("exception" => $ex));
            exit(2);
        }
    }
}
