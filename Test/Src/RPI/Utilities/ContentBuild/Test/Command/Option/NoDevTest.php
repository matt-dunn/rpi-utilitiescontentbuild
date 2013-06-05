<?php

namespace RPI\Utilities\ContentBuild\Test\Command\Options;

use Ulrichsg\Getopt;

class NoDevTest extends \RPI\Utilities\ContentBuild\Test\Command\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Command\Options\NoDev
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
        
        $this->object = new \RPI\Utilities\ContentBuild\Command\Options\NoDev();
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
                "name" => "no-dev",
                "option" => array(
                    null,
                    "no-dev",
                    Getopt::NO_ARGUMENT, "Do not generate any debug code"
                )
            ),
            $this->object->getOption()
        );
    }
    
    public function testGetOptionName()
    {
        $this->assertEquals(
            "no-dev",
            $this->object->getOptionName()
        );
    }
    
    public function testExec()
    {
        $this->assertEquals(
            array(
                "debug-include" => false
            ),
            $this->execCommand("--no-dev")
        );
    }
}
