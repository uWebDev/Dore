<?php

namespace Dore\Core\Foundation;

use Dore\Core\Exception\SemanticExceptions\LackException;

/**
 * Class Controller
 * @package Dore\Core\Foundation
 */
abstract class Controller
{

    /** @var App */
    protected $container;
    protected $error = [];
    protected $message = [];

    /**
     * BaseController constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->container = $app;
    }

    /**
     * Automatically executes before the action of the controller.
     */
    public function before()
    {
        // Default is empty
    }

    /**
     * @param $action
     * @param $params
     *
     * @throws LackException
     */
    public function run($action, $params)
    {
        $this->before();
        $method = 'action' . $action;
        if (!method_exists($this, $method)) {
            throw new LackException("Target method [$action] of class [" . get_class($this) . '] not found');
        }
        $this->$method($params);
        $this->after();
    }

    /**
     * Automatically executed after controller action.
     */
    public function after()
    {
        // Default is empty
    }

}
