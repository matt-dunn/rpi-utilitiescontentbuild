<?php

namespace RPI\Utilities\ContentBuild\Test\Plugins;

class DebugWriterTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Plugins\DebugWriter
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

        $this->object = new \RPI\Utilities\ContentBuild\Plugins\DebugWriter(
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
        \RPI\Foundation\Helpers\FileUtils::delTree(__DIR__."/DebugWriterTest/ROOT");
    }
    
    public function testGetVersion()
    {
        $this->assertEquals(
            "v".\RPI\Utilities\ContentBuild\Plugins\DebugWriter::VERSION,
            \RPI\Utilities\ContentBuild\Plugins\DebugWriter::getVersion()
        );
    }
    
    public function testWriteDebugFileCss()
    {
        $this->setUpObjects(__DIR__."/DebugWriterTest/ui.build-css.xml");
        
        $files = array(
            __DIR__."/DebugWriterTest/test.css"
        );
        
        $outputFilename = __DIR__."/DebugWriterTest/test.css";
        
        $webroot = $this->configuration->project->basePath."/".$this->configuration->project->appRoot;
        
        $this->assertTrue(
            $this->object->writeDebugFile(
                $this->configuration->project->builds[0],
                $files,
                $outputFilename,
                $webroot
            )
        );
        
        $this->assertTrue(file_exists(__DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/proxy.php"));
        
        $vendorBasePath = realpath(__DIR__."/../../../../../../../vendor");
        $configFilename = __DIR__."/DebugWriterTest/ui.build-css.xml";

        $content = <<<EOT
\$GLOBALS["autoloader"] = "$vendorBasePath/autoload.php";
\$GLOBALS["configuration-file"] = "$configFilename";
EOT;
        $this->assertContains(
            \RPI\Foundation\Helpers\Utils::normalizeString($content, true),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents(__DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/proxy.php"),
                true
            )
        );
        
        $inputFilename = __DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/test.min.css";

        $this->assertTrue(file_exists($inputFilename));
        
        $content = <<<EOT
@import url("/compiled/__debug/css/proxy.php?t=css&n=core&f=%2F..%2F%2Ftest.css");
EOT;
        
        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString($content),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents($inputFilename)
            )
        );
    }
    
    public function testWriteDebugFileCssPhar()
    {
        $this->setUpObjects(
            __DIR__."/DebugWriterTest/ui.build-css.xml",
            array(
                "pharRunning" => true
            )
        );
        
        $files = array(
            __DIR__."/DebugWriterTest/test.css"
        );
        
        $outputFilename = __DIR__."/DebugWriterTest/test.css";
        
        $webroot = $this->configuration->project->basePath."/".$this->configuration->project->appRoot;
        
        $this->assertTrue(
            $this->object->writeDebugFile(
                $this->configuration->project->builds[0],
                $files,
                $outputFilename,
                $webroot
            )
        );
        
        $this->assertTrue(file_exists(__DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/proxy.php"));
        
        $vendorBasePath = realpath(__DIR__."/../../../../../../../vendor");
        $configFilename = __DIR__."/DebugWriterTest/ui.build-css.xml";

        $content = <<<EOT
Phar::loadPhar("/usr/local/bin/phpunit", "phpunit.phar");
\$GLOBALS["autoloader"] = "phar://phpunit.phar/vendor/autoload.php";
\$GLOBALS["configuration-file"] = "$configFilename";
EOT;
        $this->assertContains(
            \RPI\Foundation\Helpers\Utils::normalizeString($content, true),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents(__DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/proxy.php"),
                true
            )
        );
        
        $inputFilename = __DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/test.min.css";

        $this->assertTrue(file_exists($inputFilename));
        
        $content = <<<EOT
@import url("/compiled/__debug/css/proxy.php?t=css&n=core&f=%2F..%2F%2Ftest.css");
EOT;
        
        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString($content),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents($inputFilename)
            )
        );
    }
    
    public function testWriteDebugFileCssDebugFilesExists()
    {
        $this->testWriteDebugFileCss();
        
        $this->testWriteDebugFileCss();
    }
    
    public function testWriteDebugFileCssHTTP()
    {
        $this->setUpObjects(__DIR__."/DebugWriterTest/ui.build-css.xml");
        
        $files = array(
            __DIR__."/DebugWriterTest/test.css",
            "http://localhost/test.css"
        );
        
        $outputFilename = __DIR__."/DebugWriterTest/test.css";
        
        $webroot = $this->configuration->project->basePath."/".$this->configuration->project->appRoot;
        
        $this->assertTrue(
            $this->object->writeDebugFile(
                $this->configuration->project->builds[0],
                $files,
                $outputFilename,
                $webroot
            )
        );
        
        $this->assertTrue(file_exists(__DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/proxy.php"));
        
        $inputFilename = __DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/test.min.css";

        $this->assertTrue(file_exists($inputFilename));
        
        $content = <<<EOT
@import url("/compiled/__debug/css/proxy.php?t=css&n=core&f=%2F..%2F%2Ftest.css");
@import url("/compiled/__debug/css/proxy.php?t=css&n=core&f=http%3A%2F%2Flocalhost%2Ftest.css");
EOT;
        
        $this->assertEquals(
            \RPI\Foundation\Helpers\Utils::normalizeString($content, true),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents($inputFilename),
                true
            )
        );
    }
    
    public function testWriteDebugFileCssMaxImports()
    {
        $this->setUpObjects(__DIR__."/DebugWriterTest/ui.build-css.xml");
        
        $numberOfImports = 40;
        
        $files = array(
        );
        
        for ($i = 0; $i < $numberOfImports; $i++) {
            $files[] = __DIR__."/DebugWriterTest/test.css";
        }
        
        $outputFilename = __DIR__."/DebugWriterTest/test.css";
        
        $webroot = $this->configuration->project->basePath."/".$this->configuration->project->appRoot;
        
        $this->assertTrue(
            $this->object->writeDebugFile(
                $this->configuration->project->builds[0],
                $files,
                $outputFilename,
                $webroot
            )
        );
        
        $this->assertTrue(file_exists(__DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/proxy.php"));
        
        $inputFilename = __DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/test.min.css";

        $this->assertTrue(file_exists($inputFilename));
        
        $content = <<<EOT
@import url("test.min_part001.css");
@import url("test.min_part002.css");
EOT;
        
        $this->assertContains(
            \RPI\Foundation\Helpers\Utils::normalizeString($content, true),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents($inputFilename),
                true
            )
        );
        
        $inputFilename = __DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/test.min_part001.css";

        $this->assertTrue(file_exists($inputFilename));
        
        $this->assertEquals(
            30,
            substr_count(
                file_get_contents($inputFilename),
                '@import url("/compiled/__debug/css/proxy.php?t=css&n=core&f=%2F..%2F%2Ftest.css");'
            )
        );
        
        $inputFilename = __DIR__."/DebugWriterTest/ROOT/compiled/__debug/css/test.min_part002.css";

        $this->assertTrue(file_exists($inputFilename));
        
        $this->assertEquals(
            10,
            substr_count(
                file_get_contents($inputFilename),
                '@import url("/compiled/__debug/css/proxy.php?t=css&n=core&f=%2F..%2F%2Ftest.css");'
            )
        );
    }
    
    public function testWriteDebugFileJs()
    {
        $this->setUpObjects(__DIR__."/DebugWriterTest/ui.build-js.xml");
        
        $files = array(
            __DIR__."/DebugWriterTest/test.js"
        );
        
        $outputFilename = __DIR__."/DebugWriterTest/test.js";
        
        $webroot = $this->configuration->project->basePath."/".$this->configuration->project->appRoot;
        
        $this->assertTrue(
            $this->object->writeDebugFile(
                $this->configuration->project->builds[0],
                $files,
                $outputFilename,
                $webroot
            )
        );
        
        $this->assertTrue(file_exists(__DIR__."/DebugWriterTest/ROOT/compiled/__debug/js/proxy.php"));
        
        $inputFilename = __DIR__."/DebugWriterTest/ROOT/compiled/__debug/js/test.min.js";

        $this->assertTrue(file_exists($inputFilename));
        
        $content = <<<EOT
document.prepareScript("/compiled/__debug/js/proxy.php?t=js&n=core&f=%2F..%2F%2Ftest.js");
EOT;
        
        $this->assertContains(
            \RPI\Foundation\Helpers\Utils::normalizeString($content, true),
            \RPI\Foundation\Helpers\Utils::normalizeString(
                file_get_contents($inputFilename),
                true
            )
        );
    }
    
    public function testWriteDebugFileJsDebugFilesExists()
    {
        $this->testWriteDebugFileJs();
        
        $this->testWriteDebugFileJs();
    }
}
