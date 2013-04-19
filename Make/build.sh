#!/bin/bash

BASEDIR=$(cd $(dirname $0);echo $PWD)

mkdir -p $BASEDIR/../bin/

php -dphar.readonly=0 $BASEDIR/empir-1.0.0/empir make $BASEDIR/../bin/contentbuild.phar Src/Bootstrap.php $BASEDIR/../ --exclude="*phing*|*.git*|*/.git*|*/.git/*|.DS_Store|Build/*|composer.*|*/test/*|*/yuicompressor/*|*/composer.json|*/Makefile|*/*README*|*phpunit*|*symfony*" --include="*/yuicompressor/build/*" --allowupdate=1

mv $BASEDIR/../bin/contentbuild.phar $BASEDIR/../bin/contentbuild
chmod +x $BASEDIR/../bin/contentbuild

cp $BASEDIR/../bin/contentbuild /usr/local/bin/contentbuild
