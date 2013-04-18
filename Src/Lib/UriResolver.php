<?php

namespace RPI\Utilities\ContentBuild\Lib;

use \RPI\Utilities\ContentBuild\Lib\Helpers\Object;

class UriResolver extends Object
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\UriResolver\IUriResolver[]
     */
    private $resolvers = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject
     */
    private $project = null;
    
    public function __construct(\RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project)
    {
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
        \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
            "Creating '".get_class($resolver)."' ({$resolver->getVersion()})",
            LOG_INFO
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
                \RPI\Utilities\ContentBuild\Lib\Exception\Handler::log(
                    "Reading resolvers from configuration'",
                    LOG_DEBUG
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
                $realpath = $resolver->realpath($project, $uri);
                if ($realpath !== false) {
                    return $realpath;
                }
            }

            throw new \Exception("Unable to resolve path '$uri'");
        }
        
        return false;
    }
}
