<?php

namespace RPI\Utilities\ContentBuild\Processors;

class LESS implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.4";

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
    
    public static function getVersion()
    {
        return "v".self::VERSION." - lessphp ".\lessc::$VERSION;
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
        if (pathinfo($inputFilename, PATHINFO_EXTENSION) == "less") {
            $this->project->getLogger()->info("Compiling LESS '$inputFilename'");
            
            $less = new \RPI\Utilities\ContentBuild\Processors\LESS\LessCompiler();
            $less->debug = $processor->debug;

            if (isset($this->customFunctions)) {
                foreach ($this->customFunctions as $function) {
                    if (!isset($function["@"], $function["@"]["name"])) {
                        throw new \Exception(
                            "Custom function missing name attribute: ".
                            str_replace("array (", "", var_export($function, true))
                        );
                    }
                    
                    if (!isset($function["@"], $function["@"]["params"])) {
                        throw new \Exception(
                            "Custom function '{$function["@"]["name"]}' missing params attribute"
                        );
                    }
                    
                    if (!isset($function["#"]) || trim($function["#"]) == "") {
                        throw new \Exception(
                            "Custom function '{$function["@"]["name"]}' missing code in function body"
                        );
                    }
                    
                    $functionBody = <<<EOT
                        try {
                            {$function["#"]}
                        } catch (\Exception \$ex) {
                            throw new \Exception(
                                "Custom function '{$function["@"]["name"]}' caused an exception: ".\$ex->getMessage()
                            );
                        }
EOT;
                    $less->registerFunction(
                        $function["@"]["name"],
                        create_function($function["@"]["params"], $functionBody)
                    );
                }
            }

            try {
                $project = $this->project;
                
                $less->setImportCallback(
                    function ($url) use ($project, $resolver, $inputFilename) {
                        // LESS allows @import file extension (.less) to be optional
                        if (pathinfo($url, PATHINFO_EXTENSION) == "") {
                            $url .= ".less";
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
                
                $buffer = $less->compile($buffer, $inputFilename);
            } catch (\Exception $ex) {
                throw new \Exception("LESS compile error in '".realpath($inputFilename)."'", null, $ex);
            }
        }
        
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor
    ) {
        
    }
}
