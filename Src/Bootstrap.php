<?php

require_once __DIR__."/Constants.php";
require_once __DIR__."/Functions.php";

require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/Autoload.php";

\RPI\Utilities\ContentBuild\Autoload::init();

\RPI\Utilities\ContentBuild\Lib\Exception\Handler::set("ContentBuild");

$commands = new \RPI\Utilities\ContentBuild\Command(
    array(
        new \RPI\Utilities\ContentBuild\Command\Help(),
        new \RPI\Utilities\ContentBuild\Command\Version(),
        new \RPI\Utilities\ContentBuild\Command\LogLevel(),
        new \RPI\Utilities\ContentBuild\Command\Config()
    )
);
$options = $commands->parse();

if ($options !== false && isset($options["configurationFile"])) {
    displayHeader();

    $project = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project($options["configurationFile"]);

    $processor = new \RPI\Utilities\ContentBuild\Lib\Processor($project);

    $build = new \RPI\Utilities\ContentBuild\Lib\Build($project, $processor);
    
    $build->run();
}
