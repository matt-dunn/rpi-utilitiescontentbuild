<?php

namespace RPI\Utilities\ContentBuild\Test\Plugins;

class YUICompressorTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Plugins\YUICompressor
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

        $this->object = new \RPI\Utilities\ContentBuild\Plugins\YUICompressor(
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
            "v".\RPI\Utilities\ContentBuild\Plugins\YUICompressor::VERSION,
            \RPI\Utilities\ContentBuild\Plugins\YUICompressor::getVersion()
        );
        
        $this->assertContains(
            "yuicompressor ".\RPI\Utilities\ContentBuild\Plugins\YUICompressor::VERSION_YUI,
            \RPI\Utilities\ContentBuild\Plugins\YUICompressor::getVersion()
        );
    }
    
    public function testCompressFileCss($deleteOutputFile = true)
    {
        $this->setUpObjects(__DIR__."/YUICompressorTest/ui.build.xml");
        
        $filename = __DIR__."/YUICompressorTest/test-compress.css";
        
        copy(__DIR__."/YUICompressorTest/test.css", $filename);
        
        $type = "css";
        $outputFilename = __DIR__."/YUICompressorTest/test.min.css";
        
        $this->assertTrue(file_exists($filename));
        
        $this->assertTrue(
            $this->object->compressFile($filename, $type, $outputFilename)
        );
        
        $this->assertFalse(file_exists($filename));
        
        $this->assertTrue(file_exists($outputFilename));
        
        $content = <<<EOT
.image1{background:url(I/1.png)}
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
    
    public function testCompressFileJs($deleteOutputFile = true)
    {
        $this->setUpObjects(__DIR__."/YUICompressorTest/ui.build.xml");
        
        $filename = __DIR__."/YUICompressorTest/test-compress.js";
        
        copy(__DIR__."/YUICompressorTest/test.js", $filename);
        
        $type = "css";
        $outputFilename = __DIR__."/YUICompressorTest/test.min.js";
        
        $this->assertTrue(file_exists($filename));
        
        $this->assertTrue(
            $this->object->compressFile($filename, $type, $outputFilename)
        );
        
        $this->assertFalse(file_exists($filename));
        
        $this->assertTrue(file_exists($outputFilename));
        
        $content = <<<EOT
function test(){alert("Test JS")}
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
    
    public function testCompressFileOutputFileExists()
    {
        $outputFilename = $this->testCompressFileCss(false);
        
        $this->assertTrue(file_exists($outputFilename));
        
        $this->testCompressFileCss();
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testCompressFileInvalidFile()
    {
        $this->setUpObjects(__DIR__."/YUICompressorTest/ui.build.xml");
        
        $this->object->compressFile(__DIR__."/missing.css", "css", __DIR__."/missing.min.css");
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testCompressFileInvalidCompressorLocation()
    {
        $this->setUpObjects(
            __DIR__."/YUICompressorTest/ui.build.xml",
            array(
                "yuicompressorLocation" => __DIR__."/missing.jar"
            )
        );
    }
    
    public function testCompressFilePhar()
    {
        $this->setUpObjects(
            __DIR__."/YUICompressorTest/ui.build.xml",
            array(
                "pharRunning" => true
            )
        );
        
        $filename = __DIR__."/YUICompressorTest/test-compress.js";
        
        copy(__DIR__."/YUICompressorTest/test.js", $filename);
        
        $type = "css";
        $outputFilename = __DIR__."/YUICompressorTest/test.min.js";
        
        $this->assertTrue(file_exists($filename));
        
        $this->assertTrue(
            $this->object->compressFile($filename, $type, $outputFilename)
        );
        
        $yuiCompressorFilename = $this->object->getYUICompressorLocation();
        $this->assertTrue(file_exists($yuiCompressorFilename));
        
        unlink($outputFilename);
        
        $this->object = null;
        
        $this->assertFalse(file_exists($yuiCompressorFilename));
    }
}
