<?php

namespace tests;

use sergebezborodov\beanstalk\Beanstalk;

class BeanstalkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Beanstalk
     */
    protected function beans()
    {
        return \Yii::$app->beanstalk;
    }

    public function testPutJob()
    {
        $id = $this->beans()->addJob('test-tube', 'test data for test tube', 1234, 321, 1);
        $this->assertNotEmpty($id);

        $stat = $this->beans()->statsJob($id);

        $this->assertEquals('test-tube', $stat['tube']);
        $this->assertEquals('delayed', $stat['state']);
        $this->assertEquals(321, $stat['pri']);
        $this->assertEquals(1, $stat['delay']);
        $this->assertEquals(1234, $stat['ttr']);

        sleep(1);

        $stat = $this->beans()->statsJob($id);
        $this->assertEquals('ready', $stat['state']);
    }
}