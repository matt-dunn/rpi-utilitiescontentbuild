<?php

namespace RPI\Utilities\ContentBuild\Processors;

class LESS implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.3";

    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    private $project = null;

    /**
     *
     * @var array
     */
    private $customFunctions = null;
    
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
            
            $less = new \RPI\Utilities\ContentBuild\Processors\LESS\lessc();

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
                            "Custom function missing params attribute: ".
                            str_replace("array (", "", var_export($function, true))
                        );
                    }
                    
                    if (!isset($function["#"]) || trim($function["#"]) == "") {
                        throw new \Exception(
                            "Custom function missing function code: ".
                            str_replace("array (", "", var_export($function, true))
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
                    function ($url) use ($project, $resolver) {
                        $resolvedPath = $resolver->realpath($project, $url);
                        if ($resolvedPath !== false) {
                            return $resolvedPath;
                        }
                        
                        return null;
                    }
                );
                
                
                // Compile and resolve @import paths
                
                /*
                 * LESS @import syntax:
                 * 
                 * @import "file";
                 * @import 'file.less';
                 * @import url("file");
                 * @import url('file');
                 * @import url(file);
                 */
                
                $buffer = $less->compile(
                    preg_replace_callback(
                        "/@import\s*(?:url\s*\(\s*)?'*\"*(.*?)'*\"*\s*(?:\))?;/sim",
                        function ($matches) use ($resolver, $inputFilename, $project) {
                            $importFilename = $matches[1];
                            
                            // LESS allows @import file extension (.less) to be optional
                            if (pathinfo($importFilename, PATHINFO_EXTENSION) == "") {
                                $importFilename .= ".less";
                            }

                            $resolvedPath = $resolver->realpath($project, $importFilename);
                            if ($resolvedPath === false) {
                                $resolvedPath = realpath($importFilename);

                                if ($resolvedPath === false) {
                                    $resolvedPath = realpath(
                                        dirname($inputFilename).DIRECTORY_SEPARATOR.$importFilename
                                    );
                                }
                            }

                            if ($resolvedPath === false) {
                                throw new \Exception("Unable to locate imported file '{$importFilename}'");
                            }
                            
                            $project->getLogger()->debug("Resolved @import '$importFilename' to\n\t'$resolvedPath'");
                            
                            return "@import \"$resolvedPath\";";
                        },
                        $buffer
                    )
                );
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
