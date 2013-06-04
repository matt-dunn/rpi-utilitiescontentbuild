<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Images implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.12";

    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor 
     */
    protected $processor = null;
    
    /**
     *
     * @var integer
     */
    protected $timestamp = null;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->processor = $processor;
        $this->project = $project;
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    public function init(
        $processorIndex
    ) {
        $this->timestamp = microtime(true) - 1;
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    ) {
        $outputFilename = $build->outputFilename;
        $imageFiles = array();
        $debugPath = $build->debugPath;
        $project = $this->project;
        
        \RPI\Foundation\Helpers\Utils::pregReplaceCallbackOffset(
            "/(background[-\w\s\d]*):([\/\\#_-\w\d\s]*?)url\s*\(\s*'*\"*(.*?)'*\"*\s*\)/sim",
            function ($matches) use ($resolver, $project, $inputFilename, &$imageFiles, $outputFilename, $debugPath) {
                $imageMatch = $matches[3][0];
                
                if (strtolower(substr($imageMatch, 0, 5)) !== "data:") {
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
                            throw new \RPI\Foundation\Exceptions\FileNotFound(
                                "Unable to locate image '{$imageMatch}'".
                                " in '$inputFilename{$matches[3]["fileDetails"]}'"
                            );
                        }
                    } else {
                        if ($resolvedPath !== false) {
                            $imagePath = $resolver->getRelativePath($project, $imageMatch);

                            $destinationFile = dirname($outputFilename)."/".$imagePath;
                            $imageFiles[$destinationFile] = array(
                                "imagePath" => $imageMatch,
                                "sourceDocument" => $inputFilename,
                                "sourceFile" => $imageUrl,
                                "destinationFile" => $destinationFile,
                                "destinationFileDebug" =>
                                    (isset($debugPath) ? $debugPath."/".$imagePath : null)
                            );

                            $imageMatch = $imagePath;
                        } else {
                            $destinationFile = dirname($outputFilename)."/".str_replace("../", "", $imageMatch);
                            $imageFiles[$destinationFile] = array(
                                "imagePath" => $imageMatch,
                                "sourceDocument" => $inputFilename,
                                "sourceFile" => $imageUrl,
                                "destinationFile" => $destinationFile,
                                "destinationFileDebug" =>
                                    (isset($debugPath) ? $debugPath."/".str_replace("../", "", $imageMatch) : null)
                            );
                        }
                    }
                }
                
                return "{$matches[1][0]}:{$matches[2][0]}url($imageMatch)";
            },
            $buffer
        );

        if (count($imageFiles) > 0) {
            $this->project->getLogger()->debug("Copying images");
            $this->copyCSSImageFiles($imageFiles);
        }
        
        return true;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    ) {
        $project = $this->project;
        
        return \RPI\Foundation\Helpers\Utils::pregReplaceCallbackOffset(
            "/(background[-\w\s\d]*):([\/\\#_-\w\d\s]*?)url\s*\(\s*'*\"*(.*?)'*\"*\s*\)/sim",
            function ($matches) use ($resolver, $project) {
                $imageMatch = $matches[3][0];
                
                if (strtolower(substr($imageMatch, 0, 5)) !== "data:") {
                    $imageUrl = $resolver->realpath($project, $imageMatch);
                    if ($imageUrl !== false) {
                        $imageMatch = $resolver->getRelativePath($project, $imageMatch);
                    }
                }
                
                return "{$matches[1][0]}:{$matches[2][0]}url($imageMatch)";
            },
            $buffer
        );
    }
    
    public function complete()
    {
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
    
    public function canProcessBuffer()
    {
        return true;
    }
    
    protected function copyCSSImageFiles(array $files)
    {
        foreach ($files as $fileDetails) {
            $this->copyCSSImageFile($fileDetails["sourceFile"], $fileDetails["destinationFile"]);
            if (isset($fileDetails["destinationFileDebug"])) {
                $this->copyCSSImageFile($fileDetails["sourceFile"], $fileDetails["destinationFileDebug"]);
            }
        }
    }
    
    protected function copyCSSImageFile($sourceImageFile, $destImageFile)
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
    
    protected function cleanupImages($basePath, $timestamp)
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
