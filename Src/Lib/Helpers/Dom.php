<?php

namespace RPI\Utilities\ContentBuild\Lib\Helpers;

/**
 * DOM Helpers
 * @author Matt Dunn
 */

class Dom
{
    private function __construct()
    {
    }
    
    /**
     * Validate a DOMDocument against an schema
     * 
     * @param \DOMDocument $doc
     * @param string $schemaFile
     * 
     * @return boolean
     * 
     * @throws \Exception
     */
    public static function validateSchema(\DOMDocument $doc, $schemaFile)
    {
        $currentState = libxml_use_internal_errors(true);
        
        if (!file_exists($schemaFile)) {
            throw new \Exception("Cannot locate schema '$schemaFile'");
        }
        
        $isValid = false;
        
        try {
            $isValid = $doc->schemaValidate($schemaFile);
        } catch (\Exception $ex) {
            // Allow the code to continue and pick up the errors below
        }
        
        if (!$isValid) {
            $errors = libxml_get_errors();
            $message = "";
            foreach ($errors as $error) {
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $message .= "Warning [$error->code]: ";
                        break;
                    case LIBXML_ERR_ERROR:
                        $message .= "Error [$error->code]: ";
                        break;
                    case LIBXML_ERR_FATAL:
                        $message .= "Fatal Error [$error->code]: ";
                        break;
                }
                $message .= trim($error->message);
                if ($error->file) {
                    $message .= " in '$error->file'";
                }
                $message .= " on line $error->line.\n";
            }

            libxml_clear_errors();
            
            libxml_use_internal_errors($currentState);
            
            throw new \RuntimeException($message);
        }
        
        libxml_use_internal_errors($currentState);
        
        return true;
    }

    /**
     * 
     * @param \SimpleXMLElement $xml
     * @param \SimpleXMLElement $parent
     * 
     * @return array
     */
    public static function deserialize(\SimpleXMLElement $xml, \SimpleXMLElement $parent = null)
    {
        $children = array();
        
        foreach ($xml->children() as $elementName => $child) {
            $element = array();
            
            $parentElement = $child->xpath("parent::*");
            if (isset($parentElement) && $parentElement !== false) {
                $parentElement = $parentElement[0];
            } else {
                $parentElement = null;
            }
            
            $attributes = array();
            foreach ($child->attributes() as $name => $value) {
                $attributes[$name] = self::parseType($value);
            }
            if (count($attributes) > 0) {
                $element["@"] = $attributes;
            }
            
            $namespaces = $child->getNamespaces();
            if (count($namespaces) > 0) {
                $namespace = reset($namespaces);
                $parentNamespaces = $parentElement->getNamespaces();
                if (!isset($parent) || $namespace != reset($parentNamespaces)) {
                    $element["#NS"] = $namespace;
                }
            }
            
            if (!isset($children[$elementName]) && substr($elementName, 0, 1) != "_") {
                $children[$elementName] = array();
            } elseif (!isset($children)) {
                $children = array();
            }
            
            $element = array_merge($element, self::deserialize($child, $xml));
            
            if (trim((string)$child) != "") {
                if (count($element) == 0) {
                    $element = self::parseType((string)$child);
                } else {
                    $element["#"] = self::parseType((string)$child);
                }
            }
            
            if (substr($elementName, 0, 1) != "_") {
                $children[$elementName][] = $element;
            } else {
                $children[] = $element;
            }
        }
        
        foreach ($children as $key => $child) {
            if (is_array($child) && count($child) == 1) {
                reset($child);
                $firstKey = key($child);
                if (is_numeric($firstKey)) {
                    $children[$key] = $child[$firstKey];
                }
            }
        }
        
        if (!isset($parent)) {
            $attributes = array();
            foreach ($xml->attributes() as $name => $value) {
                $attributes[$name] = self::parseType($value);
            }
            
            if (substr($xml->getName(), 0, 1) != "_") {
                $parentElement = array("#NAME" => $xml->getName());
            } else {
                $parentElement = array();
            }
            
            $namespaces = $xml->getNamespaces();
            if (count($namespaces) > 0) {
                $parentElement["#NS"] = reset($namespaces);
            }
            
            if (count($attributes) > 0) {
                $parentElement["@"] = $attributes;
            }
            
            return array_merge($parentElement, $children);
        }
        
        return $children;
    }
    
    /**
     * Cast a value to its type
     * 
     * @param mixed $value
     * 
     * @return mixed
     */
    public static function parseType($value)
    {
        $value = trim($value);
        
        if (strtolower($value) == "null") {
            $value = null;
        } elseif (strtolower($value) == "true") {
            $value = true;
        } elseif (strtolower($value) == "false") {
            $value = false;
        } elseif (ctype_digit($value)) {
            $value = (int) $value;
        } elseif (is_numeric($value)) {
            $value = (double) $value;
        }

        return $value;
    }
}
