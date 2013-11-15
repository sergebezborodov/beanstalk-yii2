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
        $this->beans()->choose('test');
        $id = $this->beans()->put(0, 1, 10000, 'test');
        $this->assertNotEmpty($id);
    }
}