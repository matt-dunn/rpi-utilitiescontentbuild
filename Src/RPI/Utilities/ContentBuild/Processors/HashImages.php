<?php

namespace RPI\Utilities\ContentBuild\Processors;

class HashImages implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.4";

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
     * @var string
     */
    protected $hashAlgorithm = "md5";
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->processor = $processor;
        $this->project = $project;
        
        if (isset($options, $options["hashAlgorithm"])) {
            $this->hashAlgorithm = $options["hashAlgorithm"];
        }
        
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    public function init(
        $processorIndex
    ) {
    }
    
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    ) {
        return true;
    }
    
    public function process(
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    ) {
        $project = $this->project;
        $hashAlgorithm = $this->hashAlgorithm;

        return \RPI\Foundation\Helpers\Utils::pregReplaceCallbackOffset(
            "/(background[-\w\s\d]*):([\/\\#_-\w\d\s]*?)url\s*\(\s*'*\"*(.*?)'*\"*\s*\)/sim",
            function ($matches) use ($project, $build, $hashAlgorithm, $inputFilename) {
                $imageMatch = $matches[3][0];

                if (strtolower(substr($imageMatch, 0, 5)) !== "data:") {
                    $querystring = parse_url($imageMatch, PHP_URL_QUERY);
                    if (isset($querystring)) {
                        if (substr($imageMatch, 0, 1) == "/") {
                            $imageFilename = $project->basePath."/".$project->appRoot.$imageMatch;
                        } else {
                            $imageFilename = parse_url(dirname($build->outputFilename)."/$imageMatch", PHP_URL_PATH);
                        }
                        if (!file_exists($imageFilename)) {
                            throw new \RPI\Foundation\Exceptions\FileNotFound(
                                "Unable to locate image '{$imageMatch}'".
                                " in '$inputFilename{$matches[3]["fileDetails"]}'"
                            );
                        }
                        $parts = null;
                        parse_str($querystring, $parts);
                        if (!isset($parts["hash"])) {
                            $project->getLogger()->debug("Generated has for '$imageFilename'");
                            $fileHash = hash_file($hashAlgorithm, $imageFilename);
                            $imageMatch .= "&hash={$fileHash}";
                        }
                    } else {
                        if (substr($imageMatch, 0, 1) == "/") {
                            $imageFilename = $project->basePath."/".$project->appRoot.$imageMatch;
                        } else {
                            $imageFilename = dirname($build->outputFilename)."/$imageMatch";
                        }
                        if (!file_exists($imageFilename)) {
                            throw new \RPI\Foundation\Exceptions\FileNotFound(
                                "Unable to locate image '{$imageMatch}'".
                                " in '$inputFilename{$matches[3]["fileDetails"]}'"
                            );
                        }
                        $project->getLogger()->debug("Generated has for '$imageFilename'");
                        $fileHash = hash_file($hashAlgorithm, $imageFilename);
                        $imageMatch .= "?hash={$fileHash}";
                    }
                }

                return "{$matches[1][0]}:{$matches[2][0]}url($imageMatch)";
            },
            $buffer
        );
        
        return $buffer;
    }
    
    public function complete()
    {
    }
    
    public function canProcessBuffer()
    {
        return true;
    }
}
