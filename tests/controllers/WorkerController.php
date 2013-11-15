<?php


namespace app\controllers;

use sergebezborodov\beanstalk\Controller;

/**
 * Test controller with action mapped to tubes
 * @package tests\controllers
 */
class WorkerController extends Controller
{
    public function actionTest($payload)
    {
        echo $payload."\n";
        return null;
    }

    public function actionTubeSecond()
    {

    }
}