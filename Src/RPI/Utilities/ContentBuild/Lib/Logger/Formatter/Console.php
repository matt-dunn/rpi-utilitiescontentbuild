<?php

namespace RPI\Utilities\ContentBuild\Lib\Logger\Formatter;

class Console implements \RPI\Foundation\App\Logger\Formatter\IFormatter
{
    public function format(\RPI\Foundation\App\Logger\ILogger $logger, array $record)
    {
        $message = $record["message"];

        $logLevel = $logger->getLogLevel();
        if (isset($record["trace"]) && (!isset($logLevel) || in_array(\Psr\Log\LogLevel::DEBUG, $logLevel))) {
            $message .= "\n\n".print_r($record["trace"], true);
        }
        
        return $message;
    }
}
