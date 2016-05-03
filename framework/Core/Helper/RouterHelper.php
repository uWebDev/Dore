<?php

namespace Dore\Core\Helper;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class RouterHelper implements ExtensionInterface
{

    private $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('route', [$this, 'generate']);
    }

    /**
     * 
     * @param type $routeName
     * @param array $params
     * @return type
     */
    public function generate($routeName, array $params = array())
    {
        return $this->router->generate($routeName, $params);
    }

}
