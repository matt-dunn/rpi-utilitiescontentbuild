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

    public static function find(
        $path,
        $pattern,
        $recursive = true
    ) {
        $files = array();
        self::findFiles(realpath($path), $pattern, $files, $recursive);
        return $files;
    }

    private static function findFiles($path, $pattern, array &$files, $recursive)
    {
        if (file_exists($path)) {
            $path = rtrim(str_replace("\\", "/", $path), '/') . '/';
            $dir = dir($path);
            while (false !== ($entry = $dir->read())) {
                $fullname = $path . $entry;
                if ($recursive && $entry != '.' && $entry != '..' && substr($entry, 0, 1) != "." && is_dir($fullname)) {
                    self::findFiles($fullname, $pattern, $files, $recursive);
                } elseif (is_file($fullname) && preg_match($pattern, $entry)) {
                    $files[$fullname] = filemtime($fullname);
                }
            }
            $dir->close();
        }
    }
}
