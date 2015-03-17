#!/usr/bin/env php
<?php
define('ROOT', realpath(__DIR__.DIRECTORY_SEPARATOR.'..'));
require_once ROOT.'/vendor/autoload.php';
require ROOT. '/vendor/yiisoft/yii2/Yii.php';

$app = new \yii\console\Application(require "config.php");
$app->run();