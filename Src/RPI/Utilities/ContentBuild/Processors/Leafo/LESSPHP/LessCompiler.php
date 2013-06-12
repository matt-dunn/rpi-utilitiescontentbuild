<?php

namespace RPI\Utilities\ContentBuild\Processors\Leafo\LESSPHP;

class LessCompiler extends \lessc
{
    public $debug = false;
    protected $importCallback = null;
    protected $processImportCallback = null;

	public function __construct($fname = null) {
        $this->numberPrecision = 5;
        
        parent::__construct($fname);
    }
    
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

    /**
     * @codeCoverageIgnore
     */
    protected function tryImport($importPath, $parentBlock, $out)
    {
        if ($importPath[0] == "function" && $importPath[1] == "url") {
            $importPath = $this->flattenList($importPath[2]);
        }

        $str = $this->coerceString($importPath);
        if ($str === null) {
            return false;
        }

        $url = $this->compileValue($this->lib_e($str));

        // don't import if it ends in css
        if (substr_compare($url, '.css', -4, 4) === 0) {
            return false;
        }

        $realPath = $this->findImport($url);
        if ($realPath === null) {
            return false;
        }

        if ($this->importDisabled) {
            return array(false, "/* import disabled */");
        }

        $this->addParsedFile($realPath);
        $parser = $this->makeParser($realPath);
        // BEGIN INSERTED CODE
        $code = file_get_contents($realPath);
        if (isset($this->processImportCallback) && is_callable($this->processImportCallback)) {
            $callback = $this->processImportCallback;
            $code = $callback($code, $realPath);
        }
        // END INSERTED CODE
        $root = $parser->parse($code);

        // set the parents of all the block props
        foreach ($root->props as $prop) {
            if ($prop[0] == "block") {
                $prop[1]->parent = $parentBlock;
            }
        }

        // copy mixins into scope, set their parents
        // bring blocks from import into current block
        // TODO: need to mark the source parser	these came from this file
        foreach ($root->children as $childName => $child) {
            if (isset($parentBlock->children[$childName])) {
                $parentBlock->children[$childName] = array_merge(
                    $parentBlock->children[$childName],
                    $child
                );
            } else {
                $parentBlock->children[$childName] = $child;
            }
        }

        $pi = pathinfo($realPath);
        $dir = $pi["dirname"];

        list($top, $bottom) = $this->sortProps($root->props, true);
        $this->compileImportedProps($top, $parentBlock, $out, $parser, $dir);

        return array(true, $bottom, $parser, $dir);
    }

    public function setImportCallback($callback)
    {
        $this->importCallback = $callback;
    }

    public function setProcessImportCallback($callback)
    {
        $this->processImportCallback = $callback;
    }

    protected function compileCSSBlock($block)
    {
        if ($this->debug && isset($block->props[0])) {
            $prop = $block->props[0];
            $filename = $prop[-2];
            $lineNumber = $prop[-3];

            $outDebug = $this->makeOutputBlock(null);
            $outDebug->lines [] = sprintf(
                "@media -sass-debug-info{filename{font-family:file\:\/\/%s}line{font-family:\\00003%d}}\n",
                preg_replace(
                    "/([\/:.])/",
                    "\\\\$1",
                    realpath($filename)
                ),
                $lineNumber
            );
            $this->scope->children[] = $outDebug;
        }

        return parent::compileCSSBlock($block);
    }

    protected function makeParser($name)
    {
        $parser = new \RPI\Utilities\ContentBuild\Processors\Leafo\LESSPHP\LesscParser($this, $name);
        $parser->writeComments = $this->preserveComments;

        return $parser;
    }

    /**
     * Need to override injectVariables as it does not call makeParser
     * 
     * @codeCoverageIgnore
     */
    protected function injectVariables($args)
    {
        $this->pushEnv();
        $parser = new \RPI\Utilities\ContentBuild\Processors\Leafo\LESSPHP\LesscParser($this, __METHOD__);
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
