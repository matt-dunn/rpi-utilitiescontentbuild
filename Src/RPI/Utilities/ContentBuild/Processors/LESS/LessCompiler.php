<?php

namespace RPI\Utilities\ContentBuild\Processors\LESS;

class LessCompiler extends \lessc
{
    public $debug = false;
    protected $importCallback = null;

    protected function findImport($url)
    {
        $importPath = parent::findImport($url);

        if (!isset($importPath) && isset($this->importCallback) && is_callable($this->importCallback)) {
            $callback = $this->importCallback;
            $importPath = $callback($url);

            if (!isset($importPath)) {
                throw new \Exception("Unable to find import '$url'");
            }
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
                "@media -sass-debug-info{filename{font-family:file\:\/\/%s}line{font-family:\%08d}}\n",
                str_replace(array(".", "/"), array("\.", "\/"), realpath($filename)),
                $line_number
            );
            $this->scope->children[] = $outDebug;
        }

        return parent::compileCSSBlock($block);
    }

    protected function makeParser($name)
    {
        $parser = new \RPI\Utilities\ContentBuild\Processors\LESS\LesscParser($this, $name);
        $parser->writeComments = $this->preserveComments;

        return $parser;
    }

    // Need to override injectVariables as it does not call makeParser
    protected function injectVariables($args)
    {
        $this->pushEnv();
        $parser = new \RPI\Utilities\ContentBuild\Processors\LESS\LesscParser($this, __METHOD__);
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
