<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Images implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    private $imageFiles = array();
    private $timestamp = null;
    const VERSION = "1.0.8";

    public function getVersion()
    {
        return "v".self::VERSION;
    }
    
    
    public function getOptions()
    {
        return null;
    }

    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $processorIndex
    ) {
        $this->timestamp = microtime(true) - 1;
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $buffer
    ) {
        $files = $this->imageFiles;
        $debugPath = $build->debugPath;
        
        preg_replace_callback(
            "/url\((.*?)\)/sim",
            function ($matches) use ($inputFilename, &$files, $outputFilename, $debugPath) {
                $imageUrl = realpath(dirname($inputFilename)."/".$matches[1]);
                if ($imageUrl === false) {
                    $event = new \RPI\Utilities\ContentBuild\Events\ImageCheckAvailability(
                        array(
                            "imageLocation" => dirname($inputFilename)."/".$matches[1],
                            "imageUri" => $matches[1]
                        )
                    );
                    \RPI\Utilities\ContentBuild\Event\Manager::fire($event);
                    
                    if ($event->getReturnValue() !== true) {
                        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                            "Unable to locate image '{$matches[1]}' in '$inputFilename'",
                            LOG_ERR
                        );
                    }
                } else {
                    $files[$imageUrl] = array(
                        "imagePath" => $matches[1],
                        "sourceDocument" => $inputFilename,
                        "sourceFile" => $imageUrl,
                        "destinationFile" => dirname($outputFilename)."/".str_replace("../", "", $matches[1]),
                        "destinationFileDebug" =>
                            (isset($debugPath) ? $debugPath."/".str_replace("../", "", $matches[1]) : null)
                    );
                }
            },
            $buffer
        );

        $this->imageFiles = array_merge($this->imageFiles, $files);
        
        return $buffer;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $inputFilename,
        $buffer
    ) {
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
        if (count($this->imageFiles) > 0) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Copying images", LOG_DEBUG);
            self::copyCSSImageFiles($this->imageFiles);
        }

        $basePaths = array();
        foreach ($project->builds as $build) {
            if ($build->type == "css") {
                $basePaths[$project->basePath."/".$build->outputDirectory] = true;
                if ($project->includeDebug) {
                    $basePaths[$build->debugPath."/"] = true;
                }
            }
        }
        
        if (count($basePaths) > 0) {
            $basePaths = array_keys($basePaths);
            foreach ($basePaths as $basePath) {
                \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Cleaning images in '$basePath'", LOG_DEBUG);
                self::cleanupImages($basePath, $this->timestamp);
            }
        }
    }
    
    
    private static function copyCSSImageFiles(array $files)
    {
        foreach ($files as $fileDetails) {
            self::copyCSSImageFile($fileDetails["sourceFile"], $fileDetails["destinationFile"]);
            if (isset($fileDetails["destinationFileDebug"])) {
                self::copyCSSImageFile($fileDetails["sourceFile"], $fileDetails["destinationFileDebug"]);
            }
        }
    }
    
    private static function copyCSSImageFile($sourceImageFile, $destImageFile)
    {
        if (!file_exists(dirname($destImageFile))) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Creating image path: ".$destImageFile, LOG_DEBUG);
            $oldumask = umask(0);
            mkdir(dirname($destImageFile), 0755, true);
            umask($oldumask);
        }

        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
            "Copying '$sourceImageFile' to '$destImageFile'",
            LOG_NOTICE
        );
        copy($sourceImageFile, $destImageFile);
    }
    
    private static function cleanupImages($basePath, $timestamp)
    {
        $filesSearch = \RPI\Utilities\ContentBuild\Lib\Helpers\FileUtils::find(
            $basePath,
            "*.png|*.gif|*.jpg|*.jpeg"
        );
        $existingFiles = array_keys($filesSearch);
        
        foreach ($existingFiles as $existingFile) {
            if (filemtime($existingFile) < $timestamp) {
                \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                    "Deleting unused file '$existingFile'",
                    LOG_INFO
                );
                unlink($existingFile);
            }
        }
    }
}
