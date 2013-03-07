<?php

require_once __DIR__."/Constants.php";
require_once __DIR__."/Functions.php";

date_default_timezone_set("Europe/London");
        
require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/Autoload.php";

\RPI\Utilities\ContentBuild\Autoload::init();

\RPI\Utilities\ContentBuild\Lib\Exception\Handler::set("ContentBuild");

use Ulrichsg\Getopt;

$getopt = new Getopt(
    array(
        array("h", "help", Getopt::NO_ARGUMENT, "Show this help"),
        array("l", "loglevel", Getopt::REQUIRED_ARGUMENT, "Define the log level"),
        array("c", "config", Getopt::REQUIRED_ARGUMENT, "Location of the configuration file"),
        array("v", "version", Getopt::NO_ARGUMENT, "Version information")
    )
);

try {
    $getopt->parse();
} catch (\UnexpectedValueException $ex) {
    displayHeader();
    echo $ex->getMessage()."\r\n";
    exit(1);
}

if ($getopt->getOption("help")) {
    displayHeader();
    $getopt->showHelp();
    echo "\n";
    exit;
} elseif ($getopt->getOption("version")) {
    echo "ContentBuild v".CONTENT_BUILD_VERSION."\n";
    exit;
}

displayHeader();

$logLevel = $getopt->getOption("loglevel");
if (isset($logLevel)) {
    \RPI\Utilities\ContentBuild\Lib\Exception\Handler::setLogLevel($logLevel);
}

$configurationFile = $getopt->getOption("config");
if (!isset($configurationFile)) {
    if (file_exists(getcwd()."/"."ui.build.xml")) {
        $configurationFile = getcwd()."/"."ui.build.xml";
    }
} else {
    if (!file_exists($configurationFile)) {
        $configurationFile = getcwd()."/".$configurationFile;
    }
}

if (!file_exists($configurationFile)) {
    echo "Configuration file '$configurationFile' not found\n\n";
    $getopt->showHelp();
    echo "\n";
} else {
    $project = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project($configurationFile);

    $processor = new \RPI\Utilities\ContentBuild\Lib\Processor($project);

    $build = new \RPI\Utilities\ContentBuild\Lib\Build($project, $processor);
    
    $build->run();
}
