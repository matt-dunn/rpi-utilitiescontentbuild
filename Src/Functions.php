<?php

function displayHeader()
{
    $header = <<<EOT
       ___         _           _   ___      _ _    _ 
      / __|___ _ _| |_ ___ _ _| |_| _ )_  _(_) |__| |
     | (__/ _ \ ' \  _/ -_) ' \  _| _ \ || | | / _` |
      \___\___/_||_\__\___|_||_\__|___/\_,_|_|_\__,_|


EOT;

    echo $header;
    echo "v".CONTENT_BUILD_VERSION."\n\n";
}
