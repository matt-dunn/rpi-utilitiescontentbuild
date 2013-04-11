#!/bin/bash

BASEDIR=$(cd $(dirname $0);echo $PWD)

php -dphar.readonly=0 $BASEDIR/empir-1.0.0/empir make $BASEDIR/../Build/contentbuild.phar Src/Bootstrap.php $BASEDIR/../ --exclude="*phing*|*.git*|*/.git*|*/.git/*|.DS_Store|Build/*|composer.*|*/test/*|*/yuicompressor/*|*/composer.json|*/Makefile|*/*README*|*phpunit*|*symfony*" --include="*/yuicompressor/build/*" --allowupdate=1

# cp $BASEDIR/contentbuild.phar /usr/local/bin/contentbuild
# chmod +x /usr/local/bin/contentbuild
