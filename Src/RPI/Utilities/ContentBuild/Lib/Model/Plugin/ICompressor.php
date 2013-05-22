<?php

namespace RPI\Utilities\ContentBuild\Lib\Model\Plugin;

interface ICompressor extends \RPI\Utilities\ContentBuild\Lib\Model\IPlugin
{
    /**
     * 
     * @param string $filename
     * @param string $type
     * @param string $outputFilename
     * 
     * @return bool
     */
    public function compressFile($filename, $type, $outputFilename);
}
