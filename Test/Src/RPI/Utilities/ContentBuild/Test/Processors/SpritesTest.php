<?php

namespace RPI\Utilities\ContentBuild\Test\Processors;

class SpritesTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Processors\Sprites
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
            __DIR__."/SpriteTest/ui.build.xml"
        );

        $this->processor = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project,
            false
        );

        $this->object = new \RPI\Utilities\ContentBuild\Processors\Sprites(
            $this->processor,
            $this->configuration->project,
            array(
            )
        );
        
        \RPI\Foundation\Helpers\FileUtils::delTree(__DIR__."/SpriteTest/ROOT");
        $this->processor->setMetadata("sprites", null);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        \RPI\Foundation\Helpers\FileUtils::delTree(__DIR__."/SpriteTest/ROOT");
        
        $this->object = null;
    }
    
    public function testGetVersion()
    {
        $this->assertEquals(
            "v".\RPI\Utilities\ContentBuild\Processors\Sprites::VERSION,
            $this->object->getVersion()
        );
    }
    
    public function testInit()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SpriteTest/test.css";
        
        $this->assertTrue(
            $this->object->preProcess(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $this->assertTrue(file_exists(__DIR__."/SpriteTest/ROOT/compiled/css/I/Sprites/core.png"));
        
        $this->assertTrue(file_exists(__DIR__."/SpriteTest/ROOT/compiled/__debug/css/I/Sprites/core.png"));
        
        $this->assertNull(
            $this->object->init(0)
        );
        
        $this->assertFalse(file_exists(__DIR__."/SpriteTest/ROOT/compiled/css/I/Sprites/core.png"));
        
        $this->assertFalse(file_exists(__DIR__."/SpriteTest/ROOT/compiled/__debug/css/I/Sprites/core.png"));
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
        
        $inputFilename = __DIR__."/SpriteTest/test.css";
        
        $css = <<<EOT
.sprite1:after {
    background:url(I/Sprites/core.png) no-repeat 0px 0px;width:24px;height:24px;content:'';
}

.sprite2:after {
    background:url(I/Sprites/core.png) no-repeat -26px 0px;width:10px;height:10px;content:'';
}

.sprite3:after {
    background:url(I/Sprites/core.png) no-repeat -38px 0px;width:10px;height:10px;content:'';
}

.sprite4:after {
    background:url(I/Sprites/core.png) no-repeat -50px 0px;width:17px;height:17px;content:'';
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
        
        $this->assertTrue(file_exists(__DIR__."/SpriteTest/ROOT/compiled/css/I/Sprites/core.png"));
        
        $this->assertEquals(
            array(
                69,
                24,
                3,
                "width=\"69\" height=\"24\"",
                "bits" => 8,
                "mime" => "image/png"
            ),
            getimagesize(__DIR__."/SpriteTest/ROOT/compiled/css/I/Sprites/core.png")
        );
        
        $this->assertTrue(file_exists(__DIR__."/SpriteTest/ROOT/compiled/__debug/css/I/Sprites/core.png"));
        
        $this->assertEquals(
            array(
                69,
                24,
                3,
                "width=\"69\" height=\"24\"",
                "bits" => 8,
                "mime" => "image/png"
            ),
            getimagesize(__DIR__."/SpriteTest/ROOT/compiled/__debug/css/I/Sprites/core.png")
        );
    }

    /**
     * @expectedException \RPI\Foundation\Exceptions\FileNotFound
     */
    public function testProcessSpriteNotFound()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SpriteTest/test.css";
        
        $this->assertTrue(
            $this->object->preProcess(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $inputFilename = __DIR__."/SpriteTest/test-file-not-found.css";
        
        $this->object->process(
            $resolver,
            $this->configuration->project->builds[0],
            $inputFilename,
            file_get_contents($inputFilename)
        );
    }
    
    public function testPreProcessCheckImageAvailability()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SpriteTest/test.css";
        
        $this->assertTrue(
            $this->object->preProcess(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $event = new \RPI\Utilities\ContentBuild\Events\ImageCheckAvailability(
            array(
                "imageUri" => "invalid"
            )
        );
        
        \RPI\Foundation\Event\Manager::fire($event);
        
        $this->assertNull(
            $event->getReturnValue()
        );
        
        $event = new \RPI\Utilities\ContentBuild\Events\ImageCheckAvailability(
            array(
                "imageUri" => "I/Sprites/core.png"
            )
        );
        
        \RPI\Foundation\Event\Manager::fire($event);

        $this->assertTrue(
            $event->getReturnValue()
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\InvalidArgument
     */
    public function testProcessMaxWidthInvalidSize()
    {
        $this->object = new \RPI\Utilities\ContentBuild\Processors\Sprites(
            $this->processor,
            $this->configuration->project,
            array(
                "maxSpriteWidth" => 30000
            )
        );
    }
        
    /**
     * @expectedException \RPI\Foundation\Exceptions\InvalidArgument
     */
    public function testProcessMaxWidthInvalidValue()
    {
        $this->object = new \RPI\Utilities\ContentBuild\Processors\Sprites(
            $this->processor,
            $this->configuration->project,
            array(
                "maxSpriteWidth" => "invalid"
            )
        );
    }
        
    public function testProcessMaxWidth()
    {
        $this->object = new \RPI\Utilities\ContentBuild\Processors\Sprites(
            $this->processor,
            $this->configuration->project,
            array(
                "maxSpriteWidth" => 30
            )
        );
        
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SpriteTest/test.css";
        
        $css = <<<EOT
.sprite1:after {
    background:url(I/Sprites/core.png) no-repeat 0px 0px;width:24px;height:24px;content:'';
}

.sprite2:after {
    background:url(I/Sprites/core.png) no-repeat 0px -26px;width:10px;height:10px;content:'';
}

.sprite3:after {
    background:url(I/Sprites/core.png) no-repeat -12px -26px;width:10px;height:10px;content:'';
}

.sprite4:after {
    background:url(I/Sprites/core.png) no-repeat 0px -38px;width:17px;height:17px;content:'';
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
        
        $this->assertTrue(file_exists(__DIR__."/SpriteTest/ROOT/compiled/css/I/Sprites/core.png"));
        
        $this->assertEquals(
            array(
                30,
                55,
                3,
                "width=\"30\" height=\"55\"",
                "bits" => 8,
                "mime" => "image/png"
            ),
            getimagesize(__DIR__."/SpriteTest/ROOT/compiled/css/I/Sprites/core.png")
        );
        
        $this->assertTrue(file_exists(__DIR__."/SpriteTest/ROOT/compiled/__debug/css/I/Sprites/core.png"));
        
        $this->assertEquals(
            array(
                30,
                55,
                3,
                "width=\"30\" height=\"55\"",
                "bits" => 8,
                "mime" => "image/png"
            ),
            getimagesize(__DIR__."/SpriteTest/ROOT/compiled/__debug/css/I/Sprites/core.png")
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
        
        $inputFilename = __DIR__."/SpriteTest/test-file-not-found.css";
        
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
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testProcessOptionInvalid()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SpriteTest/test-option-invalid.css";
        
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
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testProcessOptionRatioInvalidMissing()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SpriteTest/test-option-ratio-invalid-missing.css";
        
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
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testProcessOptionRatioInvalidValue()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SpriteTest/test-option-ratio-invalid-value.css";
        
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
    
    public function testProcessOptionRatio()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/SpriteTest/test-option-ratio.css";
        
        $css = <<<EOT
@media only screen and (-webkit-min-device-pixel-ratio: 2), 
only screen and (min-device-pixel-ratio: 2) {
    .sprite1:after {
        background:url(I/Sprites/coreX2.png) no-repeat 0px 0px;width:12px;height:12px;content:'';background-size:12px 12px
    }
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
