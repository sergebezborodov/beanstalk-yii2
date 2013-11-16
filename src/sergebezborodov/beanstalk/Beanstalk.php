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
     * @var int default job time to execute
     */
    public $defaultJobTtr = 3600;

    private $_currTube;


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

    /**
     * Adds job to query
     *
     * @param string $tube tube name
     * @param string $data data to worker
     * @param int|null $ttl time to execute this job
     * @param int $priority Jobs with smaller priority values will be scheduled
     *              before jobs with larger priorities. The most urgent priority is 0;
     *              the least urgent priority is 4294967295.
     * @param int $delay delay before insert job into work query
     * @return bool
     */
    public function addJob($tube, $data, $ttl = null, $priority = 0, $delay = 0)
    {
        $ttl = $ttl ?: $this->defaultJobTtr;
        if ($tube != $this->_currTube) {
            $this->_client->choose($tube);
            $this->_currTube = $tube;
        }

        return $this->_client->put($priority, $delay, $ttl, $data);
    }

}