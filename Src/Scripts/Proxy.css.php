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

if (class_exists("\RPI\Utilities\Build\Content\scripts\ProcessCSS")) {
    $seg = sem_get("12131313121");
    sem_acquire($seg);

    echo \RPI\Utilities\Build\Content\scripts\ProcessCSS::process(
        $file,
        file_get_contents($file),
        dirname(__FILE__)."/variables"
    );
    \RPI\Utilities\Build\Content\scripts\ProcessCSS::saveVariables(dirname(__FILE__)."/variables");

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
