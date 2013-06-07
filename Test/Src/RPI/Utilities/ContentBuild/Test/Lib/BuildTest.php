<?php

namespace RPI\Utilities\ContentBuild\Test\Lib;

class BuildTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Lib\Build
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
     *
     * @var \RPI\Utilities\ContentBuild\Lib\UriResolver 
     */
    protected $resolver;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/BuildTest/ui.build.xml"
        );

        $this->processor = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project,
            false
        );

        $this->resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $this->object = new \RPI\Utilities\ContentBuild\Lib\Build(
            $this->logger,
            $this->configuration->project,
            $this->processor,
            $this->resolver,
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
        \RPI\Foundation\Helpers\FileUtils::delTree(__DIR__."/BuildTest/ROOT");
    }
    
    public function testRun()
    {
        $this->assertTrue($this->object->run());
        
        $this->assertTrue(
            file_exists(__DIR__."/BuildTest/ROOT/compiled/__debug/css/TEMPLATE.website-core.min.css"),
            "File does not exist"
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/BuildTest/ROOT/compiled/__debug/css/head-all.html"),
            "File does not exist"
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/BuildTest/ROOT/compiled/__debug/css/proxy.php"),
            "File does not exist"
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/BuildTest/ROOT/compiled/__debug/js/TEMPLATE.website-core.min.js"),
            "File does not exist"
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/BuildTest/ROOT/compiled/__debug/js/head-all.html"),
            "File does not exist"
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/BuildTest/ROOT/compiled/__debug/js/proxy.php"),
            "File does not exist"
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/BuildTest/ROOT/compiled/__debug/css/TEMPLATE.website-core.min.css"),
            "File does not exist"
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/BuildTest/ROOT/compiled/__debug/css/head-all.html"),
            "File does not exist"
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/BuildTest/ROOT/compiled/css/TEMPLATE.website-core.min.css"),
            "File does not exist"
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/BuildTest/ROOT/compiled/css/head-all.html"),
            "File does not exist"
        );

        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString(
                '@import url("/compiled/__debug/css/proxy.php?t=css&n=core&f=%2F..%2F%2Ftest.css");'
            ),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents(__DIR__."/BuildTest/ROOT/compiled/__debug/css/TEMPLATE.website-core.min.css")
            )
        );

        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString(
                'root{display:block}'
            ),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents(__DIR__."/BuildTest/ROOT/compiled/css/TEMPLATE.website-core.min.css")
            )
        );

        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString(
                '<link rel="stylesheet" type="text/css" '.
                'href="/compiled/__debug/css/TEMPLATE.website-core.min.css" media="all" />'
            ),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents(__DIR__."/BuildTest/ROOT/compiled/__debug/css/head-all.html")
            )
        );

        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString(
                '<link rel="stylesheet" type="text/css" '.
                'href="/compiled/css/TEMPLATE.website-core.min.css?824b9347da8ebb9944efb991d5a5b2ae" media="all" />'
            ),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents(__DIR__."/BuildTest/ROOT/compiled/css/head-all.html")
            )
        );
        
        $this->assertContains(
            \RPI\Foundation\Helpers\Utils::normalizeString(
                'document.prepareScript("/compiled/__debug/js/proxy.php?t=js&n=core&f=%2F..%2F%2Ftest.js");'
            ),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents(__DIR__."/BuildTest/ROOT/compiled/__debug/js/TEMPLATE.website-core.min.js")
            )
        );

        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString(
                'function test(){alert("Test JS")};'
            ),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents(__DIR__."/BuildTest/ROOT/compiled/js/TEMPLATE.website-core.min.js")
            )
        );

        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString(
                '<script type="text/javascript" src="/compiled/__debug/js/TEMPLATE.website-core.min.js"> </script>'
            ),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents(__DIR__."/BuildTest/ROOT/compiled/__debug/js/head-all.html")
            )
        );

        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString(
                '<script type="text/javascript" '.
                'src="/compiled/js/TEMPLATE.website-core.min.js?4b29d13b9ef194bfa9f5aa1dcbb8805c"> </script>'
            ),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents(__DIR__."/BuildTest/ROOT/compiled/js/head-all.html")
            )
        );
    }
    
    public function testRunNoDebug()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Build(
            $this->logger,
            $this->configuration->project,
            $this->processor,
            $this->resolver,
            array(
                "debug-include" => false
            )
        );
        
        $this->assertTrue($object->run());
    }
    
    public function testRunPlugins()
    {
        $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/BuildTest/ui.build-plugins.xml"
        );
        
        $object = new \RPI\Utilities\ContentBuild\Lib\Build(
            $this->logger,
            $configuration->project,
            $this->processor,
            $this->resolver,
            array(
                "debug-include" => false
            )
        );
        
        $this->assertTrue($object->run());
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testRunPluginsInvalid()
    {
        $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/BuildTest/ui.build-plugins-invalid.xml"
        );
        
        $object = new \RPI\Utilities\ContentBuild\Lib\Build(
            $this->logger,
            $configuration->project,
            $this->processor,
            $this->resolver,
            array(
                "debug-include" => false
            )
        );
        
        $this->assertTrue($object->run());
    }
}
