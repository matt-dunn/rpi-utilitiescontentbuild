<?php

namespace RPI\Utilities\ContentBuild\Processors;

class HashImages implements \RPI\Utilities\ContentBuild\Lib\Model\Processor\IProcessor
{
    const VERSION = "1.0.1";

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

        if (!$this->processor->debug) {
            return preg_replace_callback(
                "/(background[-\w\s\d]*):([\/\\#_-\w\d\s]*?)url\s*\(\s*'*\"*(.*?)'*\"*\s*\)/sim",
                function ($matches) use ($resolver, $project, $build, $hashAlgorithm) {
                    $imageMatch = $matches[3];

                    if (strtolower(substr($imageMatch, 0, 5)) !== "data:") {
                        $querystring = parse_url($imageMatch, PHP_URL_QUERY);
                        if(isset($querystring)) {
                            $parts = null;
                            parse_str($querystring, $parts);
                            if (!isset($parts["hash"])) {
                                $fileHash = hash_file($hashAlgorithm, dirname($build->outputFilename)."/$imageMatch");
                                $imageMatch .= "&hash={$fileHash}";
                            }
                        } else {
                            $fileHash = hash_file($hashAlgorithm, dirname($build->outputFilename)."/$imageMatch");
                            $imageMatch .= "?hash={$fileHash}";
                        }
                    }
                    return "{$matches[1]}:{$matches[2]}url($imageMatch)";
                },
                $buffer
            );
        }
        
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
