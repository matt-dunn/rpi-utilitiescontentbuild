<?php

namespace RPI\Utilities\ContentBuild\Test\Lib\Configuration\Xml;

class ProjectTest extends \RPI\Test\Harness\Base
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testConstructMissingConfigFile()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project(
            $this->logger,
            __DIR__."/ProjectTest/ui.build-missing.xml"
        );
    }
    
    /**
     * @expectedException \RPI\Foundation\Exceptions\RuntimeException
     */
    public function testConstructInvalidConfigFile()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project(
            $this->logger,
            __DIR__."/ProjectTest/ui.build-invalid.xml"
        );
    }
    
    public function testConstruct()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project(
            $this->logger,
            __DIR__."/ProjectTest/ui.build.xml",
            array(
                "debug-include" => true
            )
        );
        
        $this->assertEquals("website", $object->getName());
        $this->assertEquals("website", $object->name);
        
        $this->assertEquals("TEMPLATE", $object->getPrefix());
        $this->assertEquals("TEMPLATE", $object->prefix);
        
        $this->assertEquals(2, count($object->getBuilds()));
        $this->assertEquals(2, count($object->builds));
        $this->assertEquals(
            array(
                '' . "\0" . '*' . "\0" . 'buildDirectory' => '',
                '' . "\0" . '*' . "\0" . 'files' =>
                array(
                    0 => 'test.css',
                ),
                '' . "\0" . '*' . "\0" . 'name' => 'core',
                '' . "\0" . '*' . "\0" . 'outputDirectory' => 'compiled/css/',
                '' . "\0" . '*' . "\0" . 'outputFilename' => 'test.css',
                '' . "\0" . '*' . "\0" . 'externalDependenciesNames' => null,
                '' . "\0" . '*' . "\0" . 'type' => 'css',
                '' . "\0" . '*' . "\0" . 'target' => null,
                '' . "\0" . '*' . "\0" . 'media' => 'all',
                '' . "\0" . '*' . "\0" . 'debugPath' =>
                    __DIR__.'/ProjectTest/ROOT/compiled/__debug/css',
            ),
            (array)$object->builds[0]
        );
        $this->assertEquals(
            array(
                '' . "\0" . '*' . "\0" . 'buildDirectory' => '',
                '' . "\0" . '*' . "\0" . 'files' =>
                array(
                    0 => 'test.js',
                ),
                '' . "\0" . '*' . "\0" . 'name' => 'core',
                '' . "\0" . '*' . "\0" . 'outputDirectory' => 'compiled/js/',
                '' . "\0" . '*' . "\0" . 'outputFilename' =>
                    __DIR__."/ProjectTest/ROOT/compiled/js/TEMPLATE.website-core.js",
                '' . "\0" . '*' . "\0" . 'externalDependenciesNames' => "core",
                '' . "\0" . '*' . "\0" . 'type' => 'js',
                '' . "\0" . '*' . "\0" . 'target' => "footer",
                '' . "\0" . '*' . "\0" . 'media' => 'all',
                '' . "\0" . '*' . "\0" . 'debugPath' =>
                    __DIR__.'/ProjectTest/ROOT/compiled/__debug/js',
            ),
            (array)$object->builds[1]
        );
        
        $this->assertEquals("ROOT", $object->getAppRoot());
        $this->assertEquals("ROOT", $object->appRoot);
        
        $this->assertEquals(__DIR__."/ProjectTest", $object->getBasePath());
        $this->assertEquals(__DIR__."/ProjectTest", $object->basePath);
        
        $this->assertEquals(__DIR__."/ProjectTest/ui.build.xml", $object->getConfigurationFile());
        $this->assertEquals(__DIR__."/ProjectTest/ui.build.xml", $object->configurationFile);
        
        $this->assertEquals(1, count($object->getProcessors()));
        $this->assertEquals(1, count($object->processors));
        $this->assertEquals(
            array(
                '' . "\0" . '*' . "\0" . 'type' => 'RPI\\Utilities\\ContentBuild\\Processors\\HashImages',
                '' . "\0" . '*' . "\0" . 'params' =>
                array(
                    0 =>
                    array(
                        'hashAlgorithm' => 'md5',
                    ),
                ),
            ),
            (array)$object->processors["RPI\Utilities\ContentBuild\Processors\HashImages"]
        );
        
        $this->assertEquals(1, count($object->getResolvers()));
        $this->assertEquals(1, count($object->resolvers));
        $this->assertEquals(
            array(
                '' . "\0" . '*' . "\0" . 'type' => 'RPI\\Utilities\\ContentBuild\\UriResolvers\\Composer',
                '' . "\0" . '*' . "\0" . 'params' =>
                array (
                    0 =>
                    array (
                        'vendorPath' => '../../../../../../../../../../vendor',
                    )
                )
            ),
            (array)$object->resolvers["RPI\Utilities\ContentBuild\UriResolvers\Composer"]
        );
        
        $this->assertEquals(1, count($object->getPlugins()));
        $this->assertEquals(1, count($object->plugins));
        $this->assertEquals(
            array(
                '' . "\0" . '*' . "\0" . 'interface' => 'RPI\\Utilities\\ContentBuild\\Lib\\Model\\Plugin\\ICompressor',
                '' . "\0" . '*' . "\0" . 'type' => 'RPI\\Utilities\\ContentBuild\\Plugins\\YuglifyCompressor',
                '' . "\0" . '*' . "\0" . 'params' =>
                array (
                    0 =>
                    array (
                        'testParam' => 'value for test param',
                    ),
                ),
            ),
            (array)$object->plugins["RPI\Utilities\ContentBuild\Lib\Model\Plugin\ICompressor"]
        );
        
        $this->assertTrue($object->getIncludeDebug());
        $this->assertTrue($object->includeDebug);
        
        $this->assertSame($this->logger, $object->getLogger());
        $this->assertSame($this->logger, $object->logger);
        
        $this->assertEquals(
            array(
                "debug-include" => true
            ),
            $object->getOptions()
        );
        $this->assertEquals(
            array(
                "debug-include" => true
            ),
            $object->options
        );
    }
    
    public function testConstructSingleBuild()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project(
            $this->logger,
            __DIR__."/ProjectTest/ui.build-single-build.xml",
            array(
                "debug-include" => true
            )
        );
        
        $this->assertEquals("website", $object->getName());
        $this->assertEquals("website", $object->name);
        
        $this->assertEquals("TEMPLATE", $object->getPrefix());
        $this->assertEquals("TEMPLATE", $object->prefix);
        
        $this->assertEquals(1, count($object->getBuilds()));
        $this->assertEquals(1, count($object->builds));
        $this->assertEquals(
            array(
                '' . "\0" . '*' . "\0" . 'buildDirectory' => '',
                '' . "\0" . '*' . "\0" . 'files' =>
                array(
                    0 => 'test.css',
                ),
                '' . "\0" . '*' . "\0" . 'name' => 'core',
                '' . "\0" . '*' . "\0" . 'outputDirectory' => 'compiled/css/',
                '' . "\0" . '*' . "\0" . 'outputFilename' => 'test.css',
                '' . "\0" . '*' . "\0" . 'externalDependenciesNames' => null,
                '' . "\0" . '*' . "\0" . 'type' => 'css',
                '' . "\0" . '*' . "\0" . 'target' => null,
                '' . "\0" . '*' . "\0" . 'media' => 'all',
                '' . "\0" . '*' . "\0" . 'debugPath' =>
                    __DIR__.'/ProjectTest/ROOT/compiled/__debug/css',
            ),
            (array)$object->builds[0]
        );
        
        $this->assertEquals("ROOT", $object->getAppRoot());
        $this->assertEquals("ROOT", $object->appRoot);
        
        $this->assertEquals(__DIR__."/ProjectTest", $object->getBasePath());
        $this->assertEquals(__DIR__."/ProjectTest", $object->basePath);
        
        $this->assertEquals(__DIR__."/ProjectTest/ui.build-single-build.xml", $object->getConfigurationFile());
        $this->assertEquals(__DIR__."/ProjectTest/ui.build-single-build.xml", $object->configurationFile);
        
        $this->assertNull($object->getProcessors());
        $this->assertNull($object->processors);
        
        $this->assertNull($object->getResolvers());
        $this->assertNull($object->resolvers);
        
        $this->assertNull($object->getPlugins());
        $this->assertNull($object->plugins);
        
        $this->assertTrue($object->getIncludeDebug());
        $this->assertTrue($object->includeDebug);
        
        $this->assertSame($this->logger, $object->getLogger());
        $this->assertSame($this->logger, $object->logger);
        
        $this->assertEquals(
            array(
                "debug-include" => true
            ),
            $object->getOptions()
        );
        $this->assertEquals(
            array(
                "debug-include" => true
            ),
            $object->options
        );
    }
    
    public function testConstructNoBasePath()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project(
            $this->logger,
            __DIR__."/ProjectTest/ui.build-nobasepath.xml"
        );
        
        $this->assertEquals(realpath(__DIR__."/../"), $object->getBasePath());
        $this->assertEquals(realpath(__DIR__."/../"), $object->basePath);
    }
    
    public function testConstructMultiplePlugins()
    {
        $object = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project(
            $this->logger,
            __DIR__."/ProjectTest/ui.build-multiple-plugins.xml"
        );
        
        $this->assertEquals(2, count($object->getProcessors()));
        $this->assertEquals(2, count($object->processors));
        
        $this->assertEquals(1, count($object->getResolvers()));
        $this->assertEquals(1, count($object->resolvers));
        
        $this->assertEquals(1, count($object->getPlugins()));
        $this->assertEquals(1, count($object->plugins));
    }
}
