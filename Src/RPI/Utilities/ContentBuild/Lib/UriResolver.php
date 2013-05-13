<?php

namespace RPI\Utilities\ContentBuild\Lib;

use \RPI\Foundation\Helpers\Object;

class UriResolver extends Object
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\UriResolver\IUriResolver[]
     */
    protected $resolvers = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    protected $project = null;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger = null;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
        $this->logger = $logger;
        $this->project = $project;
    }
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\UriResolver\IUriResolver $resolver
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Processor
     */
    public function add(\RPI\Utilities\ContentBuild\Lib\Model\UriResolver\IUriResolver $resolver)
    {
        if (!isset($this->resolvers)) {
            $this->getResolvers();
        }
        
        $this->resolvers[get_class($resolver)] = $resolver;
        $this->logger->info(
            "Creating '".get_class($resolver)."' ({$resolver->getVersion()})"
        );
        
        return $this;
    }
    
    /**
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\Model\UriResolver\IUriResolver[]
     */
    public function getResolvers()
    {
        if (!isset($this->resolvers)) {
            $this->resolvers = array();
            
            if (isset($this->project->resolvers)) {
                $this->logger->debug(
                    "Reading resolvers from configuration '{$this->project->configurationFile}'"
                );
                foreach ($this->project->resolvers as $resolver) {
                    $params = array($this->project);
                    if (isset($resolver->params)) {
                        $params = array_merge($params, $resolver->params);
                    }
                    $instance = new \ReflectionClass($resolver->type);
                    $this->add($instance->newInstanceArgs($params));
                }
            }
        }
        
        return $this->resolvers;
    }
    
    public function realpath(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $uri
    ) {
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        if ($scheme != "") {
            foreach ($this->getResolvers() as $resolver) {
                if ($resolver->getScheme() == $scheme) {
                    $realpath = $resolver->realpath($project, $uri);
                    if ($realpath !== false) {
                        return $realpath;
                    }
                }
            }
        }
        
        return false;
    }
    
    public function getRelativePath(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $uri
    ) {
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        if ($scheme != "") {
            foreach ($this->getResolvers() as $resolver) {
                if ($resolver->getScheme() == $scheme) {
                    $relativePath = $resolver->getRelativePath($project, $uri);
                    if ($relativePath !== false) {
                        return $relativePath;
                    }
                }
            }
        }
        
        return false;
    }
}
