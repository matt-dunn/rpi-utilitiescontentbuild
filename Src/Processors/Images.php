<?php

namespace RPI\Utilities\ContentBuild\Processors;

class Images implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    private $imageFiles = array();
    
    public function getOptions()
    {
        return null;
    }

    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $debugPath,
        $buffer
    ) {
        $files = $this->imageFiles;
        
        preg_replace_callback(
            "/url\((.*?)\)/sim",
            function ($matches) use ($inputFilename, &$files, $outputFilename, $debugPath) {
                $imageUrl = realpath(dirname($inputFilename)."/".$matches[1]);
                if ($imageUrl === false) {
                    \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Unable to locate image '{$matches[1]}' in '$inputFilename'", LOG_ERR);
                } else {
                    $files[$imageUrl] = array(
                        "imagePath" => $matches[1],
                        "sourceDocument" => $inputFilename,
                        "sourceFile" => $imageUrl,
                        "destinationFile" => dirname($outputFilename)."/".str_replace("../", "", $matches[1]),
                        "destinationFileDebug" => (isset($debugPath) ? $debugPath."/".str_replace("../", "", $matches[1]) : null)
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
        $inputFilename,
        $outputFilename,
        $debugPath,
        $buffer
    ) {
        return $buffer;
    }
    
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
        var_dump($this->imageFiles);
        
        if (count($this->imageFiles) > 0) {
            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Copying images", LOG_DEBUG);
            self::copyCSSImageFiles($this->imageFiles);
        }

        // TODO: cleanup...
//        $basePaths = array();
//        $outputBasePath = realpath(dirname($project->configurationFile).$project->basePath);
//        foreach ($project->builds as $build) {
//            if ($build->type == "css") {
//                $basePaths[$outputBasePath.$build->outputDirectory] = true;
//                if ($this->includeDebug) {
//                    $debugPath = self::getDebugPath($outputBasePath.$build->outputDirectory, $build->type);
//                    $basePaths[$debugPath."/"] = true;
//                }
//            }
//        }
//        
//        $imagePaths = array();
//        if (count($this->imageFiles) > 0) {
//            foreach($this->imageFiles as $image) {
//                $imagePaths[strtolower($image["destinationFile"])] = true;
//                if (isset($image["destinationFileDebug"])) {
//                    $imagePaths[strtolower($image["destinationFileDebug"])] = true;
//                }
//                \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Processed image: {$image["destinationFile"]}", LOG_DEBUG);
//            }
//        } else {
//            \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("No images processed", LOG_DEBUG);
//        }
//        
//        if (count($imagePaths) > 0) {
//            $basePaths = array_keys($basePaths);
//            foreach($basePaths as $basePath) {
//                self::cleanupImages($imagePaths, $basePath);
//            }
//        }
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

        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Copying '$sourceImageFile' to '$destImageFile'", LOG_DEBUG);
        copy($sourceImageFile, $destImageFile);
    }
    
    private static function cleanupImages(array $files, $basePath)
    {
        $filesSearch = array();
        \RPI\Framework\Helpers\FileUtils::find($basePath, "/.*\.(png|gif|jpg|jpeg)/", $filesSearch, true, false);
        $existingFiles = array_keys($filesSearch);
        
        foreach ($existingFiles as $existingFile) {
            if (!isset($files[strtolower($existingFile)])) {
                \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log("Deleting unused file '$existingFile'", LOG_INFO);
                unlink($existingFile);
            }
        }
    }
}
