<?php

namespace sergebezborodov\beanstalk;

use yii\helpers\Console;

/**
 * Application for beanstalk worker
 * @package sergebezborodov\beanstalk
 */
class Application extends \yii\console\Application
{
    public $enableCoreCommands = false;

    /**
     * @inheritdoc
     */
    public function registerCoreComponents()
    {
        parent::registerCoreComponents();

        $this->setComponents([
            'router'    => ['class' => '\sergebezborodov\beanstalk\Router'],
            'beanstalk' => ['class' => '\sergebezborodov\beanstalk\Beanstalk'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function handleRequest($request)
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        /** @var Beanstalk $info */
        $beanstalk = $this->getComponent('beanstalk');
        /** @var Router $router */
        $router = $this->getComponent('router');

        try {
            if ($tubes = $request->getParams()) {
                foreach ($tubes as $tube) {
                    if (!$beanstalk->watch($tube)) {
                        throw new Exception("Unable to watch {$tube}");
                    }
                }
            } else {
                $tubes = $beanstalk->listTubes();
            }

            $onlyOneTube = count($tubes) == 1;
            $tube = reset($tubes);
            $route = $router->getRoute($tube);

            while (true) {
                $job = $beanstalk->reserve();

                if (!$onlyOneTube) {
                    $info = $beanstalk->statsJob($job['id']);
                    $tube = $info['tube'];
                    $route = $router->getRoute($tube);
                }

                try {
                    $actResp = $this->runAction($route, [$job['body']]);
                    if ($actResp) {
                        $beanstalk->delete($job['id']);
                    } else {
                        $beanstalk->bury($job['id'], 0);
                    }
                } catch (\Exception $e) {
                    fwrite(STDERR, Console::ansiFormat($e->getMessage()."\n", [Console::FG_RED]));
                    $beanstalk->bury($job['id'], 0);
                }
            }
        } catch (\Exception $e) {
            $response->exitStatus = 1;
            fwrite(STDERR, Console::ansiFormat($e->getMessage()."\n", [Console::FG_RED]));
        }

        return $response;
    }
}