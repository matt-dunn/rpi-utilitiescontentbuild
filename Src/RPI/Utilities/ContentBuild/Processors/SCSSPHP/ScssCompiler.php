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
    
	protected function compileBlock($block) {
        if ($this->debug && isset($block->children[0])) {
            $prop = $block->children[0];
            
            $filename = null;
            $lineNumber = null;
            
            if (isset($prop[-2]) && is_object($prop[-2])) {
                $filename = $prop[-2]->sourceName;
                $lineNumber = substr_count(substr($prop[-2]->buffer, 0, $prop[-1]), "\n");
            } else {
                $filename = $this->parser->sourceName;
                $lineNumber = substr_count(substr($this->parser->buffer, 0, $prop[-1]), "\n");
            }
            
            $outDebug = $this->makeOutputBlock(null);
            $outDebug->lines [] = sprintf(
                "@media -sass-debug-info{filename{font-family:file\:\/\/%s}line{font-family:\\00003%d}}",
                preg_replace("/([\/:.])/", "\\\\$1", realpath($filename)),
                $lineNumber
            );
            $this->scope->children[] = $outDebug;
        }

        return parent::compileBlock($block);
	}
}
