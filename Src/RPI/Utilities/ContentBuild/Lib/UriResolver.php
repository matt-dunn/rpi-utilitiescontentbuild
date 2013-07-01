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
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor 
     */
    protected $processor = null;
    
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param \RPI\Utilities\ContentBuild\Lib\Processor $processor
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
    ) {
        $this->logger = $logger;
        $this->processor = $processor;
        $this->project = $project;
    }
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\UriResolver\IUriResolver $resolver
     * 
     * @return \RPI\Utilities\ContentBuild\Lib\UriResolver
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
                    $params = array($this->processor, $this->project);
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
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
     * @param string $uri
     * 
     * @return boolean
     * 
     * @throws \RPI\Foundation\Exceptions\RuntimeException
     */
    public function realpath(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $uri
    ) {
        $resolverRealPath = false;
        
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        if ($scheme != "" && $scheme != "http") {
            $schemRegistered = false;
            foreach ($this->getResolvers() as $resolver) {
                if ($resolver->getScheme() == $scheme) {
                    $schemRegistered = true;
                    $realpath = $resolver->realpath($project, $uri);
                    if ($realpath !== false) {
                        $resolverRealPath = $realpath;
                        break;
                    }
                }
            }
            
            if (!$schemRegistered) {
                throw new \RPI\Foundation\Exceptions\RuntimeException(
                    "Unable to locate '$uri' as scheme '$scheme' cannot be resolved. ".
                    "Ensure '$scheme' is configured as a uriResolvers"
                );
            }
        }
        
        return $resolverRealPath;
    }
    
    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
     * @param string $uri
     * 
     * @return boolean
     * 
     * @throws \RPI\Foundation\Exceptions\RuntimeException
     */
    public function getRelativePath(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        $uri
    ) {
        $resolverRelativePath = false;
        
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        if ($scheme != "" && $scheme != "http") {
            $schemRegistered = false;
            foreach ($this->getResolvers() as $resolver) {
                if ($resolver->getScheme() == $scheme) {
                    $schemRegistered = true;
                    $relativePath = $resolver->getRelativePath($project, $uri);
                    if ($relativePath !== false) {
                        $resolverRelativePath = $relativePath;
                        break;
                    }
                }
            }
            
            if (!$schemRegistered) {
                throw new \RPI\Foundation\Exceptions\RuntimeException(
                    "Unable to locate '$uri' as scheme '$scheme' cannot be resolved. ".
                    "Ensure '$scheme' is configured as a uriResolvers"
                );
            }
        }
        
        return $resolverRelativePath;
    }
}
