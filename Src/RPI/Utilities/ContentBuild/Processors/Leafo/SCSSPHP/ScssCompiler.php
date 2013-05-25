<?php

namespace RPI\Utilities\ContentBuild\Processors\Leafo\SCSSPHP;

class ScssCompiler extends \scssc
{

    public $debug = false;
    protected $importCallback = null;
    protected $processImportCallback = null;

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

    protected function importFile($path, $out)
    {
        // see if tree is cached
        $realPath = realpath($path);
        if (isset($this->importCache[$realPath])) {
            $tree = $this->importCache[$realPath];
        } else {
            $code = file_get_contents($path);
            // BEGIN INSERTED CODE
            if (isset($this->processImportCallback) && is_callable($this->processImportCallback)) {
                $callback = $this->processImportCallback;
                $code = $callback($code, $path);
            }
            // END INSERTED CODE
            $parser = new \scss_parser($path, false);
            $tree = $parser->parse($code);
            $this->parsedFiles[] = $path;

            $this->importCache[$realPath] = $tree;
        }

        $pi = pathinfo($path);
        array_unshift($this->importPaths, $pi['dirname']);
        $this->compileChildren($tree->children, $out);
        array_shift($this->importPaths);
    }

    public function setImportCallback($callback)
    {
        $this->importCallback = $callback;
    }

    public function setProcessImportCallback($callback)
    {
        $this->processImportCallback = $callback;
    }

    protected function compileBlock($block)
    {
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
