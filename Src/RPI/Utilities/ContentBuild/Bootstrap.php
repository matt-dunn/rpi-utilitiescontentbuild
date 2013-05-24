<?php

require_once __DIR__."/Constants.php";
require_once __DIR__."/Functions.php";

if (file_exists(__DIR__."/../../../../vendor/autoload.php")) {
    // Local package autoload
    require_once __DIR__."/../../../../vendor/autoload.php";
} else {
    // Global autoload
    require_once __DIR__."/../../../../../../autoload.php";
}

$logger = new \RPI\Foundation\App\Logger(
    new \RPI\Console\Logger\Handler\Stdout(),
    new \RPI\Console\Logger\Formatter\Console()
);

$logger->setLogLevel(
    array(
        \Psr\Log\LogLevel::INFO,
        \Psr\Log\LogLevel::ERROR
    )
);

new \RPI\Console\Exception\Handler($logger);

$commands = new \RPI\Console\Command(
    $logger,
    array(
        new \RPI\Utilities\ContentBuild\Command\LogLevel(),
        new \RPI\Utilities\ContentBuild\Command\Version(),
        new \RPI\Utilities\ContentBuild\Command\Help(),
        new \RPI\Utilities\ContentBuild\Command\Support(),
        new \RPI\Utilities\ContentBuild\Command\Options\NoDev(),
        new \RPI\Utilities\ContentBuild\Command\ValidateDependency(),
        new \RPI\Utilities\ContentBuild\Command\Config(),
        new \RPI\Utilities\ContentBuild\Command\Validate()
    )
);
$options = $commands->parse();

if ($options !== false && isset($options["configurationFile"])) {
    displayHeader($logger);

    $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
        $logger,
        $options["configurationFile"],
        $options
    );

    $processor = new \RPI\Utilities\ContentBuild\Lib\Processor($logger, $configuration->project);
    
    $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver($logger, $processor, $configuration->project);

    $build = new \RPI\Utilities\ContentBuild\Lib\Build(
        $logger,
        $configuration->project,
        $processor,
        $resolver,
        $options
    );
    
    $build->run();
}
