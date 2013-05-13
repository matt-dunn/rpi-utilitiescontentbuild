<?php

namespace RPI\Utilities\ContentBuild\Processors\LESS;

class lessc extends \lessc
{
    protected $importCallback = null;
    
    protected function findImport($url)
    {
        $importPath = parent::findImport($url);
        
        if (!isset($importPath) && isset($this->importCallback) && is_callable($this->importCallback)) {
            $callback = $this->importCallback;
            $importPath = $callback($url);
        }
        
        return $importPath;
    }
    
    public function setImportCallback($callback)
    {
        $this->importCallback = $callback;
    }
}
