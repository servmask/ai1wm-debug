#!/bin/bash

sudo apt-get install -y zip

rm -rf dist
mkdir -p dist/$PLUGIN_NAME
cp -R {lib,$PLUGIN_NAME.php,constants.php,loader.php,functions.php,uninstall.php,USER-GUIDE.md,DEBUG-GUIDE.md,README.md} dist/$PLUGIN_NAME

sed -i s/Version:\ develop/Version:\ $VERSION/ dist/$PLUGIN_NAME/$PLUGIN_NAME.php
sed -i "s/define( 'AI1WM_DEBUG_VERSION', 'develop' )/define( 'AI1WM_DEBUG_VERSION', '$VERSION' )/" dist/$PLUGIN_NAME/constants.php

cd dist
zip -vrm9 $PLUGIN_NAME-$VERSION.zip ./$PLUGIN_NAME

echo "ASSET_PATH=dist/$PLUGIN_NAME-$VERSION.zip" >> $GITHUB_OUTPUT
