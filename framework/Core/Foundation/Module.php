<?php

namespace Dore\Core\Foundation;


/**
 * Class Module
 * @package Dore\Core\Foundation
 */
abstract class Module
{
    private $path;

    /**
     * @return array
     */
    public function getRoutes()
    {
        if (is_readable($this->path)) {
            return require $this->path;
        } else {
            return [];
        }
    }

    /**
     * @param string $path
     */
    public function setRoutes($path)
    {
        $this->path = $path;
    }

}
