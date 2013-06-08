<?php

namespace RPI\Utilities\ContentBuild\Test\Lib;

class ConfigurationTest extends \RPI\Test\Harness\Base
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
    
    public function testGetProject()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ConfigurationTest/ui.build.xml"
        );
        
        $this->assertInstanceOf("RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project", $object->getProject());
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\InvalidArgument
     */
    public function testGetProjectInvalidType()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ConfigurationTest/ui.build.invalidtype"
        );
        
        $object->getProject();
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\InvalidArgument
     */
    public function testGetProjectNoType()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ConfigurationTest/config-no-type"
        );
        
        $object->getProject();
    }
}
