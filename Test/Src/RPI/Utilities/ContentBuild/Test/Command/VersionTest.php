<?php

namespace RPI\Utilities\ContentBuild\Test\Command;

use Ulrichsg\Getopt;

class VersionTest extends Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Command\Version
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
        
        $this->object = new \RPI\Utilities\ContentBuild\Command\Version();
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
                "name" => "version",
                "option" => array(
                    "v",
                    "version",
                    Getopt::NO_ARGUMENT, "Display version information"
                )
            ),
            $this->object->getOption()
        );
    }
    
    public function testGetOptionName()
    {
        $this->assertEquals(
            "version",
            $this->object->getOptionName()
        );
    }
    
    public function testExec()
    {
        $this->assertFalse(
            $this->execCommand("-v")
        );
        
        $this->assertEquals(
            array (
                array(
                    'info' =>
                        array (
                            'message' => 'ContentBuild v'.CONTENT_BUILD_VERSION,
                            'context' =>
                        array (
                        ),
                        'exception' => null,
                    )
                )
            ),
            $this->loggerHandler->messages
        );
    }
}
