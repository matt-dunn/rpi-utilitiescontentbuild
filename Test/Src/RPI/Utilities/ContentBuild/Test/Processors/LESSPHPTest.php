<?php

namespace RPI\Utilities\ContentBuild\Test\Processors;

class LESSPHPTest extends \RPI\Test\Harness\Base
{
    /**
     * @var \RPI\Utilities\ContentBuild\Processors\LESSPHP
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
            __DIR__."/LESSPHPTest/ui.build.xml"
        );

        $this->processor = new \RPI\Utilities\ContentBuild\Lib\Processor(
            $this->logger,
            $this->configuration->project,
            false
        );

        $this->object = new \RPI\Utilities\ContentBuild\Processors\LESSPHP(
            $this->processor,
            $this->configuration->project,
            array(
            )
        );
        
        \RPI\Foundation\Helpers\FileUtils::delTree(__DIR__."/LESSPHPTest/ROOT");
        $this->processor->setMetadata("sprites", null);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        
        \RPI\Foundation\Helpers\FileUtils::delTree(__DIR__."/LESSPHPTest/ROOT");
    }

    public function testProcess()
    {
        $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver(
            $this->logger,
            $this->processor,
            $this->configuration->project
        );
        
        $inputFilename = __DIR__."/LESSPHPTest/test.less";
        
        $this->assertTrue(
            $this->object->preProcess(
                $resolver,
                $this->configuration->project->builds[0],
                $inputFilename,
                file_get_contents($inputFilename)
            )
        );
        
        $css = <<<EOT
.border {
  padding: 20px;
  margin: 20px;
  background: url(I/Sprites/core.png) no-repeat 0px 0px;
  width: 10px;
  height: 10px;
  content: '';
}
.border2 {
  background: url(I/Sprites/core.png) no-repeat -12px 0px;
  width: 24px;
  height: 24px;
  content: '';
}
.border3 {
  background: url(I/Sprites/core.png) no-repeat -38px 0px;
  width: 10px;
  height: 10px;
  content: '';
}
.border4 {
  background: url(I/Sprites/core.png) no-repeat -50px 0px;
  width: 17px;
  height: 17px;
  content: '';
}
.content-navigation {
  border-color: #3bbfce;
  color: #2ca2af;
}
.border {
  padding: 13.3333333333px;
  margin: 13.3333333333px;
  border-color: #3bbfce;
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
        
        $this->assertTrue(file_exists(__DIR__."/LESSPHPTest/ROOT/compiled/css/I/Sprites/core.png"));
        
        $this->assertEquals(
            array(
                69,
                24,
                3,
                "width=\"69\" height=\"24\"",
                "bits" => 8,
                "mime" => "image/png"
            ),
            getimagesize(__DIR__."/LESSPHPTest/ROOT/compiled/css/I/Sprites/core.png")
        );
        
        $this->assertTrue(file_exists(__DIR__."/LESSPHPTest/ROOT/compiled/__debug/css/I/Sprites/core.png"));
        
        $this->assertEquals(
            array(
                69,
                24,
                3,
                "width=\"69\" height=\"24\"",
                "bits" => 8,
                "mime" => "image/png"
            ),
            getimagesize(__DIR__."/LESSPHPTest/ROOT/compiled/__debug/css/I/Sprites/core.png")
        );
    }
}
