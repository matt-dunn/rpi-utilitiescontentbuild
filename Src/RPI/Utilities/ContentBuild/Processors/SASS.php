<?php

namespace RPI\Utilities\ContentBuild\Processors;

class SASS implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.3";

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
        try {
            $output = self::runSass("-v", false);
            $sassVersion = "Unable to get SASS version information";
            if (isset($output)) {
                $sassVersion = $output[0];
            }
            return "v".self::VERSION." - ".$sassVersion;
        } catch (\RPI\Utilities\ContentBuild\Processors\SASS\Exceptions\NotInstalled $ex) {
            return "SASS not installed. Try 'sudo gem install sass'";
        } 
    }
    
    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        $processorIndex
    ) {
        if ($processorIndex != 0) {
            throw new \Exception("Processor '".__CLASS__."' must be configured as the first processor");
        }
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
        if (pathinfo($inputFilename, PATHINFO_EXTENSION) == "scss") {
            $this->project->getLogger()->info("Compiling SASS '$inputFilename'");
            
            $cachepath = dirname($this->project->configurationFile)."/.sass-cache";
            self::runSass("--update $inputFilename --cache-location $cachepath");
            
            $buffer = file_get_contents(str_replace(".scss", ".css", $inputFilename));
        }
        
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor
    ) {
        
    }
    
    /**
     * 
     * @param string $command
     * @param boolean $sendNonErrorOutput
     * @return integer|null
     * 
     * @throws \Exception
     */
    private static function runSass($command, $sendNonErrorOutput = true)
    {
        $output = null;
        $ret = null;

        exec(
            "sass $command 2>&1 1> /dev/stdout",
            $output,
            $ret
        );

        if ($ret != 0) {
            switch ($ret) {
                case 126:
                    throw new \Exception("Permission problems running SASS");
                    break;
                case 127:
                    throw new \RPI\Utilities\ContentBuild\Processors\SASS\Exceptions\NotInstalled(
                        "SASS not installed or cannot be found. Check installation of SASS."
                    );
                    break;
                default:
                    throw new \Exception(
                        "There was a problem compiling SASS '$command'. ".
                        "Could be a write permission problem. Returned '$ret' and output:".
                        print_r($output, true)
                    );
            }
        } elseif (isset($output) && $sendNonErrorOutput) {
            if ($ret !== 0) {
                foreach ($output as $line) {
                    $this->project->getLogger()->error($line);
                }
            } else {
                foreach ($output as $line) {
                    $this->project->getLogger()->info($line);
                }
            }
        }
        
        return (count($output) > 0 ? $output : null);
    }
}
