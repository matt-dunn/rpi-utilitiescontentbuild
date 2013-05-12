<?php

namespace RPI\Utilities\ContentBuild\Processors;

class LESS implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.0";

    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    private $project = null;

    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
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
            
            $less = new \lessc();
            
            try {
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
                
                $project = $this->project;
                
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
