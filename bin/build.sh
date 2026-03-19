#!/bin/bash
set -e

rm -rf dist; mkdir -p dist/servmask-debug
cp -R {lib,servmask-debug.php,constants.php,loader.php,functions.php,uninstall.php,USER-GUIDE.md,DEBUG-GUIDE.md,README.md} dist/servmask-debug
version=`git describe`

if [ "$(uname)" == "Darwin" ]; then
	sed -i '' s/Version:\ develop/Version:\ $version/ dist/servmask-debug/servmask-debug.php
	sed -i '' "s/define( 'AI1WM_DEBUG_VERSION', 'develop' )/define( 'AI1WM_DEBUG_VERSION', '$version' )/" dist/servmask-debug/constants.php
else
	sed -i s/Version:\ develop/Version:\ $version/ dist/servmask-debug/servmask-debug.php
	sed -i "s/define( 'AI1WM_DEBUG_VERSION', 'develop' )/define( 'AI1WM_DEBUG_VERSION', '$version' )/" dist/servmask-debug/constants.php
fi

cd dist; zip -vrm9 servmask-debug.zip ./servmask-debug
