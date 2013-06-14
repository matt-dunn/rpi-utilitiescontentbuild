<?php

namespace RPI\Utilities\ContentBuild\Test\Processors;

class ImagesTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Processors\Images
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
            __DIR__."/ImagesTest/ui.build.xml"
        );

        $this->processor = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project,
            false
        );

        $this->object = new \RPI\Utilities\ContentBuild\Processors\Images(
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
        \RPI\Foundation\Helpers\FileUtils::delTree(__DIR__."/ImagesTest/ROOT");
        \RPI\Foundation\Event\Manager::removeEventListener("RPI\Utilities\ContentBuild\Events\ImageCheckAvailability");
    }
    
    public function testGetVersion()
    {
        $this->assertEquals(
            "v".\RPI\Utilities\ContentBuild\Processors\Images::VERSION,
            $this->object->getVersion()
        );
    }
    
    public function testInit()
    {
        $this->assertNull(
            $this->object->init(0)
        );
    }
    
    public function testCompleteEmptyDirectories()
    {
        mkdir(__DIR__."/ImagesTest/ROOT/compiled/css/empty/sub/dir", 0777, true);
        mkdir(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/empty/sub/dir", 0777, true);
        
        $this->assertNull(
            $this->object->complete()
        );
        
        $this->assertFalse(
            is_dir(__DIR__."/ImagesTest/ROOT/compiled/css/empty/sub/dir")
        );
        
        $this->assertFalse(
            is_dir(__DIR__."/ImagesTest/ROOT/compiled/css/empty/sub")
        );
        
        $this->assertFalse(
            is_dir(__DIR__."/ImagesTest/ROOT/compiled/css/empty")
        );
        
        $this->assertFalse(
            is_dir(__DIR__."/ImagesTest/ROOT/compiled/css")
        );
        
        $this->assertTrue(
            is_dir(__DIR__."/ImagesTest/ROOT/compiled")
        );
        
        $this->assertFalse(
            is_dir(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/empty/sub/dir")
        );
        
        $this->assertFalse(
            is_dir(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/empty/sub")
        );
        
        $this->assertFalse(
            is_dir(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/empty")
        );
        
        $this->assertFalse(
            is_dir(__DIR__."/ImagesTest/ROOT/compiled/__debug/css")
        );
        
        $this->assertTrue(
            is_dir(__DIR__."/ImagesTest/ROOT/compiled/__debug")
        );
    }
    
    public function testCompleteWithImages()
    {
        mkdir(__DIR__."/ImagesTest/ROOT/compiled/css/I", 0777, true);
        mkdir(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/I", 0777, true);
        
        copy(__DIR__."/ImagesTest/I/1.png", __DIR__."/ImagesTest/ROOT/compiled/css/I/old.png");
        copy(__DIR__."/ImagesTest/I/1.png", __DIR__."/ImagesTest/ROOT/compiled/__debug/css/I/old.png");
        
        touch(__DIR__."/ImagesTest/ROOT/compiled/css/I/old.png", time() - 10);
        touch(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/I/old.png", time() - 10);
        
        $this->assertNull(
            $this->object->init(0)
        );
        
        copy(__DIR__."/ImagesTest/I/1.png", __DIR__."/ImagesTest/ROOT/compiled/css/I/new.png");
        copy(__DIR__."/ImagesTest/I/1.png", __DIR__."/ImagesTest/ROOT/compiled/__debug/css/I/new.png");
        
        $this->assertNull(
            $this->object->complete()
        );
        
        $this->assertFalse(
            file_exists(__DIR__."/ImagesTest/ROOT/compiled/css/I/old.png")
        );
        
        $this->assertFalse(
            file_exists(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/I/old.png")
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/ImagesTest/ROOT/compiled/css/I/new.png")
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/I/new.png")
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
        
        $inputFilename = __DIR__."/ImagesTest/test.css";
        
        $css = <<<EOT
.image1 {
    background:url(I/1.png);
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
        
        $this->assertTrue(file_exists(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/I/1.png"), "File not found");
        
        $this->assertTrue(file_exists(__DIR__."/ImagesTest/ROOT/compiled/css/I/1.png"), "File not found");
    }
    
    
    public function testProcessDataUrl()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/ImagesTest/test-data.css";
        
        /* @codingStandardsIgnoreStart */
        $css = <<<EOT
.image1 {
    background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAYCAYAAADtaU2/AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QzVERUQwQkZBNzJCMTFFMkEzOTNCQTVFRDdDRDk1NDkiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QzVERUQwQzBBNzJCMTFFMkEzOTNCQTVFRDdDRDk1NDkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpDNURFRDBCREE3MkIxMUUyQTM5M0JBNUVEN0NEOTU0OSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpDNURFRDBCRUE3MkIxMUUyQTM5M0JBNUVEN0NEOTU0OSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PsnLs9QAAACnSURBVHjaYmRgYGAHYgUg5mGgPfgBxA+A+CtDY2Oj84cPH87/////338ag2/fvt2fNWtWNNBiRob379+f+U9H8OvXr1ehoaGcjP/+/fvFyMjIykBH8OPHDwUmoMUn6Gkp0NPPOTg4njAxMzMnAPlHQGJ0sPcGMHQDgfgvIw5XUWQ60GCCapgYBggMmMWMowXIaAEyWoCMFiCjBchoATJagMAKEIAAAwAvPPQdjbEbIQAAAABJRU5ErkJggg==);
}
EOT;
        /* @codingStandardsIgnoreEnd */
        
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
    
    public function testProcessWithResolver()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/ImagesTest/test-resolver.css";
        
        $css = <<<EOT
.image1 {
    background:url(Src/RPI/View/Css/I/1.png);
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
        
        $this->assertTrue(
            file_exists(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/Src/RPI/View/Css/I/1.png"),
            "File not found"
        );
        
        $this->assertTrue(
            file_exists(__DIR__."/ImagesTest/ROOT/compiled/css/Src/RPI/View/Css/I/1.png"),
            "File not found"
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\FileNotFound
     */
    public function testPreProcessWithResolverNotFound()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/ImagesTest/test-resolver-not-found.css";
        
        $css = <<<EOT
.image1 {
    background:url(Src/RPI/View/Css/I/1.png);
}
EOT;
        
        $this->object->preProcess(
            $resolver,
            $this->configuration->project->builds[0],
            $inputFilename,
            file_get_contents($inputFilename)
        );
    }
    
    public function testProcessWithResolverNotFound()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/ImagesTest/test-resolver-not-found.css";
        
        $css = <<<EOT
.image1 {
    background:url(composer://rpi/view#Src/RPI/View/Css/I/Sprites/1.png);
}
EOT;
        
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
        
        $this->assertFalse(
            file_exists(__DIR__."/ImagesTest/ROOT/compiled/__debug/css/Src/RPI/View/Css/I/1.png"),
            "File not found"
        );
        
        $this->assertFalse(
            file_exists(__DIR__."/ImagesTest/ROOT/compiled/css/Src/RPI/View/Css/I/1.png"),
            "File not found"
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
        
        $inputFilename = __DIR__."/ImagesTest/test-file-not-found.css";
        
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
    
    public function testProcessFileNotFoundWithEventFound()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/ImagesTest/test-file-not-found.css";
        
        $css = <<<EOT
.image1 {
    background:url(I/not-found.png);
}
EOT;
        
        \RPI\Foundation\Event\Manager::addEventListener(
            "RPI\Utilities\ContentBuild\Events\ImageCheckAvailability",
            function (\RPI\Foundation\Event $event, $params) {
                $event->srcEvent->setReturnValue(true);
            }
        );
        
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
    public function testProcessFileNotFoundWithEventNotFound()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/ImagesTest/test-file-not-found.css";
        
        \RPI\Foundation\Event\Manager::addEventListener(
            "RPI\Utilities\ContentBuild\Events\ImageCheckAvailability",
            function (\RPI\Foundation\Event $event, $params) {
                $event->srcEvent->setReturnValue(false);
            }
        );
        
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
