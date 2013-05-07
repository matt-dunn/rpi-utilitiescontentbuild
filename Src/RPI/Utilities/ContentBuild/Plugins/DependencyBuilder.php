<?php

namespace RPI\Utilities\ContentBuild\Plugins;

class DependencyBuilder implements \RPI\Utilities\ContentBuild\Lib\Model\IPlugin
{
    const VERSION = "2.0.1";
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    private $project = null;
    
    /**
     *
     * @var array
     */
    private $buildFiles = array();
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
    }
    
    public function __destruct()
    {
    }

    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    public function build(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver
    ) {
        $this->buildFiles = array();
        
        foreach ($this->project->builds as $build) {
            if (isset($build->files)) {
                foreach ($build->files as $file) {
                    $dependentFiles = array();
                    $this->buildFileList(
                        $build,
                        $resolver,
                        $this->getInputFileName($build, $resolver, $file),
                        $dependentFiles
                    );
                }
            }
        }
        
        return $this->buildFiles;
    }
    
    private function getInputFileName(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $file,
        $basePath = null
    ) {
        $realpath = $resolver->realpath($this->project, $file);
        if ($realpath === false) {
            $realpath = realpath(
                (isset($basePath) ? $basePath : $this->project->basePath)."/".$build->buildDirectory.$file
            );
        }
        
        if ($realpath === false) {
            $realpath = $file;
        }
        
        if (!\RPI\Foundation\Helpers\FileUtils::exists($realpath)) {
            throw new \Exception("Unable to locate input file '$file'");
        }
        
        return $realpath;
    }
    
    private function getDependencyFileType($inputFilename)
    {
        $filesSearch = \RPI\Foundation\Helpers\FileUtils::find(
            dirname($inputFilename),
            "*".pathinfo($inputFilename, PATHINFO_FILENAME).".dependencies.*",
            null,
            false
        );
        
        $type = null;
        $files = array_keys($filesSearch);
        $dependenciesFile = reset($files);
        if ($dependenciesFile !== false) {
            $type = pathinfo($dependenciesFile, PATHINFO_EXTENSION);
        }
        
        return $type;
    }
    
    private function buildFileList(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $dependentFiles
    ) {
        if (!\RPI\Foundation\Helpers\FileUtils::exists($inputFilename)) {
            throw new \Exception("Unable to locate input file '$inputFilename'");
        }
        $this->project->getLogger()->notice(
            "Building dependencies: [".$build->name."] ".$inputFilename
        );

        $type = pathinfo($inputFilename, PATHINFO_EXTENSION);
        if (!isset($type) || $type === false || $type == "") {
            $type = $build->type;
        }
        $buildType = $build->name."_".$type;

        if (!isset($this->buildFiles[$buildType])) {
            $this->buildFiles[$buildType] = Array();
        }

        $dependenciesFileType = $this->getDependencyFileType($inputFilename);
        if (isset($dependenciesFileType)) {
            $dependenciesFile =
                dirname($inputFilename)."/".pathinfo($inputFilename, PATHINFO_FILENAME).
                ".dependencies.".$dependenciesFileType;
            if (file_exists($dependenciesFile)) {
                $this->project->getLogger()->notice(
                    "Found dependencies file: ".$dependenciesFile
                );

                if (array_search($inputFilename, $dependentFiles) !== false) {
                    $dependenciesFile = dirname(
                        end($dependentFiles)
                    )."/".pathinfo(end($dependentFiles), PATHINFO_FILENAME).".dependencies.".$dependenciesFileType;
                    throw new \Exception(
                        "Circular reference detected in [".$build->name."] - Problem file: ".
                        $inputFilename." in ".$dependenciesFile
                    );
                }

                array_push($dependentFiles, $inputFilename);

                $dependencyClassname =
                    "\\RPI\\Utilities\\ContentBuild\\Lib\\Dependencies\\".ucfirst($dependenciesFileType)."\\Dependency";
                if (!class_exists($dependencyClassname)) {
                    throw new \Exception("Dependency type '$dependenciesFileType' not supported");
                }
                
                $dependency = new $dependencyClassname($dependenciesFile);

                $this->project->getLogger()->debug(
                    "Processing ".count($dependency->files)." dependencies"
                );

                foreach ($dependency->files as $dependency) {
                    $filename = $this->getInputFileName(
                        $build,
                        $resolver,
                        $dependency["name"],
                        dirname($inputFilename)
                    );
                    if (file_exists($filename)) {
                        $this->project->getLogger()->debug(
                            "Found dependency '".$filename."' - ".$inputFilename
                        );
                        $this->buildFileList($build, $resolver, $filename, $dependentFiles);
                        
                        $buildTypeDependency = null;
                        if (isset($dependency["type"])) {
                            $buildTypeDependency = $build->name."_".$dependency["type"];
                        } else {
                            $buildTypeDependency = $build->name."_".pathinfo($filename, PATHINFO_EXTENSION);
                        }
                        
                        $this->addUniqueFileToList($build, $filename, $buildTypeDependency);
                    } else {
                        throw new \Exception(
                            "Cannot find file '$filename' in '$dependenciesFile'"
                        );
                    }
                }
            }
        }

        $this->addUniqueFileToList($build, $inputFilename, $buildType);
    }
    
    private function addUniqueFileToList($build, $filename, $buildType)
    {
        if (isset($build->externalDependenciesNames)) {
            $names = explode(",", $build->externalDependenciesNames);
            for ($i = 0; $i < count($names); $i++) {
                if (isset($this->buildFiles[$names[$i]."_".$build->type])) {
                    if (array_search($filename, $this->buildFiles[$names[$i]."_".$build->type]) !== false) {
                        return false;
                    }
                } else {
                    $this->project->getLogger()->warning(
                        "[".$build->name."] Unable to locate external dependency '".$names[$i]."' for ".$filename
                    );
                }
            }
        }

        if (array_search($filename, $this->buildFiles[$buildType]) === false) {
            array_push($this->buildFiles[$buildType], $filename);

            return true;
        }
    }
}
