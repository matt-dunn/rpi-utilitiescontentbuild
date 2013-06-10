<?php

namespace RPI\Utilities\ContentBuild\Test\Lib\Configuration\Xml;

class PluginTest extends \RPI\Test\Harness\Base
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
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Plugin("plugininterface", "plugintype");
        
        $this->assertEquals("plugininterface", $object->getInterface());
        $this->assertEquals("plugininterface", $object->interface);
        
        $this->assertEquals("plugintype", $object->getType());
        $this->assertEquals("plugintype", $object->type);
        
        $this->assertNull($object->getParams());
        $this->assertNull($object->params);
    }
    
    public function testConstructWithParams()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Plugin(
            "plugininterface",
            "plugintype",
            array(
                "param1" => "value1"
            )
        );
        
        $this->assertEquals("plugininterface", $object->getInterface());
        $this->assertEquals("plugininterface", $object->interface);
        
        $this->assertEquals("plugintype", $object->getType());
        $this->assertEquals("plugintype", $object->type);
         
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
