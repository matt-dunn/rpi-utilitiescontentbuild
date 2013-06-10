<?php

namespace RPI\Utilities\ContentBuild\Test\Lib\Dependencies\Xml;

class DependencyTest extends \RPI\Test\Harness\Base
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
        $object = new \RPI\Utilities\ContentBuild\Lib\Dependencies\Xml\Dependency(
            $this->logger,
            __DIR__."/DependencyTest/test.dependencies.xml"
        );
        
        $this->assertEquals(
            array(
                array("name" => "test.css", "type" => null),
                array("name" => "test.sass", "type" => "css"),
                array("name" => "test.less", "type" => "css")
            ),
            $object->getFiles()
        );
    }
    
    public function testConstructSingle()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Dependencies\Xml\Dependency(
            $this->logger,
            __DIR__."/DependencyTest/test.dependencies-single.xml"
        );
        
        $this->assertEquals(
            array(
                array("name" => "test.less", "type" => "css")
            ),
            $object->getFiles()
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testConstructMissingDependencyFile()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Dependencies\Xml\Dependency(
            $this->logger,
            __DIR__."/test.dependencies-missing.xml"
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testConstructInvalidDependencyFile()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Dependencies\Xml\Dependency(
            $this->logger,
            __DIR__."/DependencyTest/test.dependencies-invalid.xml"
        );
    }
}
