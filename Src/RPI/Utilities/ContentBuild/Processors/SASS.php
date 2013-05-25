<?php

namespace RPI\Utilities\ContentBuild\Processors;

class SASS implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.4";

    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;

    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor 
     */
    protected $processor = null;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->processor = $processor;
        $this->project = $project;
    }
    
    public static function getVersion()
    {
        $output = null;
        
        try {
            $output = \RPI\Console\Helpers\Console::run(
                "sass",
                "-v"
            );
        } catch (\RPI\Console\Exceptions\Console\NotInstalled $ex) {
            $output = array("NOT INSTALLED");
        }
        
        return "v".self::VERSION.(is_array($output) && count($output) > 0 ? " - {$output[0]}" : null);
    }
    
    public function init(
        $processorIndex
    ) {
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    ) {
        return true;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    ) {
        $fileExtension = strtolower(pathinfo($inputFilename, PATHINFO_EXTENSION));
        if (in_array($fileExtension, array("scss", "sass"))) {
            $this->project->getLogger()->info("Compiling SASS ($fileExtension) '$inputFilename'");
            
            $cachepath = sys_get_temp_dir();
            
            $args = "";
            if ($fileExtension == "scss") {
                $args .= " --scss";
            }
            
            if ($this->processor->debug) {
                $args .= " --debug-info";
            }
            
            $helpInstallation = <<<EOT
See http://sass-lang.com/download.html for instructions on how to install and setup sass
EOT;
        
            $output = \RPI\Console\Helpers\Console::run(
                "sass",
                "$args $inputFilename --cache-location $cachepath/.sass-cache",
                $helpInstallation
            );

            $buffer = implode(PHP_EOL, $output);
        }
        
        return $buffer;
    }
    
    public function complete(
    ) {
        
    }
    
    public function canProcessBuffer()
    {
        return false;
    }
}
