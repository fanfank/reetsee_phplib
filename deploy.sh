#!/bin/bash

DEPLOY_PATH="$PHP_PATH/phplib"

OUTPUT_DIR="output"
PRODUCT="reetsee_phplib"
OUTPUT_FILE="$PRODUCT.tar.gz"

mkdir -p $OUTPUT_DIR
rm -rf $OUTPUT_DIR/*
cp -rf Reetsee reetsee.php $OUTPUT_DIR/

cd $OUTPUT_DIR
find ./ -name .git -exec rm -rf {} \;
tar zcvf $OUTPUT_FILE ./*

rm -rf Reetsee reetsee.php

cp $OUTPUT_FILE $DEPLOY_PATH/
cd $DEPLOY_PATH
tar zxvf $OUTPUT_FILE
rm -rf $OUTPUT_FILE
