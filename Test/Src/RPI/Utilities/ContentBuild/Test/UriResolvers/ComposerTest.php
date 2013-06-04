<?php

namespace RPI\Utilities\ContentBuild\Test\UriResolvers;

class ComposerTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\UriResolvers\Composer
     */
    protected $object;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ComposerTest/ui.build.xml"
        );

        $this->processor = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project,
            false
        );

        $this->object = new \RPI\Utilities\ContentBuild\UriResolvers\Composer(
            $this->processor,
            $this->configuration->project,
            array(
                "vendorPath" => "vendor"
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
    
    public function testRealPathInvalid()
    {
        $this->assertFalse($this->object->realpath($this->configuration->project, "invalid"));
    }
    
    public function testRealPath()
    {
        $this->assertEquals(
            realpath(__DIR__."/ComposerTest/vendor/rpi/view/Src/RPI/View/Css/test.css"),
            $this->object->realpath($this->configuration->project, "composer://rpi/view#Src/RPI/View/Css/test.css")
        );
    }
    
    public function testGetRelativePathInvalid()
    {
        $this->assertEquals(
            "Src/RPI/View/Css/invalid.css",
            $this->object->getRelativePath(
                $this->configuration->project,
                "composer://rpi/view#Src/RPI/View/Css/invalid.css"
            )
        );
    }
    
    public function testGetRelativePath()
    {
        $this->assertEquals(
            "Src/RPI/View/Css/test.css",
            $this->object->getRelativePath(
                $this->configuration->project,
                "composer://rpi/view#Src/RPI/View/Css/test.css"
            )
        );
    }
}
