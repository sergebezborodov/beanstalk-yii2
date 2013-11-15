<?php


namespace sergebezborodov\beanstalk;

use yii\base\Component;

/**
 * Beanstalk client
 *
 * @package sergebezborodov\beanstalk
 */
class Beanstalk extends Component
{
    /**
     * @var string
     */
    public $host = '127.0.0.1';

    /**
     * @var int
     */
    public $port = 11300;

    /**
     * @var int
     */
    public $timeout = 1;

    /**
     * @var bool
     */
    public $persistent = true;


    /**
     * @var \Socket_Beanstalk
     */
    protected $_client;

    public function init()
    {
        parent::init();

        $this->_client = new \Socket_Beanstalk([
            'persistent' => $this->persistent,
            'host'       => $this->host,
            'port'       => $this->port,
            'timeout'    => $this->timeout,
        ]);

        $this->_client->connect();
    }

    /**
     * Translates all function calls to client
     *
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        return call_user_func_array([$this->_client, $name], $params);
    }

    /**
     * @return bool
     */
    public function getIsConnected()
    {
        return $this->_client->connected;
    }
}