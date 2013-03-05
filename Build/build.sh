#!/bin/bash

BASEDIR=$(cd $(dirname $0);echo $PWD)

php $BASEDIR/../../empir-1.0.0/empir make $BASEDIR/contentbuild.phar Src/Bootstrap.php $BASEDIR/../ --exclude="*/.git*|*/.git/*|.DS_Store|Build/*|composer.*|*/test/*|*/yuicompressor/*|*/composer.json|*/Makefile|*/*README*|*phpunit*" --include="*/yuicompressor/build/*"

cp $BASEDIR/contentbuild.phar /usr/local/bin/contentbuild
chmod +x /usr/local/bin/contentbuild
