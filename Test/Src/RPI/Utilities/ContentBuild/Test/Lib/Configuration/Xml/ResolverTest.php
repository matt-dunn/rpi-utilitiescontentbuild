<?php

namespace RPI\Utilities\ContentBuild\Test\Lib\Configuration\Xml;

class ResolverTest extends \RPI\Test\Harness\Base
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
    
    public function testConstructNoParams()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Resolver("resolverType");
        
        $this->assertEquals("resolverType", $object->getType());
        $this->assertEquals("resolverType", $object->type);
        
        $this->assertNull($object->getParams());
        $this->assertNull($object->params);
    }
    
    public function testConstructWithParams()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Resolver(
            "resolverType",
            array(
                "param1" => "value1"
            )
        );
        
        $this->assertEquals("resolverType", $object->getType());
        $this->assertEquals("resolverType", $object->type);
        
        $this->assertEquals(
            array(
                "param1" => "value1"
            ),
            $object->getParams()
        );
        $this->assertEquals(
            array(
                "param1" => "value1"
            ),
            $object->params
        );
    }
}
