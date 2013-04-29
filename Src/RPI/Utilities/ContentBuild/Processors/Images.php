<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Images implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.9";

    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    private $project = null;
    
    /**
     *
     * @var array
     */
    private $imageFiles = array();
    
    /**
     *
     * @var integer
     */
    private $timestamp = null;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        $processorIndex
    ) {
        $this->timestamp = microtime(true) - 1;
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $buffer
    ) {
        $files = $this->imageFiles;
        $debugPath = $build->debugPath;
        $project = $this->project;
        
        preg_replace_callback(
            "/url\((.*?)\)/sim",
            function ($matches) use ($project, $inputFilename, &$files, $outputFilename, $debugPath) {
                $imageUrl = realpath(dirname($inputFilename)."/".$matches[1]);
                if ($imageUrl === false) {
                    $event = new \RPI\Utilities\ContentBuild\Events\ImageCheckAvailability(
                        array(
                            "imageLocation" => dirname($inputFilename)."/".$matches[1],
                            "imageUri" => $matches[1]
                        )
                    );
                    \RPI\Foundation\Event\Manager::fire($event);
                    
                    if ($event->getReturnValue() !== true) {
                        $project->getLogger()->error(
                            "Unable to locate image '{$matches[1]}' in '$inputFilename'"
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
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $buffer
    ) {
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor
    ) {
        if (count($this->imageFiles) > 0) {
            $this->project->getLogger()->debug("Copying images");
            $this->copyCSSImageFiles($this->imageFiles);
        }

        $basePaths = array();
        foreach ($this->project->builds as $build) {
            if ($build->type == "css") {
                $basePaths[$this->project->basePath."/".$build->outputDirectory] = true;
                if ($this->project->includeDebug) {
                    $basePaths[$build->debugPath."/"] = true;
                }
            }
        }
        
        if (count($basePaths) > 0) {
            $basePaths = array_keys($basePaths);
            foreach ($basePaths as $basePath) {
                $this->project->getLogger()->debug("Cleaning images in '$basePath'");
                $this->cleanupImages($basePath, $this->timestamp);
            }
        }
    }
    
    
    private function copyCSSImageFiles(array $files)
    {
        foreach ($files as $fileDetails) {
            $this->copyCSSImageFile($fileDetails["sourceFile"], $fileDetails["destinationFile"]);
            if (isset($fileDetails["destinationFileDebug"])) {
                $this->copyCSSImageFile($fileDetails["sourceFile"], $fileDetails["destinationFileDebug"]);
            }
        }
    }
    
    private function copyCSSImageFile($sourceImageFile, $destImageFile)
    {
        if (!file_exists(dirname($destImageFile))) {
            $this->project->getLogger()->debug("Creating image path: ".$destImageFile);
            $oldumask = umask(0);
            mkdir(dirname($destImageFile), 0755, true);
            umask($oldumask);
        }

        $this->project->getLogger()->notice(
            "Copying '$sourceImageFile' to '$destImageFile'"
        );
        copy($sourceImageFile, $destImageFile);
    }
    
    private function cleanupImages($basePath, $timestamp)
    {
        $filesSearch = \RPI\Foundation\Helpers\FileUtils::find(
            $basePath,
            "*.png|*.gif|*.jpg|*.jpeg"
        );
        $existingFiles = array_keys($filesSearch);
        
        foreach ($existingFiles as $existingFile) {
            if (filemtime($existingFile) < $timestamp) {
                $this->project->getLogger()->info(
                    "Deleting unused file '$existingFile'"
                );
                unlink($existingFile);
            }
        }
    }
}
