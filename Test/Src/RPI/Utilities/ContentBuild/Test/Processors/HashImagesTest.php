<?php

namespace RPI\Utilities\ContentBuild\Test\Processors;

class HashImagesTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Processors\HashImages
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
            __DIR__."/HashImagesTest/ui.build.xml"
        );

        $this->processor = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project,
            false
        );

        $this->object = new \RPI\Utilities\ContentBuild\Processors\HashImages(
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
            "v".\RPI\Utilities\ContentBuild\Processors\HashImages::VERSION,
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
        $this->assertTrue(
            $this->object->canProcessBuffer()
        );
    }
    
    public function testProcess()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/HashImagesTest/test.css";
        
        $css = <<<EOT
.image1 {
    background:url(I/1.png?hash=e2c317321f7a82a46fe80bea949ddab9);
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
    
    public function testProcessExistingQuerystring()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/HashImagesTest/test-querystring.css";
        
        $css = <<<EOT
.image1 {
    background:url(I/1.png?hash=12345);
}

.image2 {
    background:url(I/1.png?item1=val1&item2=val2&hash=e2c317321f7a82a46fe80bea949ddab9);
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
    
    /**
     * @expectedException ErrorException
     */
    public function testProcessHashAlgorithmInvalid()
    {
        $this->object = new \RPI\Utilities\ContentBuild\Processors\HashImages(
            $this->processor,
            $this->configuration->project,
            array(
                "hashAlgorithm" => "invalid-hash-algorithm"
            )
        );
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/HashImagesTest/test.css";
        
        $css = <<<EOT
.image1 {
    background:url(I/1.png?hash=b56473393236591d9ff9fd89322580bb4a0a282d6bf6c7a99c1b0e94eef8aaac);
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
        
        $this->object->process(
            $resolver,
            $this->configuration->project->builds[0],
            $inputFilename,
            file_get_contents($inputFilename)
        );
    }
    
    public function testProcessHashAlgorithm()
    {
        $this->object = new \RPI\Utilities\ContentBuild\Processors\HashImages(
            $this->processor,
            $this->configuration->project,
            array(
                "hashAlgorithm" => "sha256"
            )
        );
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/HashImagesTest/test.css";
        
        $css = <<<EOT
.image1 {
    background:url(I/1.png?hash=b56473393236591d9ff9fd89322580bb4a0a282d6bf6c7a99c1b0e94eef8aaac);
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
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\FileNotFound
     */
    public function testProcessFileNotFound()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/HashImagesTest/test-file-not-found.css";
        
        $this->assertTrue(
            $this->object->preProcess(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $this->object->process(
            $resolver,
            $this->configuration->project->builds[0],
            $inputFilename,
            file_get_contents($inputFilename)
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\FileNotFound
     */
    public function testProcessFileNotFoundQuerystring()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/HashImagesTest/test-file-not-found-querystring.css";
        
        $this->assertTrue(
            $this->object->preProcess(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $this->object->process(
            $resolver,
            $this->configuration->project->builds[0],
            $inputFilename,
            file_get_contents($inputFilename)
        );
    }
}
