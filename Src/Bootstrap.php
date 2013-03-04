<?php

require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/Autoload.php";

\RPI\Utilities\ContentBuild\Autoload::init();

date_default_timezone_set("Europe/London");
\RPI\Utilities\ContentBuild\Lib\Exception\Handler::set("ContentBuild");        

$processor = new \RPI\Utilities\ContentBuild\Lib\Processor();
$processor->add(
    new \RPI\Utilities\ContentBuild\Processors\Sprites()
);

$build = new \RPI\Utilities\ContentBuild\Lib\Build($processor);
$build->run();


echo "END";
