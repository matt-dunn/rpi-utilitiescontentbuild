<?php

namespace RPI\Utilities\ContentBuild\Test\Lib;

class UriResolverTest extends \RPI\Test\Harness\Base
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\UriResolver 
     */
    protected $object;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Configuration 
     */
    protected $configuration;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor 
     */
    protected $processor;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    protected function setUpObjects($configFile)
    {
        $this->configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            $configFile
        );
        
        $this->processor = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project
        );
        
        $this->object = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
    }
    
    public function testAddWithConfiguredResolvers()
    {
        $this->setUpObjects(__DIR__."/UriResolverTest/ui.build-resolvers.xml");
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\UriResolver",
            $this->object->add(
                new \RPI\Utilities\ContentBuild\UriResolvers\Composer(
                    $this->processor,
                    $this->configuration->project,
                    array(
                        "vendorPath" => ""
                    )
                )
            )
        );
        
        $this->assertEquals(2, count($this->object->getResolvers()));
        $this->assertEquals(
            array(
                "param1" => "value1"
            ),
            $this->object->resolvers["RPI\Utilities\ContentBuild\Test\Lib\UriResolverTest\UriResolvers\Mock"]->options
        );
    }
    
    public function testAdd()
    {
        $this->setUpObjects(__DIR__."/UriResolverTest/ui.build.xml");
        
        $this->assertEquals(0, count($this->object->getResolvers()));
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\UriResolver",
            $this->object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\UriResolverTest\UriResolvers\Mock(
                    $this->processor,
                    $this->configuration->project
                )
            )
        );
        
        $this->assertEquals(1, count($this->object->getResolvers()));
    }
    
    public function testRealpath()
    {
        $this->setUpObjects(__DIR__."/UriResolverTest/ui.build.xml");
        
        $this->assertEquals(0, count($this->object->getResolvers()));
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\UriResolver",
            $this->object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\UriResolverTest\UriResolvers\MockInvalidUri(
                    $this->processor,
                    $this->configuration->project
                )
            )
        );
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\UriResolver",
            $this->object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\UriResolverTest\UriResolvers\Mock(
                    $this->processor,
                    $this->configuration->project
                )
            )
        );
        
        $this->assertEquals(2, count($this->object->getResolvers()));
        
        $this->assertFalse(
            $this->object->realpath($this->configuration->project, "test")
        );
        
        $this->assertEquals(
            "mock://test.css",
            $this->object->realpath($this->configuration->project, "mock://test.css")
        );
        
        $this->assertFalse(
            $this->object->realpath($this->configuration->project, "mockinvalid://test.css")
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testRealpathInvalidScheme()
    {
        $this->setUpObjects(__DIR__."/UriResolverTest/ui.build.xml");
        
        $this->object->realpath($this->configuration->project, "invalid://test");
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testRealpathInvalidSchemeWithResolvers()
    {
        $this->setUpObjects(__DIR__."/UriResolverTest/ui.build.xml");
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\UriResolver",
            $this->object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\UriResolverTest\UriResolvers\Mock(
                    $this->processor,
                    $this->configuration->project
                )
            )
        );
        
        $this->object->realpath($this->configuration->project, "invalid://test");
    }
    
    public function testGetRelativePath()
    {
        $this->setUpObjects(__DIR__."/UriResolverTest/ui.build.xml");
        
        $this->assertEquals(0, count($this->object->getResolvers()));
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\UriResolver",
            $this->object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\UriResolverTest\UriResolvers\MockInvalidUri(
                    $this->processor,
                    $this->configuration->project
                )
            )
        );
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\UriResolver",
            $this->object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\UriResolverTest\UriResolvers\Mock(
                    $this->processor,
                    $this->configuration->project
                )
            )
        );
        
        $this->assertEquals(2, count($this->object->getResolvers()));
        
        $this->assertFalse(
            $this->object->getRelativePath($this->configuration->project, "test")
        );
        
        $this->assertEquals(
            "mock://test.css",
            $this->object->getRelativePath($this->configuration->project, "mock://test.css")
        );
        
        $this->assertFalse(
            $this->object->getRelativePath($this->configuration->project, "mockinvalid://test.css")
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testGetRelativePathInvalidScheme()
    {
        $this->setUpObjects(__DIR__."/UriResolverTest/ui.build.xml");
        
        $this->object->getRelativePath($this->configuration->project, "invalid://test");
    }
}
