<?php

namespace RPI\Utilities\ContentBuild\Processors\Leafo;

abstract class ProcessorBase implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;

    /**
     *
     * @var array
     */
    protected $customFunctions = null;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
        
        if (isset($options["custom"], $options["custom"]["function"])) {
            $this->customFunctions = $options["custom"]["function"];
            if (isset($this->customFunctions["@"])) {
                $this->customFunctions = array($this->customFunctions);
            }
        }
    }
    
    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        $processorIndex
    ) {
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $buffer
    ) {
        return $buffer;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $buffer
    ) {
        if (pathinfo($inputFilename, PATHINFO_EXTENSION) == $this->getFileExtension()) {
            $this->project->getLogger()->info("Compiling ".strtoupper($this->getFileExtension())." '$inputFilename'");
            
            $compiler = $this->getCompiler();
            $compiler->debug = $processor->debug;

            if (isset($this->customFunctions)) {
                foreach ($this->customFunctions as $function) {
                    if (!isset($function["@"], $function["@"]["name"])) {
                        throw new \RPI\Utilities\ContentBuild\Processors\Leafo\Exceptions\CustomFunction(
                            "Custom function missing name attribute: ".
                            str_replace("array (", "", var_export($function, true))
                        );
                    }
                    
                    if (!isset($function["@"], $function["@"]["params"])) {
                        throw new \RPI\Utilities\ContentBuild\Processors\Leafo\Exceptions\CustomFunction(
                            "Custom function '{$function["@"]["name"]}' missing params attribute"
                        );
                    }
                    
                    if (!isset($function["#"]) || trim($function["#"]) == "") {
                        throw new \RPI\Utilities\ContentBuild\Processors\Leafo\Exceptions\CustomFunction(
                            "Custom function '{$function["@"]["name"]}' missing code in function body"
                        );
                    }
                    
                    $functionBody = <<<EOT
                        try {
                            {$function["#"]}
                        } catch (\Exception \$ex) {
                            throw new \RPI\Utilities\ContentBuild\Processors\Leafo\Exceptions\CustomFunction(
                                "Custom function '{$function["@"]["name"]}' caused an exception: ".\$ex->getMessage()
                            );
                        }
EOT;
                    $compiler->registerFunction(
                        $function["@"]["name"],
                        create_function($function["@"]["params"], $functionBody)
                    );
                }
            }

            try {
                $project = $this->project;
                $fileExtension = $this->getFileExtension();
                
                $compiler->setImportCallback(
                    function ($url) use ($project, $resolver, $inputFilename, $fileExtension) {
                        // @import file extension is optional
                        if (pathinfo($url, PATHINFO_EXTENSION) == "") {
                            $url .= ".{$fileExtension}";
                        }
                        
                        $resolvedPath = $resolver->realpath($project, $url);
                        if ($resolvedPath === false) {
                            $resolvedPath = realpath($url);

                            if ($resolvedPath === false) {
                                $resolvedPath = realpath(
                                    dirname($inputFilename).DIRECTORY_SEPARATOR.$url
                                );
                            }
                        }

                        if ($resolvedPath !== false) {
                            return $resolvedPath;
                        }
                        
                        return null;
                    }
                );
                
                $buffer = $compiler->compile($buffer, $inputFilename);
            } catch (\Exception $ex) {
                throw new \RPI\Utilities\ContentBuild\Processors\Leafo\Exceptions\CompilerError(
                    "{$this->getFileExtension()} compile error in '".realpath($inputFilename)."'",
                    null,
                    $ex
                );
            }
        }
        
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor
    ) {
        
    }
    
    abstract protected function getCompiler();
    
    abstract protected function getFileExtension();
}
