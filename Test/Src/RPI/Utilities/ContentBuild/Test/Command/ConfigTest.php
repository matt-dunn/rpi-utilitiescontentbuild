<?php

namespace RPI\Utilities\ContentBuild\Test\Command;

use Ulrichsg\Getopt;

class ConfigTest extends Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Command\Config
     */
    protected $object;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->object = new \RPI\Utilities\ContentBuild\Command\Config();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    public function testGetOption()
    {
        $this->assertEquals(
            array(
                "name" => "config",
                "option" => array(
                    "c",
                    "config",
                    Getopt::REQUIRED_ARGUMENT, "Location of the configuration file <arg>. ".
                    "Defaults to a file called 'ui.build.xml' in the current directory or the directory supplied."
                )
            ),
            $this->object->getOption()
        );
    }
    
    public function testGetOptionName()
    {
        $this->assertEquals(
            "config",
            $this->object->getOptionName()
        );
    }
    
    public function testExecCurrentDirectory()
    {
        chdir(__DIR__);
        
        $command = "-c ConfigTest";
        
        $this->assertEquals(
            array(
                "configurationFile" => realpath(__DIR__."/ConfigTest/ui.build.xml")
            ),
            $this->execCommand($command)
        );
    }
    
    public function testExecFullPath()
    {
        $command = "-c ".__DIR__."/ConfigTest";
        
        $this->assertEquals(
            array(
                "configurationFile" => realpath(__DIR__."/ConfigTest/ui.build.xml")
            ),
            $this->execCommand($command)
        );
    }
    
    public function testExecFullPathAndConfig()
    {
        $command = "-c ".__DIR__."/ConfigTest/ui.build.xml";
        
        $this->assertEquals(
            array(
                "configurationFile" => realpath(__DIR__."/ConfigTest/ui.build.xml")
            ),
            $this->execCommand($command)
        );
    }
    
    public function testExecMissingConfig()
    {
        $command = "-c ".__DIR__."/ConfigTest/invalid.xml";
        
        $this->assertFalse(
            $this->execCommand($command)
        );
    }
    
    /**
     * @expectedException UnexpectedValueException
     */
    public function testExecMissingConfigFile()
    {
        $command = "-c";
        
        $this->execCommand($command);
    }
}
