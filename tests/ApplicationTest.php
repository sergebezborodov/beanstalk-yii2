<?php


namespace tests;

/**
 * Test for application
 *
 * @package tests
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $this->assertInstanceOf('\sergebezborodov\beanstalk\Application', \Yii::$app);
    }

    public function testHashComponents()
    {
        $this->assertInstanceOf('\sergebezborodov\beanstalk\Beanstalk', \Yii::$app->beanstalk);
    }

    public function testConnected()
    {
        if (!\Yii::$app->beanstalk->getIsConnected()) {
            $this->fail('You have to run beanstalk server');
        }
    }


}