<?php

namespace RPI\Utilities\ContentBuild\Lib\Helpers;

/**
 * FileUtils
 * @author Matt Dunn
 */
class FileUtils
{
    private function __construct()
    {
    }

    /**
     * 
     * @param string $basePath
     * @param string $includes      Pipe delimited list of patterns
     * @param string $excludes      Pipe delimited list of patterns
     * @param boolean $recursive
     * 
     * @return array
     */
    public static function find(
        $basePath,
        $includes,
        $excludes = null,
        $recursive = true
    ) {
        $files = array();
        
        if (file_exists($basePath)) {
            $dir = dir($basePath);
            while (false !== ($entry = $dir->read())) {
                $fullname = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$entry;
                if ($recursive && $entry != '.' && $entry != '..' && substr($entry, 0, 1) != "." && is_dir($fullname)) {
                    $files += self::find($fullname, $includes, $excludes, $recursive);
                } elseif (is_file($fullname)
                    && self::isMatch(explode("|", $includes), $fullname)
                    && (!isset($excludes) || (isset($excludes) && !self::isMatch(explode("|", $excludes), $fullname)))
                ) {
                    $files[$fullname] = filemtime($fullname);
                }
            }
            $dir->close();
        }
        
        return $files;
    }
    
    private static function isMatch(array $patterns, $file)
    {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $file)) {
                return true;
            }
        }
        
        return false;
    }
}
