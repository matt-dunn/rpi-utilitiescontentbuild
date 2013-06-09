<?php

namespace RPI\Utilities\ContentBuild\Test\Plugins;

class HashImagesTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Plugins\DependencyBuilder
     */
    protected $object;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Configuration 
     */
    protected $configuration = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor 
     */
    protected $processor = null;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
    }
    
    protected function setUpObjects($configFile)
    {
        $this->configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            $configFile
        );

        $this->processor = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project,
            false
        );

        $this->object = new \RPI\Utilities\ContentBuild\Plugins\DependencyBuilder(
            $this->processor,
            $this->configuration->project,
            array(
            )
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    public function testGetVersion()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build.xml");
        
        $this->assertEquals(
            "v".\RPI\Utilities\ContentBuild\Plugins\DependencyBuilder::VERSION,
            $this->object->getVersion()
        );
    }
    
    public function testBuildNoDependencies()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build.xml");
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $this->assertEquals(
            array(
                'core_css' =>
                    array (
                        __DIR__.'/DependencyBuilderTest/test.css'
                    ),
                'core_js' =>
                    array (
                        __DIR__.'/DependencyBuilderTest/test.js'
                    )
            ),
            $this->object->build($resolver)
        );
    }
    
    public function testBuildWithDependencies()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build-dependencies.xml");
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $this->assertEquals(
            array(
                'core_css' =>
                    array (
                        __DIR__.'/DependencyBuilderTest/test.css',
                        __DIR__.'/DependencyBuilderTest/css/test-dep.css'
                    ),
                'core_js' =>
                    array (
                        __DIR__.'/DependencyBuilderTest/test.js',
                        __DIR__.'/DependencyBuilderTest/js/test-dep.js'
                    )
            ),
            $this->object->build($resolver)
        );
    }
    
    public function testBuildWithDuplicateDependencies()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build-duplicate-dependencies.xml");
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );

        $this->assertEquals(
            array(
                'core_css' =>
                    array (
                        __DIR__.'/DependencyBuilderTest/test.css',
                        __DIR__.'/DependencyBuilderTest/css/test-dep.css'
                    ),
                'core_js' =>
                    array (
                        __DIR__.'/DependencyBuilderTest/test.js',
                        __DIR__.'/DependencyBuilderTest/js/test-dep.js',
                        __DIR__.'/DependencyBuilderTest/js/test-dep-duplicate.js'
                    )
            ),
            $this->object->build($resolver)
        );
    }
    
    public function testBuildWithDefinedFileType()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build-filetype.xml");
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );

        $this->assertEquals(
            array(
                'core_css' =>
                    array (
                        __DIR__.'/DependencyBuilderTest/test-dep-filetype.sass'
                    )
            ),
            $this->object->build($resolver)
        );
    }
    
    public function testBuildWithDependencyAndDefinedFileType()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build-dependency-filetype.xml");
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );

        $this->assertEquals(
            array(
                'core_css' =>
                    array (
                        __DIR__.'/DependencyBuilderTest/css/test.sass',
                        __DIR__.'/DependencyBuilderTest/css/test-dep-filetype.css'
                    )
            ),
            $this->object->build($resolver)
        );
    }
    
    public function testBuildWithExternalDependencies()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build-external-dependencies.xml");
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );

        $this->assertEquals(
            array(
                'core_css' => 
                    array (
                        __DIR__.'/DependencyBuilderTest/test.css',
                    ),
                'main_css' => 
                    array (
                        __DIR__.'/DependencyBuilderTest/css/test-dep.css',
                    )
            ),
            $this->object->build($resolver)
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testBuildWithInvalidExternalDependencies()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build-external-dependencies-invalid.xml");
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );

        $this->assertEquals(
            array(
                'core_css' => 
                    array (
                        __DIR__.'/DependencyBuilderTest/test.css',
                    ),
                'main_css' => 
                    array (
                        __DIR__.'/DependencyBuilderTest/css/test-dep.css',
                    )
            ),
            $this->object->build($resolver)
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testBuildWithMissingDependencies()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build-dependencies-missing-file.xml");
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $this->object->build($resolver);
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testBuildInvalidFile()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build-missing-file.xml");
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $this->object->build($resolver);
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testBuildCircularReference()
    {
        $this->setUpObjects(__DIR__."/DependencyBuilderTest/ui.build-circular-reference.xml");
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $this->object->build($resolver);
    }
}
