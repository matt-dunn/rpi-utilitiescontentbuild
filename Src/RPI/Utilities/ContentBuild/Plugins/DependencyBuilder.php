<?php

namespace RPI\Utilities\ContentBuild\Plugins;

class DependencyBuilder implements \RPI\Utilities\ContentBuild\Lib\Model\Plugin\IDependencyBuilder
{
    const VERSION = "2.0.2";
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->project = $project;
        
        $project->getLogger()->info("Creating '".__CLASS__."' ({$this->getVersion()})");
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
        $buildFiles = array();
        
        foreach ($this->project->builds as $build) {
            if (isset($build->files)) {
                foreach ($build->files as $file) {
                    $dependentFiles = array();
                    $buildFiles = array_merge(
                        $buildFiles,
                        $this->buildFileList(
                            $build,
                            $resolver,
                            $this->getInputFileName($build, $resolver, $file),
                            $dependentFiles,
                            $buildFiles
                        )
                    );
                }
            }
        }
        
        return $buildFiles;
    }
    
    protected function getInputFileName(
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
    
    protected function getDependencyFileType($inputFilename)
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
    
    protected function buildFileList(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $dependentFiles,
        array $buildFiles = array()
    ) {
        if (!\RPI\Foundation\Helpers\FileUtils::exists($inputFilename)) {
            throw new \Exception("Unable to locate input file '$inputFilename'");
        }
        $this->project->getLogger()->notice(
            "Building dependencies: [".$build->name."] ".$inputFilename
        );

        $type = strtolower(pathinfo($inputFilename, PATHINFO_EXTENSION));
        if (!($type == "css" || $type == "js")) {
            $type = $build->type;
        }
        
        $buildType = $build->name."_".$type;

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

                $dependencyConfig = new \RPI\Utilities\ContentBuild\Lib\Dependency(
                    $this->project->getLogger(),
                    $dependenciesFile
                );

                $this->project->getLogger()->debug(
                    "Processing ".count($dependencyConfig->dependencies->files)." dependencies"
                );

                foreach ($dependencyConfig->dependencies->files as $dependency) {
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
                        
                        $buildTypeDependency = null;
                        if (isset($dependency["type"])) {
                            $buildTypeDependency = $build->name."_".$dependency["type"];
                        } else {
                            $buildTypeDependency = $build->name."_".pathinfo($filename, PATHINFO_EXTENSION);
                        }
                        
                        $buildFiles = array_merge(
                            $buildFiles,
                            $this->addUniqueFileToList(
                                $build,
                                $filename,
                                $buildTypeDependency,
                                array_merge(
                                    $buildFiles,
                                    $this->buildFileList($build, $resolver, $filename, $dependentFiles, $buildFiles)
                                )
                            )
                        );
                    } else {
                        throw new \Exception(
                            "Cannot find file '$filename' in '$dependenciesFile'"
                        );
                    }
                }
            }
        }

        return $this->addUniqueFileToList($build, $inputFilename, $buildType, $buildFiles);
    }
    
    protected function addUniqueFileToList($build, $filename, $buildType, array $buildFiles)
    {
        if (isset($build->externalDependenciesNames)) {
            $names = explode(",", $build->externalDependenciesNames);
            for ($i = 0; $i < count($names); $i++) {
                if (isset($buildFiles[$names[$i]."_".$build->type])) {
                    if (array_search($filename, $buildFiles[$names[$i]."_".$build->type]) !== false) {
                        return $buildFiles;
                    }
                } else {
                    $this->project->getLogger()->warning(
                        "[".$build->name."] Unable to locate external dependency '".$names[$i]."' for ".$filename
                    );
                }
            }
        }

        if (!isset($buildFiles[$buildType])) {
            $buildFiles[$buildType] = array();
        }

        if (array_search($filename, $buildFiles[$buildType]) === false) {
            array_push($buildFiles[$buildType], $filename);
        }
        
        return $buildFiles;
    }
}
