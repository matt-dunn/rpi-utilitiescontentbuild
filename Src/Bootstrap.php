<?php

require_once __DIR__."/Constants.php";
require_once __DIR__."/Functions.php";

require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/Autoload.php";

\RPI\Utilities\ContentBuild\Autoload::init();

\RPI\Utilities\ContentBuild\Lib\Exception\Handler::set("ContentBuild");

$commandClasses = \RPI\Utilities\ContentBuild\Lib\Helpers\FileUtils::find(__DIR__."/Command", "*.php");

$availableCommands = array();
foreach(array_keys($commandClasses) as $commandClass) {
    $className = \RPI\Utilities\ContentBuild\Autoload::getClassName($commandClass);
    $availableCommands[] = new $className();
}

$commands = new \RPI\Utilities\ContentBuild\Command($availableCommands);
$options = $commands->parse();

if ($options !== false && isset($options["configurationFile"])) {
    displayHeader();

    $project = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project($options["configurationFile"]);

    $processor = new \RPI\Utilities\ContentBuild\Lib\Processor($project);
    
    $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver($project);

    $build = new \RPI\Utilities\ContentBuild\Lib\Build($project, $processor, $resolver);
    
    $build->run();
}
