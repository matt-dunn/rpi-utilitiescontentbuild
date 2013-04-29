#!/bin/bash

BASEDIR=$(cd $(dirname $0);echo $PWD)

mkdir -p $BASEDIR/../bin/

php -dphar.readonly=0 $BASEDIR/empir-1.0.0/empir make $BASEDIR/../bin/contentbuild.phar Src/RPI/Utilities/ContentBuild/Bootstrap.php $BASEDIR/../ --exclude="composer.*|*vendor*|*.git*|*/.git*|*/.git/*|.DS_Store|bin/*|Make/*|*/test/*" --include="vendor/rpi/foundation/Src/*|vendor/composer/*|vendor/autoload.php|vendor|vendor/ulrichsg/getopt-php/src*|*/yuicompressor/build/*" --allowupdate=1

mv $BASEDIR/../bin/contentbuild.phar $BASEDIR/../bin/contentbuild
chmod +x $BASEDIR/../bin/contentbuild

cp $BASEDIR/../bin/contentbuild /usr/local/bin/contentbuild
