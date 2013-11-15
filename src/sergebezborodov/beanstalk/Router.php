<?php


namespace sergebezborodov\beanstalk;

use yii\base\Component;

/**
 * Worker handler router
 *
 * @package sergebezborodov\beanstalk
 */
class Router extends Component
{
    /**
     * @var array
     */
    private $_routes;

    /**
     * Sets routes for beanstalk tubes
     * routes format:
     *  tube_name => 'controller/action'
     *
     * @param array $routes
     */
    public function setRoutes($routes)
    {
        $this->_routes = [];
        foreach ($routes as $tube => $route) {
            if (!$tube) {
                throw new Exception('Tube name must be exist');
            }
            if (count(explode('/', $route)) != 2) {
                throw new Exception("Incorrect route defined for tube '{$tube}'");
            }
            $this->_routes[$tube] = $route;
        }
    }

    /**
     * @return array routes, format tube => [controller, action]
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     * Find and return route for tube
     *
     * @param string $tube
     * @return string
     */
    public function getRoute($tube)
    {
        if (empty($this->_routes[$tube])) {
            throw new Exception("There are no route for tube '{$tube}''");
        }

        return $this->_routes[$tube];
    }

    /**
     * @return bool true if component has defined routes
     */
    public function getIsHasRoutes()
    {
        return (bool)count($this->_routes);
    }
}