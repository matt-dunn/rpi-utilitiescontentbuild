<?php

namespace RPI\Utilities\ContentBuild\Test\Lib;

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
    
    public function testGetDependencies()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Dependency(
            $this->logger,
            __DIR__."/DependencyTest/main.dependency.xml"
        );
        
        $this->assertInstanceOf("RPI\Utilities\ContentBuild\Lib\Dependencies\Xml\Dependency", $object->getDependencies());
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\InvalidArgument
     */
    public function testGetDependenciesInvalidType()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Dependency(
            $this->logger,
            __DIR__."/DependencyTest/dependency.invalidtype"
        );
        
        $object->getDependencies();
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\InvalidArgument
     */
    public function testGetDependenciesNoType()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Dependency(
            $this->logger,
            __DIR__."/DependencyTest/dependency-no-type"
        );
        
        $object->getDependencies();
    }
}
