<?php

namespace RPI\Utilities\ContentBuild\Plugins;

class YUICompressor implements \RPI\Utilities\ContentBuild\Lib\Model\IPlugin
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

            $options = "  --verbose";
            
            $output = null;
            $ret = null;
            
            exec(
                "java -jar ".$this->yuicompressorLocation.$options." --type ".$type." ".
                $filename." -o ".$outputFilename." 2>&1 1> /dev/stdout",
                $output,
                $ret
            );
            
            unlink($filename);
            
            if ($ret != 0) {
                throw new \Exception(
                    "ERROR COMPRESSING FILE (returned $ret): ".$filename.". In addition: ".implode("\r\n    ", $output)
                );
            } elseif (isset($output) && count($output) > 0) {
                $this->project->getLogger()->debug(
                    "YUI Compressor returned this for '$filename': ".implode("\r\n    ", $output)
                );
            }
        } else {
            $this->project->getLogger()->debug("Nothing to compress: ".$outputFilename);
        }
    }
}
