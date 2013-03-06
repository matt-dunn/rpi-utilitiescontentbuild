<?php

namespace RPI\Utilities\ContentBuild\Lib\Exception;

/**
 * Error handling
 * @author Matt Dunn
 */
class Handler
{
    public static $errorCount = 0;
    private static $warnings = array();
    private static $startTime;
    private static $logLevel = 3;
    private static $ident = null;
    public static $displayShutdownInformation = false;
    
    private static $processCharacter = null;
    
    private function __construct()
    {
    }

    protected static $unloggedStrictErrorCount = 0;

    public static function setLogLevel($level)
    {
        self::$logLevel = $level;
    }

    public static function getLogLevel()
    {
        return self::$logLevel;
    }

    public static function shutdown()
    {
        $exitStatus = 0;
        
        $error = error_get_last();
        if (isset($error)) {
            self::log("ERROR (shutdown): ".$error["message"]." - ".$error["file"]."#".$error["line"], LOG_ERR, true);
        } elseif (self::$displayShutdownInformation) {
            if (self::$errorCount > 0) {
                self::log("********** SCRIPT FAILED - WITH ".self::$errorCount." ERRORS/WARNINGS:", LOG_ERR, true);
                $exitStatus = 1;
            } elseif (count(self::$warnings) > 0) {
                self::log(
                    "********** SCRIPT SUCCESSFUL - completed with ".count(self::$warnings)." WARNINGS:\r\n",
                    LOG_INFO
                );
                $exitStatus = 2;
            } else {
                self::log("SCRIPT SUCCESSFUL", LOG_INFO);
            }

            for ($i = 0; $i < count(self::$warnings); $i++) {
                self::log(self::$warnings[$i], LOG_ERR, true);
            }

            self::log(
                "Duration: ".number_format(microtime(true) - self::$startTime, 2)." seconds\r\n",
                LOG_INFO,
                false,
                true
            );
        }

        exit($exitStatus);
    }

    public static function log($msg, $severity = LOG_INFO, $dislayError = false, $lastMessage = false)
    {
        $logLevelType = null;
        $logLevel = 0;
        switch ($severity) {
            case LOG_CRIT:
                self::$errorCount++;
                $logLevelType = "CRITICAL";
                $logLevel = 1;
                break;
            case LOG_ERR:
                self::$errorCount++;
                $logLevelType = "ERROR";
                $logLevel = 1;
                break;
            case LOG_WARNING:
                self::$errorCount++;
                $logLevelType = "WARNING";
                $logLevel = 2;
                break;
            case LOG_INFO:
                $logLevelType = "INFO";
                $logLevel = 3;
                break;
            case LOG_NOTICE:
                $logLevelType = "NOTICE";
                $logLevel = 4;
                break;
            case LOG_DEBUG:
                $logLevelType = "DEBUG";
                $logLevel = 5;
                break;
        }

        $fullMessage = (isset($logLevelType) ? "[".$logLevelType."] " : "").$msg;

        if (self::isCli()) {
            if (self::$displayShutdownInformation
                && !$dislayError
                && ($severity == LOG_CRIT || $severity == LOG_WARNING || $severity == LOG_ERR)) {
                array_push(self::$warnings, $msg);
            } elseif ($dislayError || (self::$logLevel == 0 || $logLevel <= self::$logLevel)) {
                $t = microtime(true);
                $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
                $timestamp = new \DateTime(date("Y-m-d H:i:s.".$micro, $t));
                
                if (isset(self::$processCharacter)) {
                    self::$processCharacter = null;
                    echo "\033[2D";
                }
                
                fwrite(STDOUT, $timestamp->format("Y-m-d H:i:s").": ".$fullMessage."\r\n");
            } else {
                if (!isset(self::$processCharacter)) {
                    self::$processCharacter = "|";
                }
                
                echo "\033[2D";
                if (!$lastMessage) {
                    echo self::$processCharacter." ";

                    switch (self::$processCharacter) {
                        case "|":
                            self::$processCharacter = "/";
                            break;
                        case "/":
                            self::$processCharacter = "-";
                            break;
                        case "-":
                            self::$processCharacter = "\\";
                            break;
                        case "\\":
                            self::$processCharacter = "|";
                            break;
                    }
                }
            }
        } elseif ($severity == LOG_CRIT || $severity == LOG_WARNING || $severity == LOG_ERR) {
            openlog(self::$ident." (php)", LOG_NDELAY, LOG_USER);
            syslog($severity, $msg);
            closelog();
        }
    }



    /**
     * Handle unhandled exceptions
     * @param \Exception $exception
     */
    public static function handleExceptions(\Exception $exception)
    {
        self::log($exception, LOG_ERR);
    }

    /**
     * Handle PHP errors and warnings
     * @param integer $errNo
     * @param string  $errStr
     * @param string  $errFile
     * @param integer $errLine
     */
    public static function handle($errNo, $errStr, $errFile, $errLine)
    {
        switch ($errNo) {
            case E_STRICT:
            case E_DEPRECATED:
                if (strpos($errFile, "PEAR") !== false) { // Don't log any PEAR errors
                    self::$unloggedStrictErrorCount++;
                } else {
                    self::log("STRICT/DEPRECATED WARNING: [$errNo] $errStr - $errFile#$errLine", LOG_ERR);
                }
                break;
            default:
                throw new \ErrorException($errStr, 0, $errNo, $errFile, $errLine);
        }
    }

    /**
     * Initialise error handlers
     */
    public static function set($ident)
    {
        self::$ident = $ident;
        
        ini_set("html_errors", 0);
        ini_set("display_errors", 0);
        // TODO: force error logging - always override the ini config?
        ini_set("log_errors", 1);

        // Report ALL errors
        error_reporting(-1);

        set_exception_handler(array(__CLASS__ , "handleExceptions"));
        set_error_handler(array(__CLASS__ , "handle"), ini_get("error_reporting"));
        register_shutdown_function(array(__CLASS__, "shutdown"));
        
        self::$startTime = microtime(true);
    }
    
    private static function isCli()
    {
        if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
            return true;
        } else {
            return false;
        }
    }
}
