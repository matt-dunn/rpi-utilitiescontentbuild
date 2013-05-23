<?php

namespace RPI\Utilities\ContentBuild\Command;

use Ulrichsg\Getopt;

class Support implements \RPI\Console\ICommand
{
    protected $optionDetails = null;
    
    public function __construct()
    {
        $this->optionDetails = array(
            "name" => "support",
            "option" => array(
                "s",
                "support",
                Getopt::NO_ARGUMENT, "Display details about ContentBuild"
            )
        );
    }
    
    public function getOption()
    {
        return $this->optionDetails;
    }

    public function getOptionName()
    {
        return $this->optionDetails["name"];
    }

    public function exec(
        \Psr\Log\LoggerInterface $logger,
        \Ulrichsg\Getopt $getopt,
        $value,
        array $operands,
        array $commandValues
    ) {
        if (isset($value)) {
            $logger->setLogLevel(
                array(
                    \Psr\Log\LogLevel::INFO
                )
            );
            
            displayHeader($logger);

            $logger->info("\nProcessors:");
            $this->getDetails($logger, __DIR__."/../Processors");
            
            $logger->info("\nResolvers:");
            $this->getDetails($logger, __DIR__."/../UriResolvers");
            
            $logger->info("\nPlugins:");
            $this->getDetails($logger, __DIR__."/../Plugins", "RPI\Utilities\ContentBuild\Lib\Model\Plugin");
            
            $logger->info("\nSupported file types:");
            $logger->info(
                "    Configuration: ".
                implode(
                    ", ",
                    $this->getSupportedFileTypes(__DIR__."/../Lib/Configuration")
                )
            );
            
            $logger->info(
                "    Dependency: ".
                implode(
                    ", ",
                    $this->getSupportedFileTypes(__DIR__."/../Lib/Dependencies")
                )
            );

            return false;
        }
    }
    
    protected function getDetails(\Psr\Log\LoggerInterface $logger, $basePath, $baseInterfaceName = null)
    {
        $classes = \RPI\Foundation\Helpers\FileUtils::find($basePath, "*.php", null, false);
        $classes = array_keys($classes);
        asort($classes);
        
        foreach ($classes as $class) {
            $className = self::getClassName($class);
            $reflectionClass = new \ReflectionClass($className);
            $interfaceInstanceName = null;
            
            if (isset($baseInterfaceName)) {
                foreach ($reflectionClass->getInterfaceNames() as $interfaceName) {
                    if (substr($interfaceName, 0, strlen($baseInterfaceName)) == $baseInterfaceName) {
                        $interfaceInstanceName = $interfaceName;
                        break;
                    }
                }
            }
            
            if (in_array("RPI\Utilities\ContentBuild\Lib\Model\IPlugin", class_implements($className))
                && !$reflectionClass->isAbstract()) {
                $logger->info(
                    "    ".(
                        isset($interfaceInstanceName) ? "$className ($interfaceInstanceName)" : $className
                    ).": ".$className::getVersion()
                );
            }
        }
    }
    
    protected static function getClassName($classPath)
    {
        $fullPath = \RPI\Foundation\Helpers\FileUtils::realPath($classPath);
        $basePath = \RPI\Foundation\Helpers\FileUtils::realPath(__DIR__."/../../../../");
        
        return ltrim(
            str_replace(
                ".php",
                "",
                substr(str_replace(DIRECTORY_SEPARATOR, "\\", $fullPath), strlen($basePath))
            ),
            "\\"
        );
    }
    
    protected function getSupportedFileTypes($basePath)
    {
        $configurationTypes = array();
        
        $fullPath = \RPI\Foundation\Helpers\FileUtils::realPath($basePath);
        if (is_dir($fullPath)) {
            if ($dh = opendir($fullPath)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($fullPath.DIRECTORY_SEPARATOR.$file) && $file != "." && $file != "..") {
                        $configurationTypes[] = $file;
                    }
                }
                closedir($dh);
            }
        }

        asort($configurationTypes);
        
        return $configurationTypes;
    }
}
