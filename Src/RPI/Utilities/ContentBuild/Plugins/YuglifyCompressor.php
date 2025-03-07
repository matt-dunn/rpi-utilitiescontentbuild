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
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
        
        $project->getLogger()->info("Creating '".__CLASS__."' ({$this->getVersion()})");
    }
    
    public static function getVersion()
    {
        $output = null;
        
        try {
            $output = static::runCommand(
                "yuglify",
                "-v"
            );
        } catch (\RPI\Console\Exceptions\Console\NotInstalled $ex) {
            $output = array("NOT INSTALLED");
        }
        
        return "v".self::VERSION.(is_array($output) && count($output) > 0 ? " - yuglify {$output[0]}" : null);
    }
    
    public function compressFile($filename, $type, $outputFilename)
    {
        $helpInstallation = <<<EOT
See https://github.com/yui/yuglify for instructions on how to install and setup yuglify
EOT;
        $output = static::runCommand(
            "yuglify",
            $filename,
            $helpInstallation
        );

        $parts = pathinfo($filename);
        $yuglifyOutputFilename = $parts["dirname"]."/".$parts["filename"].".min.".$parts["extension"];

        if ($yuglifyOutputFilename != $outputFilename) {
            rename($yuglifyOutputFilename, $outputFilename);
            $this->project->getLogger()->debug(
                "Renaming yuglify output '$yuglifyOutputFilename' to '$outputFilename'"
            );
        }
        
        $this->project->getLogger()->debug($output);
        
        unlink($filename);
        
        return true;
    }
    
    /**
     * @codeCoverageIgnore
     */
    protected static function runCommand($command, $arguments, $helpInstallation = null)
    {
        return \RPI\Console\Helpers\Console::run($command, $arguments, $helpInstallation);
    }
}
