<?php

namespace RPI\Utilities\ContentBuild\Test\Command;

use Ulrichsg\Getopt;

class LogLevelTest extends Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Command\LogLevel
     */
    protected $object;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->object = new \RPI\Utilities\ContentBuild\Command\LogLevel();
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
                "name" => "loglevel",
                "option" => array(
                    "l",
                    "loglevel",
                    Getopt::REQUIRED_ARGUMENT, "Set log level <arg>. 0 = silent, 1 = information, 2 = verbose, 3 = debug"
                )
            ),
            $this->object->getOption()
        );
    }
    
    public function testGetOptionName()
    {
        $this->assertEquals(
            "loglevel",
            $this->object->getOptionName()
        );
    }
    
    public function testExec()
    {
        $this->assertEquals(
            array(
                "logLevel" => 0
            ),
            $this->execCommand("-l 0")
        );
        
        $this->assertEquals(
            array(
                "logLevel" => 1
            ),
            $this->execCommand("-l 1")
        );
        
        $this->assertEquals(
            array(
                "logLevel" => 2
            ),
            $this->execCommand("-l 2")
        );
        
        $this->assertEquals(
            array(
                "logLevel" => 3
            ),
            $this->execCommand("-l 3")
        );
    }
    
    public function testExecInvalid()
    {
        $this->assertFalse(
            $this->execCommand("-l 4")
        );
        
        $this->assertFalse(
            $this->execCommand("-l a")
        );
        
        $this->assertFalse(
            $this->execCommand("-l abcdef")
        );
        
        $this->assertFalse(
            $this->execCommand("-l #")
        );
    }
}
