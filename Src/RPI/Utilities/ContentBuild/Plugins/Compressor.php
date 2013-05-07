<?php

namespace RPI\Utilities\ContentBuild\Plugins;

class Compressor implements \RPI\Utilities\ContentBuild\Lib\Model\IPlugin
{
    const VERSION = "2.4.7";
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    private $project = null;
    
    /**
     *
     * @var string
     */
    private $yuicompressorLocation = null;
    
    /**
     *
     * @var boolean
     */
    private $hasExtractedComptessor = false;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
        $this->yuicompressorLocation = __DIR__."/../../../../../vendor/yui/yuicompressor/build/yuicompressor-".self::VERSION.".jar";
    }
    
    public function __destruct()
    {
        if ($this->hasExtractedComptessor) {
            unlink($this->yuicompressorLocation);
            $this->project->getLogger()->debug(
                "Deleted extracted yuicompressor '{$this->yuicompressorLocation}'"
            );
        }
    }

    public static function getVersion()
    {
        return "v".self::VERSION." (yuicompressor)";
    }
    
    public function compressFile($filename, $type, $outputFilename)
    {
        if (!file_exists($this->yuicompressorLocation)) {
            throw new \Exception("Unable to find yuicompressor (".$this->yuicompressorLocation.")");
        }
        
        if (\Phar::running() !== "" && !$this->hasExtractedComptessor) {
            $this->project->getLogger()->notice("Extracting yuicompressor");
            $tempYuiCompressorLocation = sys_get_temp_dir()."/".self::COMPRESSOR_JAR;
            copy($this->yuicompressorLocation, $tempYuiCompressorLocation);
            $this->yuicompressorLocation = $tempYuiCompressorLocation;
            $this->hasExtractedComptessor = true;
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
                $this->project->getLogger()->debug("YUI Compressor returned this for '$filename': ".implode("\r\n    ", $output));
            }
        } else {
            $this->project->getLogger()->debug("Nothing to compress: ".$outputFilename);
        }
    }
}
