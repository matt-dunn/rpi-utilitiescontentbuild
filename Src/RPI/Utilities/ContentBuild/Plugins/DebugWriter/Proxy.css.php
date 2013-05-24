<?php
header("Content-type: text/css");
header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: pre-check=0, post-check=0, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (parse_url($_GET["f"], PHP_URL_SCHEME) == "http") {
    $file = $_GET["f"];
} else {
    $file = ($_SERVER["DOCUMENT_ROOT"].$_GET["f"]);
}

if (isset($GLOBALS["configuration-file"])) {
    if (!file_exists($GLOBALS["autoloader"])) {
        throw new Exception("Cannot locate autoloader '".$GLOBALS["autoloader"]."'");
    }

    require_once $GLOBALS["autoloader"];

    $logger = new \RPI\Foundation\App\Logger(
        new \RPI\Foundation\App\Logger\Handler\Syslog()
    );

    $logger->setLogLevel(
        array(
            \Psr\Log\LogLevel::CRITICAL,
            \Psr\Log\LogLevel::ERROR,
            \Psr\Log\LogLevel::WARNING
        )
    );

    new \RPI\Foundation\Exception\Handler($logger);

    $configuration = new \RPI\Utilities\ContentBuild\Lib\Configuration(
        $logger,
        $GLOBALS["configuration-file"]
    );

    $processor = new \RPI\Utilities\ContentBuild\Lib\Processor($logger, $configuration->project, true);

    $resolver = new \RPI\Utilities\ContentBuild\Lib\UriResolver($logger, $configuration->project);

    if (!isset($_GET["n"]) || !isset($_GET["t"])) {
        throw new \RPI\Foundation\Exceptions\RuntimeException("Missing querystring parameters 'n' and/or 't'");
    }
    $name = $_GET["n"];
    $type = $_GET["t"];
    
    $currentBuild = null;
    
    foreach ($configuration->project->builds as $build) {
        if ($build->name == $name && $build->type == $type) {
            $currentBuild = $build;
            break;
        }
    }
    
    if (!isset($currentBuild)) {
        throw new \RPI\Foundation\Exceptions\RuntimeException("Unable to locate build");
    }
    
    echo $processor->process($currentBuild, $resolver, $file, file_get_contents($file));
} else {
    echo file_get_contents($file);
}
