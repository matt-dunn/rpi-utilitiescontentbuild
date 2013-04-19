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

    public static function realPath($path)
    {
        if (($absolutePath = realpath($path)) !== false) {
            return $absolutePath;
        }

        $pharBasePath = realpath($_SERVER["PHP_SELF"]);
        
        $pharPath = implode(
            '/',
            array_reduce(
                explode('/', substr($path, strlen("phar://".$pharBasePath))),
                function ($parts, $value) {
                    if ($value == '..') {
                        array_pop($parts);
                    } elseif ($value != '.') {
                        $parts[] = $value;
                    }
                    return $parts;
                }
            )
        );
            
        if (($resolvedPath = realpath($pharBasePath)) !== false) {
            if (file_exists($absolutePath = "phar://{$resolvedPath}{$pharPath}")) {
                return $absolutePath;
            }
        }
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
