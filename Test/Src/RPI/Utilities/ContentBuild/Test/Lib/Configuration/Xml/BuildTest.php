<?php

namespace RPI\Utilities\ContentBuild\Test\Lib\Configuration\Xml;

class BuildTest extends \RPI\Test\Harness\Base
{
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
    
    public function testConstruct()
    {
        $project = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project(
            $this->logger,
            __DIR__."/BuildTest/ui.build.xml"
        );
        
        $doc = new \DOMDocument();
        $doc->load(__DIR__."/BuildTest/ui.build.xml");
        
        $config = \RPI\Foundation\Helpers\Dom::deserialize(simplexml_import_dom($doc));
        
        // First BUILD with multiple files
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Build(
            $project,
            $config["build"][0]
        );
        
        $this->assertEquals("core", $object->getName());
        $this->assertEquals("core", $object->name);
        
        $this->assertEquals("css_build_dir", $object->getBuildDirectory());
        $this->assertEquals("css_build_dir", $object->buildDirectory);
        
        $this->assertEquals(2, count($object->getFiles()));
        $this->assertEquals(2, count($object->files));
        $this->assertEquals(
            array(
                "test.css",
                "test2.css"
            ),
            $object->files
        );
        
        $this->assertEquals("compiled/css/", $object->getOutputDirectory());
        $this->assertEquals("compiled/css/", $object->outputDirectory);
        
        $this->assertEquals("css", $object->getType());
        $this->assertEquals("css", $object->type);
        
        $this->assertEquals("test.css", $object->getOutputFilename());
        $this->assertEquals("test.css", $object->outputFilename);
        
        $this->assertNull($object->getExternalDependenciesNames());
        $this->assertNull($object->externalDependenciesNames);
        
        $this->assertNull($object->getTarget());
        $this->assertNull($object->target);
        
        $this->assertEquals("all", $object->getMedia());
        $this->assertEquals("all", $object->media);
        
        $this->assertEquals(__DIR__."/BuildTest/ROOT/compiled/__debug/css", $object->getDebugPath());
        $this->assertEquals(__DIR__."/BuildTest/ROOT/compiled/__debug/css", $object->debugPath);
        
        // Second BUILD with single file
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Build(
            $project,
            $config["build"][1]
        );
        
        $this->assertEquals("core", $object->getName());
        $this->assertEquals("core", $object->name);
        
        $this->assertEquals("js_build_dir", $object->getBuildDirectory());
        $this->assertEquals("js_build_dir", $object->buildDirectory);
        
        $this->assertEquals(1, count($object->getFiles()));
        $this->assertEquals(1, count($object->files));
        $this->assertEquals(
            array(
                "test.js"
            ),
            $object->files
        );
        
        $this->assertEquals("compiled/js/", $object->getOutputDirectory());
        $this->assertEquals("compiled/js/", $object->outputDirectory);
        
        $this->assertEquals("js", $object->getType());
        $this->assertEquals("js", $object->type);
        
        $this->assertEquals(
            __DIR__."/BuildTest/ROOT/compiled/js/TEMPLATE.website-core.js",
            $object->getOutputFilename()
        );
        $this->assertEquals(
            __DIR__."/BuildTest/ROOT/compiled/js/TEMPLATE.website-core.js",
            $object->outputFilename
        );
        
        $this->assertEquals("core", $object->getExternalDependenciesNames());
        $this->assertEquals("core", $object->externalDependenciesNames);
        
        $this->assertEquals("footer", $object->getTarget());
        $this->assertEquals("footer", $object->target);
        
        $this->assertEquals("all", $object->getMedia());
        $this->assertEquals("all", $object->media);
        
        $this->assertEquals(__DIR__."/BuildTest/ROOT/compiled/__debug/js", $object->getDebugPath());
        $this->assertEquals(__DIR__."/BuildTest/ROOT/compiled/__debug/js", $object->debugPath);
    }
}
