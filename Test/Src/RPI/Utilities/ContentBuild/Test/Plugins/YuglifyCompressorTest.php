<?php

namespace RPI\Utilities\ContentBuild\Test\Plugins;

class YuglifyCompressorTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Test\Plugins\YuglifyCompressorTest\Mock
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
    }
    
    protected function setUpObjects($configFile, $options = null)
    {
        $this->configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
            $this->logger,
            $configFile
        );

        $this->processor = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project,
            false
        );

        $this->object = new \RPI\Utilities\ContentBuild\Test\Plugins\YuglifyCompressorTest\Mock(
            $this->processor,
            $this->configuration->project,
            $options
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
        $this->assertContains(
            "v".\RPI\Utilities\ContentBuild\Test\Plugins\YuglifyCompressorTest\Mock::VERSION,
            \RPI\Utilities\ContentBuild\Test\Plugins\YuglifyCompressorTest\Mock::getVersion()
        );
    }
    
    public function testCompressFileCss($deleteOutputFile = true)
    {
        $this->setUpObjects(__DIR__."/YuglifyCompressorTest/ui.build.xml");
        
        $filename = __DIR__."/YuglifyCompressorTest/test-compress.css";
        
        copy(__DIR__."/YuglifyCompressorTest/test.css", $filename);
        
        $type = "css";
        $outputFilename = __DIR__."/YuglifyCompressorTest/test.min.css";
        
        $this->assertTrue(file_exists($filename));
        
        $this->assertTrue(
            $this->object->compressFile($filename, $type, $outputFilename)
        );
        
        $this->assertFalse(file_exists($filename));
        
        $this->assertTrue(file_exists($outputFilename));
        
        $content = <<<EOT
.image1 {
    background:url(I/1.png);
}
EOT;
        $this->assertContains(
            \RPI\Foundation\Helpers\Utils::normalizeString($content, true),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents($outputFilename),
                true
            )
        );
        
        if ($deleteOutputFile) {
            unlink($outputFilename);
        }
        
        return $outputFilename;
    }
    
    public function testGetVersionYuglifyNotInstalled()
    {
        $this->setUpObjects(__DIR__."/YuglifyCompressorTest/ui.build.xml");
        
        \RPI\Utilities\ContentBuild\Test\Plugins\YuglifyCompressorTest\Mock::$yuglifyCommand = "yuglify-notinstalled";
        
        $this->assertEquals(
            "v".\RPI\Utilities\ContentBuild\Test\Plugins\YuglifyCompressorTest\Mock::VERSION." - yuglify NOT INSTALLED",
            $this->object->getVersion()
        );
    }
    
    /**
     * @expectedException \RPI\Console\Exceptions\Console\NotInstalled
     */
    public function testCompressYuglifyNotInstalled()
    {
        $this->setUpObjects(__DIR__."/YuglifyCompressorTest/ui.build.xml");
        
        \RPI\Utilities\ContentBuild\Test\Plugins\YuglifyCompressorTest\Mock::$yuglifyCommand = "yuglify-notinstalled";
        
        $filename = __DIR__."/YuglifyCompressorTest/test-compress.css";
        
        copy(__DIR__."/YuglifyCompressorTest/test.css", $filename);
        
        $type = "css";
        $outputFilename = __DIR__."/YuglifyCompressorTest/test.min.css";
        
        $this->assertTrue(file_exists($filename));
        
        $this->assertTrue(
            $this->object->compressFile($filename, $type, $outputFilename)
        );
        
        $this->assertFalse(file_exists($filename));
        
        $this->assertTrue(file_exists($outputFilename));
        
        $content = <<<EOT
.image1 {
    background:url(I/1.png);
}
EOT;
        $this->assertContains(
            \RPI\Foundation\Helpers\Utils::normalizeString($content, true),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents($outputFilename),
                true
            )
        );
        
        if ($deleteOutputFile) {
            unlink($outputFilename);
        }
    }
}
