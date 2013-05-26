<?php
// Force session and output buffering before PHPUnit and any unit tests to avoid headers already sent exception
//session_start();
ob_start();

if (file_exists(__DIR__."/../vendor/autoload.php")) {
    $autoload = require __DIR__."/../vendor/autoload.php";
} else {
    $autoload = require __DIR__."/../../../autoload.php";
}
$autoload->add("RPI\\Utilities\\ContentBuild\\Test", __DIR__."/Src");

// ================================================================================================================
// Configure the tests:

new \RPI\Foundation\Exception\Handler(
    new \RPI\Foundation\App\Logger(
        new \RPI\Foundation\App\Logger\Handler\Syslog()
    )
);

mb_internal_encoding("UTF-8");
