<?php  namespace app\controllers; 

use yii\console\Controller;
use sergebezborodov\beanstalk\Beanstalk;

class TestController extends Controller
{
    public function actionIndex()
    {
        /** @var Beanstalk $beanstalk */
        $beanstalk = \Yii::$app->beanstalk;

        $beanstalk->addJob('test', serialize("Hello, World"));
    }
}