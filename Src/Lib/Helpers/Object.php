<?php

namespace RPI\Utilities\ContentBuild\Lib\Helpers;

abstract class Object
{
    public function __get($name)
    {
        $property = "get".ucfirst($name);
        
        if (method_exists($this, $property)) {
            return $this->$property();
        } else {
            throw new \InvalidArgumentException("Undefined property: '$name'");
        }
    }
    
    public function __set($name, $value)
    {
        $property = "set".ucfirst($name);
        
        if (method_exists($this, $property)) {
            $this->$property($value);
        } else {
            throw new \InvalidArgumentException("Property is read-only: '$name'");
        }
    }
    
    public function __isset($name)
    {
        $property = "get".ucfirst($name);
        
        if (method_exists($this, $property)) {
            return ($this->$property() !== null);
        } else {
            throw new \InvalidArgumentException("Undefined property: '$name'");
        }
    }
    
    public function __unset($name)
    {
        $property = "get".ucfirst($name);
        
        if (method_exists($this, $property)) {
            $this->$property = null;
        } else {
            throw new \InvalidArgumentException("Undefined property: '$name'");
        }
    }
    
    /**
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->getProperties(true);
    }
    
    public function __sleep()
    {
        return $this->getProperties();
    }
    
    private function getProperties($getValue = false)
    {
        $properties = array();
        
        $reflect = new \ReflectionObject($this);
        
        foreach ($reflect->getMethods(\ReflectionProperty::IS_PUBLIC) as $method) {
            $parameterCount = count($method->getParameters());
            $methodName = $method->getName();
            if ($parameterCount == 0 && substr($methodName, 0, 3) == "get") {
                if ($getValue) {
                    $properties[lcfirst(substr($methodName, 3))] = $this->$methodName();
                } else {
                    $properties[lcfirst(substr($methodName, 3))] = lcfirst(substr($method->getName(), 3));
                }
            }
        }
        
        foreach ($reflect->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $propertyName = $prop->getName();
            if ($getValue) {
                $properties[$propertyName] = $this->$propertyName;
            } else {
                $properties[$propertyName] = $propertyName;
            }
        }

        return $properties;
    }
}
