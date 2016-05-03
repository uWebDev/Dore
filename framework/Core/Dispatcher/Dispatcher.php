<?php

namespace Dore\Core\Dispatcher;

use Dore\Core\Foundation\Controller;
use Dore\Core\Foundation\App;
use Dore\Core\Exception\EnvironmentExceptions\NotExistsException;
use Dore\Core\Exception\SemanticExceptions\PageNotFoundException;

/**
 * Class Dispatcher
 * @package Dore\Core\Dispatcher
 */
class Dispatcher
{

    /**
     * @var App
     */
    protected $app;

    /**
     * @var array[Callable] Middleware to be run before only this route instance
     */
    protected $middleware = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return  string
     */
    public function getController()
    {
        return $this->data['target']['controller'];
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->data['target']['action'];
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->data['params'];
    }

    /**
     * @return type
     */
    public function getNameRoute()
    {
        return isset($this->data['name']) ? $this->data['name'] : null;
    }

    /**
     * Get middleware
     * @return array[Callable]
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @param App $app
     */
    public function dispatch(App $app)
    {
        $this->app = $app;
        $this->match($app['request']->getPathInfo(), $app['request']->getMethod());
        foreach ($this->middleware as $mw) {
            call_user_func($mw, $app);
        }
        $this->controller()->run($this->getAction(), $this->getParams());
    }

    /**
     * Set middleware
     * This method allows middleware to be assigned to a specific Route.
     * If the method argument `is_callable` (including callable arrays!),
     * we directly append the argument to `$this->middleware`. Else, we
     * assume the argument is an array of callables and merge the array
     * with `$this->middleware`.  Each middleware is checked for is_callable()
     * and an InvalidArgumentException is thrown immediately if it isn't.
     *
     * @param  Callable|array[Callable]
     *
     * @throws NotExistsException If argument is not callable or not an array of callables.
     */
    private function setMiddleware($middleware)
    {
        if (is_callable($middleware)) {
            $this->middleware[] = $middleware;
        } elseif (is_array($middleware)) {
            foreach ($middleware as $callable) {
                if (!is_callable($callable)) {
                    throw new NotExistsException('All Route middleware must be callable');
                }
            }
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            throw new NotExistsException('Route middleware must be callable or an array of callables');
        }
    }

    /**
     * @param string $uri
     * @param string $method
     *
     * @throws PageNotFoundException
     */
    private function match($uri, $method)
    {
        $this->data = $this->app['router']->match(rtrim($uri, '/') . '/', $method);

        if (!is_array($this->data)) {
            throw new PageNotFoundException();
        }

        if (isset($this->data['target'][0])) {
            $this->setMiddleware($this->data['target'][0]);
        }
    }

    /**
     * Run controller
     * @return Controller
     * @throws NotExistsException
     */
    private function controller()
    {
        $class = $this->getController();
        if (!class_exists($class)) {
            throw new NotExistsException("Class [{$class}] does not exist");
        }
        return new $class($this->app);
    }

}
