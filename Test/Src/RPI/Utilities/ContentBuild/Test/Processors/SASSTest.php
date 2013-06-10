<?php

namespace RPI\Utilities\ContentBuild\Test\Processors;

class SASSTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Test\Processors\SASSTest\Mock
     */
    protected $object;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Configuration 
     */
    protected $configuration = null;
    
    /**
     *
     * @var \RPI\Utilities\ContentBuild\Lib\Processor 
     */
    protected $processor = null;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            __DIR__."/SASSTest/ui.build.xml"
        );

        $this->processor = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project,
            false
        );

        $this->object = new \RPI\Utilities\ContentBuild\Test\Processors\SASSTest\Mock(
            $this->processor,
            $this->configuration->project,
            array(
            )
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    public function testGetVersion()
    {
        $this->assertEquals(
            "v".\RPI\Utilities\ContentBuild\Test\Processors\SASSTest\Mock::VERSION." - SASS MOCK",
            $this->object->getVersion()
        );
    }
    
    public function testInit()
    {
        $this->assertNull(
            $this->object->init(0)
        );
    }
    
    public function testComplete()
    {
        $this->assertNull(
            $this->object->complete()
        );
    }
    
    public function testCanProcessBuffer()
    {
        $this->assertFalse(
            $this->object->canProcessBuffer()
        );
    }
    
    public function testProcessSASS()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SASSTest/test.sass";
        
        $this->assertTrue(
            $this->object->preProcess(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $actual = \RPI\Foundation\Helpers\Utils::normalizeString(
            $this->object->process(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $this->assertContains(
            "PROCESSED: ".__DIR__."/SASSTest/test.sass",
            $actual
        );
        
        $this->assertContains(
            "--cache-location",
            $actual
        );
    }
    
    public function testProcessSCSS()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SASSTest/test.scss";
        
        $this->assertTrue(
            $this->object->preProcess(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $actual = \RPI\Foundation\Helpers\Utils::normalizeString(
            $this->object->process(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $this->assertContains(
            "PROCESSED: --scss ".__DIR__."/SASSTest/test.scss",
            $actual
        );
        
        $this->assertContains(
            "--cache-location",
            $actual
        );
    }
    
    public function testProcessDebug()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );

        $this->processor->debug = true;
        
        $inputFilename = __DIR__."/SASSTest/test.scss";
        
        $this->assertTrue(
            $this->object->preProcess(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $actual = \RPI\Foundation\Helpers\Utils::normalizeString(
            $this->object->process(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $this->assertContains(
            "--debug-info",
            $actual
        );
    }
    
    public function testGetVersionSASSNotInstalled()
    {
        \RPI\Utilities\ContentBuild\Test\Processors\SASSTest\Mock::$sassCommand = "sass-notinstalled";
        
        $this->assertEquals(
            "v".\RPI\Utilities\ContentBuild\Test\Processors\SASSTest\Mock::VERSION." - NOT INSTALLED",
            $this->object->getVersion()
        );
    }
    
    /**
     * @expectedException \RPI\Console\Exceptions\Console\NotInstalled
     */
    public function testProcessSASSNotInstalled()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        \RPI\Utilities\ContentBuild\Test\Processors\SASSTest\Mock::$sassCommand = "sass-notinstalled";

        $inputFilename = __DIR__."/SASSTest/test.scss";
        
        $this->object->process(
            $resolver,
            $this->configuration->project->builds[0],
            $inputFilename,
            file_get_contents($inputFilename)
        );
    }
    
    public function testProcessNonSASSFile()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SASSTest/test.css";
        
        $css = <<<EOT
.css {
    width:10px;
}
EOT;
        
        $this->assertTrue(
            $this->object->preProcess(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString($css),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                $this->object->process(
                    $resolver,
                    $this->configuration->project->builds[0],
                    $inputFilename,
                    file_get_contents($inputFilename)
                )
            )
        );
    }
}
