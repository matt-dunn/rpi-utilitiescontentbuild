<?php

namespace RPI\Utilities\ContentBuild\Test\Command;

use Ulrichsg\Getopt;

class ValidateTest extends Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Command\Validate
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
            $this->loggerHandler,
            new \RPI\Foundation\App\Logger\Formatter\Raw()
        );
        
        $this->object = new \RPI\Utilities\ContentBuild\Command\Validate();
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
                "name" => "validate",
                "option" => array(
                    null,
                    "validate",
                    Getopt::NO_ARGUMENT, "Validate configuration file"
                )
            ),
            $this->object->getOption()
        );
    }
    
    public function testGetOptionName()
    {
        $this->assertEquals(
            "validate",
            $this->object->getOptionName()
        );
    }
    
    public function testExec()
    {
        $this->assertFalse(
            $this->execCommand(
                "--validate",
                array(
                    "configurationFile" => __DIR__."/ValidateTest/ui.build.xml"
                )
            )
        );

        $this->assertTrue(
            isset($this->loggerHandler->messages[0]["info"])
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testExecInvalid()
    {
        $this->execCommand(
            "--validate",
            array(
                "configurationFile" => __DIR__."/ValidateTest/ui.build-invalid.xml"
            )
        );
    }
    
    public function testExecNotFound()
    {
        $this->assertFalse(
            $this->execCommand("--validate")
        );

        $this->assertTrue(
            isset($this->loggerHandler->messages[0]["error"])
        );
        
        $this->assertEquals(
            "Configuration file not found",
            $this->loggerHandler->messages[0]["error"]["message"]
        );
    }
}
