<?php

function displayHeader(\Psr\Log\LoggerInterface $logger)
{
    $header = <<<EOT
       ___         _           _   ___      _ _    _ 
      / __|___ _ _| |_ ___ _ _| |_| _ )_  _(_) |__| |
     | (__/ _ \ ' \  _/ -_) ' \  _| _ \ || | | / _` |
      \___\___/_||_\__\___|_||_\__|___/\_,_|_|_\__,_|


EOT;

    $logger->info($header);
    $logger->info("v".CONTENT_BUILD_VERSION);
}
