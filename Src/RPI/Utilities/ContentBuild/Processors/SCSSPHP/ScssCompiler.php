<?php

namespace RPI\Utilities\ContentBuild\Processors\SCSSPHP;

class ScssCompiler extends \scssc
{
    protected $importCallback = null;

    protected function findImport($url)
    {
        if (isset($this->importCallback) && is_callable($this->importCallback)) {
            $callback = $this->importCallback;
            $importPath = $callback($url);
        }
        
        if (!isset($importPath)) {
            $importPath = parent::findImport($url);
        }

        if (!isset($importPath)) {
            throw new \Exception("Unable to find import '$url'");
        }
        
        return $importPath;
    }

    public function setImportCallback($callback)
    {
        $this->importCallback = $callback;
    }
}
