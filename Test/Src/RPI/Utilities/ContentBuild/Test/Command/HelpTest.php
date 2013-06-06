<?php

namespace RPI\Utilities\ContentBuild\Test\Command;

use Ulrichsg\Getopt;

class HelpTest extends Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Command\Help
     */
    protected $object;
    
    /**
     *
     * @var \RPI\Foundation\App\Logger\Handler\IHandler
     */
    protected $loggerHandler = null;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->loggerHandler = new \RPI\Test\Harness\Mock\Logger\Handler\Mock();
        
        $this->logger = new \RPI\Foundation\App\Logger(
            $this->loggerHandler
        );
        
        $this->object = new \RPI\Utilities\ContentBuild\Command\Help();
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
                "name" => "help",
                "option" => array(
                    "h",
                    "help",
                    Getopt::NO_ARGUMENT, "Show this help"
                )
            ),
            $this->object->getOption()
        );
    }
    
    public function testGetOptionName()
    {
        $this->assertEquals(
            "help",
            $this->object->getOptionName()
        );
    }
    
    public function testExec()
    {
        $this->assertFalse(
            $this->execCommand("-h")
        );

        $this->assertTrue(
            isset($this->loggerHandler->messages[0]["info"])
        );
        
        $this->assertTrue(
            count($this->loggerHandler->messages[0]["info"]) > 1
        );
    }
    
    public function testExecMissingFlag()
    {
        $this->assertNull(
            $this->execCommand("")
        );
    }
}
