<?php

namespace RPI\Utilities\ContentBuild\Test\Command;

use Ulrichsg\Getopt;

class ValidateDependencyTest extends Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Command\ValidateDependency
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
        
        $this->object = new \RPI\Utilities\ContentBuild\Command\ValidateDependency();
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
                "name" => "validate-dep",
                "option" => array(
                    null,
                    "validate-dep",
                    Getopt::REQUIRED_ARGUMENT, "Validate dependency file <arg>"
                )
            ),
            $this->object->getOption()
        );
    }
    
    public function testGetOptionName()
    {
        $this->assertEquals(
            "validate-dep",
            $this->object->getOptionName()
        );
    }
    
    /**
     * @expectedException UnexpectedValueException
     */
    public function testExecMissingValue()
    {
        $this->execCommand(
            "--validate-dep"
        );
    }
    
    public function testExec()
    {
        $this->assertFalse(
            $this->execCommand(
                "--validate-dep ".__DIR__."/ValidateDependencyTest/test.dependencies.xml"
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
            "--validate-dep ".__DIR__."/ValidateDependencyTest/test.dependencies-invalid.xml"
        );
    }
    
    public function testExecNotFound()
    {
        $this->assertFalse(
            $this->execCommand(
                "--validate-dep ".__DIR__."/ValidateDependencyTest/test.dependencies-notfound.xml"
            )
        );

        $this->assertTrue(
            isset($this->loggerHandler->messages[0]["error"])
        );
    }
}
