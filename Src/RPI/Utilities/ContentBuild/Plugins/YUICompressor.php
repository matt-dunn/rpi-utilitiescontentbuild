<?php

namespace RPI\Utilities\ContentBuild\Plugins;

class YUICompressor implements \RPI\Utilities\ContentBuild\Lib\Model\Plugin\ICompressor
{
    const VERSION = "1.0.1";
    const VERSION_YUI = "2.4.7";
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    /**
     *
     * @var string
     */
    protected $yuicompressorLocation = null;
    
    /**
     *
     * @var boolean
     */
    protected $hasExtractedCompressor = false;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
        $this->yuicompressorLocation =
            __DIR__."/../../../../../vendor/yui/yuicompressor/build/yuicompressor-".self::VERSION_YUI.".jar";
        
        $project->getLogger()->info("Creating '".__CLASS__."' ({$this->getVersion()})");
    }
    
    public function __destruct()
    {
        if ($this->hasExtractedCompressor) {
            unlink($this->yuicompressorLocation);
            $this->project->getLogger()->debug(
                "Deleted extracted yuicompressor '{$this->yuicompressorLocation}'"
            );
        }
    }

    public static function getVersion()
    {
        return "v".self::VERSION." - yuicompressor ".self::VERSION_YUI;
    }
    
    public function compressFile($filename, $type, $outputFilename)
    {
        if (!file_exists($this->yuicompressorLocation)) {
            throw new \Exception("Unable to find yuicompressor (".$this->yuicompressorLocation.")");
        }
        
        if (\Phar::running() !== "" && !$this->hasExtractedCompressor) {
            $this->project->getLogger()->notice("Extracting yuicompressor");
            $tempYuiCompressorLocation = sys_get_temp_dir()."/".basename($this->yuicompressorLocation);
            copy($this->yuicompressorLocation, $tempYuiCompressorLocation);
            $this->yuicompressorLocation = $tempYuiCompressorLocation;
            $this->hasExtractedCompressor = true;
        }
        
        if (file_exists($outputFilename)) {
            unlink($outputFilename);
        }
        
        if (file_exists($filename)) {
            $this->project->getLogger()->notice("Compressing: ".$outputFilename."...");

            $output = \RPI\Console\Helpers\Console::run(
                "java -jar {$this->yuicompressorLocation}",
                "--verbose --type ".$type." ".$filename." -o ".$outputFilename
            );
                
            $this->project->getLogger()->debug($output);
            
            unlink($filename);
        } else {
            $this->project->getLogger()->debug("Nothing to compress: ".$outputFilename);
        }
        
        return true;
    }
}
