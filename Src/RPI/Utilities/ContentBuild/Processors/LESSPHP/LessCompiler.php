<?php

namespace RPI\Utilities\ContentBuild\Processors\LESSPHP;

class LessCompiler extends \lessc
{
    public $debug = false;
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

    protected function compileCSSBlock($block)
    {
        if ($this->debug && isset($block->props[0])) {
            $prop = $block->props[0];
            $filename = $prop[-2];
            $line_number = $prop[-3];

            $outDebug = $this->makeOutputBlock(null);
            $outDebug->lines [] = sprintf(
                "@media -sass-debug-info{filename{font-family:file\:\/\/%s}line{font-family:\\00003%d}}\n",
                preg_replace("/([\/:.])/", "\\\\$1", realpath($filename)),
                $line_number
            );
            $this->scope->children[] = $outDebug;
        }

        return parent::compileCSSBlock($block);
    }

    protected function makeParser($name)
    {
        $parser = new \RPI\Utilities\ContentBuild\Processors\LESSPHP\LesscParser($this, $name);
        $parser->writeComments = $this->preserveComments;

        return $parser;
    }

    // Need to override injectVariables as it does not call makeParser
    protected function injectVariables($args)
    {
        $this->pushEnv();
        $parser = new \RPI\Utilities\ContentBuild\Processors\LESSPHP\LesscParser($this, __METHOD__);
        foreach ($args as $name => $strValue) {
            if ($name{0} != '@') {
                $name = '@' . $name;
            }
            $parser->count = 0;
            $parser->buffer = (string) $strValue;
            if (!$parser->propertyValue($value)) {
                throw new Exception("failed to parse passed in variable $name: $strValue");
            }

            $this->set($name, $value);
        }
    }
}
