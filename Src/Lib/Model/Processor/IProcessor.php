<?php

/**
 * RPI Framework
 * 
 * (c) Matt Dunn <matt@red-pixel.co.uk>
 */

namespace RPI\Utilities\ContentBuild\Lib\Model\Processor;

/**
 * Processor interface
 */
interface IProcessor extends \RPI\Utilities\ContentBuild\Lib\Model\IPlugin
{
    /**
     * Initialise processor
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Processor $processor
     * @param integer $processorIndex
     */
    public function init(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        $processorIndex
    );
    
    /**
     * Pre-process processor
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Processor $processor
     * @param \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build
     * @param string $inputFilename
     * @param string $outputFilename
     * @param string $buffer
     */
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $outputFilename,
        $buffer
    );
    
    /**
     * Run the processor
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Processor $processor
     * @param \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver
     * @param string $inputFilename
     * @param string $buffer
     */
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        $inputFilename,
        $buffer
    );
    
    /**
     * Complete processor
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Processor $processor
     */
    public function complete(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor
    );
}
