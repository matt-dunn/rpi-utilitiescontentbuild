<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Images implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.10";

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
        if ($processorIndex != count($processor->getProcessors()) - 1) {
            throw new \Exception("Processor '".__CLASS__."' must be configured as the last processor");
        }
        
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
        
        $buffer = preg_replace_callback(
            "/url\((.*?)\)/sim",
            function ($matches) use ($resolver, $project, $inputFilename, &$files, $outputFilename, $debugPath) {
                $imageMatch = $matches[1];
            
                $resolvedPath = $imageUrl = $resolver->realpath($project, $imageMatch);
                if ($imageUrl === false) {
                    $imageUrl = realpath(dirname($inputFilename)."/".$imageMatch);
                }

                if ($imageUrl === false) {
                    $event = new \RPI\Utilities\ContentBuild\Events\ImageCheckAvailability(
                        array(
                            "imageLocation" => dirname($inputFilename)."/".$imageMatch,
                            "imageUri" => $imageMatch
                        )
                    );
                    \RPI\Foundation\Event\Manager::fire($event);
                    
                    if ($event->getReturnValue() !== true) {
                        $project->getLogger()->error(
                            "Unable to locate image '{$imageMatch}' in '$inputFilename'"
                        );
                    }
                } else {
                    if ($resolvedPath !== false) {
                        $imagePath = $resolver->getRelativePath($project, $imageMatch);
                        
                        $files[$imageUrl] = array(
                            "imagePath" => $imageMatch,
                            "sourceDocument" => $inputFilename,
                            "sourceFile" => $imageUrl,
                            "destinationFile" => dirname($outputFilename)."/".$imagePath,
                            "destinationFileDebug" =>
                                (isset($debugPath) ? $debugPath."/".$imagePath : null)
                        );
                        
                        $imageMatch = $imagePath;
                    } else {
                        $files[$imageUrl] = array(
                            "imagePath" => $imageMatch,
                            "sourceDocument" => $inputFilename,
                            "sourceFile" => $imageUrl,
                            "destinationFile" => dirname($outputFilename)."/".str_replace("../", "", $imageMatch),
                            "destinationFileDebug" =>
                                (isset($debugPath) ? $debugPath."/".str_replace("../", "", $imageMatch) : null)
                        );
                    }
                }
                
                return "url($imageMatch)";
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
        $project = $this->project;
        
        return preg_replace_callback(
            "/url\((.*?)\)/sim",
            function ($matches) use ($resolver, $project) {
                $imageMatch = $matches[1];
                
                $imageUrl = $resolver->realpath($project, $imageMatch);
                if ($imageUrl !== false) {
                    $imageMatch = $resolver->getRelativePath($project, $imageMatch);
                }
                
                return "url($imageMatch)";
            },
            $buffer
        );
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
                $basePaths[$this->project->basePath."/".$this->project->appRoot."/".$build->outputDirectory] = true;
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
                $this->project->getLogger()->debug("Removing empty directories in '$basePath'");
                \RPI\Foundation\Helpers\FileUtils::removeEmptySubfolders($basePath);
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
