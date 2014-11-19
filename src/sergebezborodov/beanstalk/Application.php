<?php

namespace sergebezborodov\beanstalk;

use yii\helpers\Console;

/**
 * Application for beanstalk worker
 * @package sergebezborodov\beanstalk
 */
class Application extends \yii\console\Application
{
    const EXIT_PARAM = '--exit-after-complete';

    public $enableCoreCommands = false;

    /**
     * Handle system signals
     * works when pcntl enabled
     *
     * @var bool
     */
    public $handleSignals = true;


    /**
     * Exit worker when handle database exception
     *
     * @var bool
     */
    public $exitOnDbException = false;

    /**
     * Flag when script need to be terminated
     *
     * @var bool
     */
    private $_needTerminate = false;

    /**
     * Flat when task is currently working
     *
     * @var bool
     */
    private $_isWorkingNow = false;

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
        $beanstalk = $this->get('beanstalk');
        /** @var Router $router */
        $router = $this->get('router');

        $exitAfterComplete = false;
        try {

            $params = $request->getParams();
            if ($pos = array_search(self::EXIT_PARAM, $params)) {
                $exitAfterComplete = true;
                unset($params[$pos]);
            }
            $tubes = $params;

            if ($tubes) {
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
                $this->unregisterSignalHandler();
                $job = $beanstalk->reserve();
                $this->registerSignalHandler();

                if (!$onlyOneTube) {
                    $info = $beanstalk->statsJob($job['id']);
                    $tube = $info['tube'];
                    $route = $router->getRoute($tube);
                }

                try {
                    $this->_isWorkingNow = true;

                    $actResp = $this->runAction($route, [$job['body']]);
                    if ($actResp) {
                        $beanstalk->delete($job['id']);
                    } else {
                        $beanstalk->bury($job['id'], 0);
                    }
                    $this->_isWorkingNow = false;

                    $this->signalDispatch();
                    if ($this->_needTerminate || $exitAfterComplete) {
                        $this->endApp();
                    }
                } catch (\Exception $e) {
                    fwrite(STDERR, Console::ansiFormat($e."\n", [Console::FG_RED]));
                    $beanstalk->bury($job['id'], 0);

                    if ($e instanceof \yii\db\Exception && $this->exitOnDbException) {
                        $this->_needTerminate = true;
                    }

                    $this->_isWorkingNow = false;
                    if ($this->_needTerminate) {
                        $this->endApp();
                    }
                }
            }
        } catch (\Exception $e) {
            $response->exitStatus = 1;
            fwrite(STDERR, Console::ansiFormat($e."\n", [Console::FG_RED]));
        }

        return $response;
    }

    protected function endApp()
    {
        exit;
    }

    private function registerSignalHandler()
    {
        if (!extension_loaded('pcntl')) {
            return;
        }

        pcntl_signal(SIGINT, function ($signal) {
            fwrite(STDOUT, Console::ansiFormat("Received SIGINT will exit soon\n", [Console::FG_RED]));
            if ($this->_isWorkingNow) {
                $this->_needTerminate = true;
            } else {
                $this->endApp();
            }
        });
        declare(ticks = 1);
        register_tick_function([$this, 'signalDispatch']);
    }

    private function unregisterSignalHandler()
    {
        if (!extension_loaded('pcntl')) {
            return;
        }
        pcntl_signal(SIGINT, SIG_DFL);
        unregister_tick_function([$this, 'signalDispatch']);
    }

    public function signalDispatch()
    {
        if (!extension_loaded('pcntl')) {
            return;
        }
        pcntl_signal_dispatch();
    }
}