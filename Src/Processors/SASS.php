<?php

namespace RPI\Utilities\ContentBuild\Processors;

class SASS implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    public function getOptions()
    {
        return null;
    }

    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $processorIndex
    ) {
        if ($processorIndex != 0) {
            throw new \Exception("Processor '".__CLASS__."' must be configured as the first processor");
        }
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $buffer
    ) {
        return $buffer;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $inputFilename,
        $buffer
    ) {
        if (pathinfo($inputFilename, PATHINFO_EXTENSION) == "scss") {
            $output = null;
            $ret = null;
            
            $cachepath = dirname($project->configurationFile)."/.sass-cache";

            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Compiling SASS '$inputFilename'", LOG_INFO);
            
            exec(
                "sass --update $inputFilename --cache-location $cachepath",
                $output,
                $ret
            );

            if ($ret != 0) {
                switch ($ret) {
                    case 126:
                        throw new \Exception("Permission problems running SASS");
                        break;
                    case 127:
                        throw new \Exception("SASS not installed or cannot be found. Check installation of SASS.");
                        break;
                    default:
                        throw new \Exception(
                            "There was a problem compiling SASS '$inputFilename'. ".
                            "Could be a write permission problem. Returned '$ret' and output:".
                            print_r($output, true)
                        );
                }
            } elseif (isset($output)) {
                $logLevel = LOG_INFO;
                if ($ret !== 0) {
                    $logLevel = LOG_ERR;
                }
                foreach ($output as $line) {
                    \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log($line, $logLevel);
                }
            }
            
            $buffer = file_get_contents(str_replace(".scss", ".css", $inputFilename));
        }
        
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
        
    }
}
