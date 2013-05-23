<?php

namespace RPI\Utilities\ContentBuild\Plugins;

class YuglifyCompressor implements \RPI\Utilities\ContentBuild\Lib\Model\Plugin\ICompressor
{
    const VERSION = "1.0.0";
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
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
        
        $project->getLogger()->info("Creating '".__CLASS__."' ({$this->getVersion()})");
    }
    
    public static function getVersion()
    {
        $output = \RPI\Console\Helpers\Console::run(
            "yuglify",
            "-v"
        );
        
        return "v".self::VERSION.(is_array($output) && count($output) > 0 ? " - yuglify {$output[0]}" : null);
    }
    
    public function compressFile($filename, $type, $outputFilename)
    {
        $helpInstallation = <<<EOT
See https://github.com/yui/yuglify for instructions on how to install and setup yuglify
EOT;
        $output = \RPI\Console\Helpers\Console::run(
            "yuglify",
            $filename,
            $helpInstallation
        );
        
        $this->project->getLogger()->notice($output);
        
        unlink($filename);
        
        return true;
    }
}
