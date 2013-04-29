<?php

require_once __DIR__."/Constants.php";
require_once __DIR__."/Functions.php";

require_once __DIR__."/../../../../vendor/autoload.php";

$logger = new \RPI\Foundation\App\Logger(
    new \RPI\Utilities\ContentBuild\Lib\Logger\Handler\Stdout(),
    new \RPI\Utilities\ContentBuild\Lib\Logger\Formatter\Console()
);

$logger->setLogLevel(
    array(
        \Psr\Log\LogLevel::INFO,
        \Psr\Log\LogLevel::ERROR
    )
);

new \RPI\Foundation\Exception\Handler($logger);

$commands = new \RPI\Utilities\ContentBuild\Command(
    $logger,
    array(
        new \RPI\Utilities\ContentBuild\Command\Version(),
        new \RPI\Utilities\ContentBuild\Command\LogLevel(),
        new \RPI\Utilities\ContentBuild\Command\Help(),
        new \RPI\Utilities\ContentBuild\Command\Extensions(),
        new \RPI\Utilities\ContentBuild\Command\Options\IncludeDebug(),
        new \RPI\Utilities\ContentBuild\Command\Config()
    )
);
$options = $commands->parse();

if ($options !== false && isset($options["configurationFile"])) {
    displayHeader($logger);

    $project = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project($logger, $options["configurationFile"]);

    $processor = new \RPI\Utilities\ContentBuild\Lib\Processor($logger, $project);
    
    $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver($logger, $project);

    $build = new \RPI\Utilities\ContentBuild\Lib\Build($logger, $project, $processor, $resolver, $options);
    
    $build->run();
}
