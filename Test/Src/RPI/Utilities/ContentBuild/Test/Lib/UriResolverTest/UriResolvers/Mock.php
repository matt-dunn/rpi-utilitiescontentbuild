<?php

namespace RPI\Utilities\ContentBuild\Test\Lib\UriResolverTest\UriResolvers;

class Mock implements \RPI\Utilities\ContentBuild\Lib\Model\UriResolver\IUriResolver
{
    const VERSION = "1.0.0";
    
    /**
     *
     * @var array
     */
    public $options = null;
    
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    ) {
        $this->options = $options;
    }
    
    public static function getVersion()
    {
        return "v".self::VERSION;
    }
    
    /**
     * {@inherit-doc}
     */
    public function getScheme()
    {
        return "mock";
    }
    
    /**
     * {@inherit-doc}
     */
    public function realpath(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $uri
    ) {
        return $uri;
    }
    
    public function getRelativePath(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $uri
    ) {
        return $uri;
    }
}
