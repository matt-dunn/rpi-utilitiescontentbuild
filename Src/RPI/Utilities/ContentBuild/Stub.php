#!/usr/bin/env php -dphar.readonly=0 -ddetect_unicode=1
<?php

Phar::mapPhar('contentbuild.phar');
require 'phar://contentbuild.phar/Src/RPI/Utilities/ContentBuild/Bootstrap.php';

__HALT_COMPILER();
