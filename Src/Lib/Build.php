<?php

namespace RPI\Utilities\ContentBuild\Lib;

use Ulrichsg\Getopt;

class Build
{
    const COMPRESSOR_JAR = "yuicompressor-2.4.7.jar";
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor
     */
    private $processor = null;
    
    /**
     *
     * @var string
     */
    private $configurationFile = null;
    
    private $yuicompressorLocation = null;
    
    private $basePath = null;
    
    public function __construct(\RPI\Utilities\ContentBuild\Lib\Processor $processor)
    {
        $this->processor = $processor;
        
        $getopt = new Getopt(
            array(
                array("h", "help", Getopt::NO_ARGUMENT, "Show this help"),
                array("l", "loglevel", Getopt::REQUIRED_ARGUMENT, "Define the log level"),
                array("c", "config", Getopt::REQUIRED_ARGUMENT, "Location of the configuration file")
            )
        );
        
        try {
            $getopt->parse();
        } catch (\UnexpectedValueException $ex) {
            echo $ex->getMessage()."\r\n";
            exit(1);
        }
        
        if ($getopt->getOption("help")) {
            $getopt->showHelp();
            exit;
        }
        
        $logLevel = $getopt->getOption("loglevel");
        if (isset($logLevel)) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::setLogLevel($logLevel);
        }

        $this->configurationFile = $getopt->getOption("config");
        
        $project = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project($this->configurationFile);
        
        var_dump($project->prefix);
        
        $this->yuicompressorLocation = dirname(__FILE__)."/../../vendor/yui/yuicompressor/build/".self::COMPRESSOR_JAR;
        if (!file_exists($this->yuicompressorLocation)) {
            throw new \Exception("Unable to find yuicompressor (".$this->yuicompressorLocation.")");
        }
        
        $this->basePath = dirname($this->configurationFile);
    }
    
    public function run()
    {
        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::$displayShutdownInformation = true;

        $this->processor->process();
    }
}
