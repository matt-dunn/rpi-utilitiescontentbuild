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
    $seg = sem_get("12131313121");
    sem_acquire($seg);

    require_once $GLOBALS["autoloader"];

    \RPI\Utilities\ContentBuild\Autoload::init();

    \RPI\Utilities\ContentBuild\Lib\Exception\Handler::set("ContentBuild");

    $project = new \RPI\Utilities\ContentBuild\Lib\Configuration\Xml\Project($GLOBALS["configuration-file"]);

    $processor = new \RPI\Utilities\ContentBuild\Lib\Processor($project);

    echo $processor->process($file, file_get_contents($file));

    sem_release($seg);
} else {
    echo file_get_contents($file);
}

$error = error_get_last();
if (isset($error)) {
    openlog("ProcessCSS (php)", LOG_NDELAY, LOG_USER);
    syslog(LOG_ERR, $error["message"]);
    closelog();
}
