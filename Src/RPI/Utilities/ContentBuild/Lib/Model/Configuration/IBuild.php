<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Configuration;

/**
 * @property-read string $buildDirectory
 * @property-read array $files
 * @property-read string $name
 * @property-read string $outputDirectory
 * @property-read string $outputFilename
 * @property-read string $externalDependenciesNames
 * @property-read string $type
 * @property-read string $version
 * @property-read string $target
 * @property-read string $media
 * @property-read string $debugPath
 */
interface IBuild
{
    /**
     * @return string
     */
    public function getBuildDirectory();
    
    /**
     * @return string
     */
    public function getOutputDirectory();
    
    /**
     * @return string
     */
    public function getOutputFilename();
    
    /**
     * @return string
     */
    public function getType();
    
    /**
     * @return string
     */
    public function getVersion();
    
    /**
     * @return string
     */
    public function getName();
    
    /**
     * @return array
     */
    public function getFiles();
    
    /**
     * @return string
     */
    public function getExternalDependenciesNames();
    
    /**
     * @return string
     */
    public function getMedia();
    
    /**
     * @return string
     */
    public function getTarget();
    
    /**
     * @return string
     */
    public function getDebugPath();
}
