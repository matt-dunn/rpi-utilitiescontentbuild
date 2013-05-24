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
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project
     * @param array $options
     */
    public function __construct(
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IProject $project,
        array $options = null
    );
    
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
     * @param string $buffer
     * 
     * @return string Processed buffer
     */
    public function preProcess(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
        $inputFilename,
        $buffer
    );
    
    /**
     * Run the processor
     * 
     * @param \RPI\Utilities\ContentBuild\Lib\Processor $processor
     * @param \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver
     * @param \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build
     * @param string $inputFilename
     * @param string $buffer
     * 
     * @return string Processed buffer
     */
    public function process(
        \RPI\Utilities\ContentBuild\Lib\Processor $processor,
        \RPI\Utilities\ContentBuild\Lib\UriResolver $resolver,
        \RPI\Utilities\ContentBuild\Lib\Model\Configuration\IBuild $build,
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
    
    /**
     * Indicates if the processor can directly process the processesor pipe buffer
     * Returns false if the processor has to directly process the input file
     * rather than the buffer.
     * 
     * @return boolean
     */
    public function canProcessBuffer();
}
