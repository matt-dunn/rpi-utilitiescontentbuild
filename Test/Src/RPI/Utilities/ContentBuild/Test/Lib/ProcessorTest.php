<?php

namespace RPI\Utilities\ContentBuild\Test\Lib;

class ProcessorTest extends \RPI\Test\Harness\Base
{
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor 
     */
    protected $object;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Configuration 
     */
    protected $configuration;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ProcessorTest/ui.build.xml"
        );
        
        $this->object = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->object->deleteMetadata();
    }
    
    public function testAdd()
    {
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $this->object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock(
                    $this->object,
                    $this->configuration->project
                )
            )
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testAddFirstProcessor()
    {
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $this->object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock(
                    $this->object,
                    $this->configuration->project
                )
            )
        );
        
        $this->object->add(
            new \RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\MockCannotProcessBuffer(
                $this->object,
                $this->configuration->project
            )
        );
    }
    
    public function testAddWithConfiguredProcessors()
    {
        $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ProcessorTest/ui.build-processors.xml"
        );
        
        $object = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $configuration->project
        );
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock(
                    $object,
                    $configuration->project
                )
            )
        );
        
        $processors = $object->getProcessors();

        $this->assertSame(
            array(
                "param-boolean" => false,
                "param-boolean-true" => true,
                "param-int" => 60,
                "param-string" => 'string value'
            ),
            $processors["RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\MockCannotProcessBuffer"]->options
        );
    }
    
    public function testInit()
    {
        $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ProcessorTest/ui.build-processors.xml"
        );
        
        $object = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $configuration->project
        );
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock(
                    $object,
                    $configuration->project
                )
            )
        );
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $object->init()
        );
        
        $processors = $object->getProcessors();
        
        $this->assertTrue(
            $processors["RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\MockCannotProcessBuffer"]->hasInit
        );
        
        $this->assertTrue(
            $processors["RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock"]->hasInit
        );
    }
    
    public function testComplete()
    {
        $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ProcessorTest/ui.build-processors.xml"
        );
        
        $object = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $configuration->project
        );
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock(
                    $object,
                    $configuration->project
                )
            )
        );
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $object->complete()
        );
        
        $processors = $object->getProcessors();
        
        $this->assertTrue(
            $processors[
            "RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\MockCannotProcessBuffer"
            ]->hasComplete
        );
        
        $this->assertTrue(
            $processors["RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock"]->hasComplete
        );
    }
    
    public function testPreProcess()
    {
        $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ProcessorTest/ui.build-processors.xml"
        );
        
        $object = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $configuration->project
        );
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $object,
            $configuration->project
        );
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock(
                    $object,
                    $configuration->project
                )
            )
        );
        
        $inputFilename = __DIR__."/ProcessorTest/test.css";
        
        $this->assertTrue(
            $object->preProcess(
                $configuration->project->builds[0],
                $resolver,
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $processors = $object->getProcessors();
        
        $this->assertTrue(
            $processors[
            "RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\MockCannotProcessBuffer"
            ]->hasPreProcess
        );
        
        $this->assertTrue(
            $processors["RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock"]->hasPreProcess
        );
    }
    
    public function testProcess()
    {
        $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ProcessorTest/ui.build-processors.xml"
        );
        
        $object = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $configuration->project
        );
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $object,
            $configuration->project
        );
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $object->add(
                new \RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock(
                    $object,
                    $configuration->project
                )
            )
        );
        
        $inputFilename = __DIR__."/ProcessorTest/test.css";
        
        $css = <<<EOT
/*!

Comment not removed

*/







root { 
    display: block;
}
EOT;
        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString($css),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                $object->Process(
                    $configuration->project->builds[0],
                    $resolver,
                    $inputFilename,
                    file_get_contents($inputFilename)
                )
            )
        );
        
        $processors = $object->getProcessors();
        
        $this->assertTrue(
            $processors[
            "RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\MockCannotProcessBuffer"
            ]->hasProcess
        );
        
        $this->assertTrue(
            $processors["RPI\Utilities\ContentBuild\Test\Lib\ProcessorTest\Processors\Mock"]->hasProcess
        );
    }
    
    public function testGetMetadata()
    {
        $this->assertFalse($this->object->getMetadata("test1"));
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $this->object->setMetadata("test1", "value1")
        );
        
        $this->assertEquals("value1", $this->object->getMetadata("test1"));
    }

    public function testGetMetadataStatefull()
    {
        $this->assertFalse($this->object->getMetadata("test1"));
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $this->object->setMetadata("test1", "value1")
        );
        
        $this->assertEquals("value1", $this->object->getMetadata("test1"));
        
        $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/ProcessorTest/ui.build.xml"
        );
        
        $object = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $configuration->project
        );
        
        $this->assertEquals("value1", $object->getMetadata("test1"));
        
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $object->setMetadata("test2", "value2")
        );
        
        $this->assertEquals("value2", $object->getMetadata("test2"));
    }
    
    public function testSetMetadata()
    {
        $this->assertInstanceOf(
            "RPI\Utilities\ContentBuild\Lib\Processor",
            $this->object->setMetadata("test1", "value1")
        );
        
        $this->assertEquals("value1", $this->object->getMetadata("test1"));
    }
}
