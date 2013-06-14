#!/usr/bin/env php
<?php

Phar::mapPhar('contentbuild.phar');
require 'phar://contentbuild.phar/Src/RPI/Utilities/ContentBuild/Bootstrap.php';

__halt_compiler();
