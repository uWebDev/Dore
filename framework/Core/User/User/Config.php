<?php

namespace Dore\Core\User\User;

use Dore\Core\Foundation\App;
use Dore\Core\Exception\SemanticExceptions\InvalidArgumentException;

/**
 * Class Config
 * @package Dore\Core\User\User
 */
class Config extends \ArrayObject
{

    /**
     * @var AbstractUser
     */
    private $userInstance;

    /**
     * @var array Default settings
     */
    private $defaults;

    /**
     * @param AbstractUser $user
     */
    public function __construct(AbstractUser $user)
    {
        $this->userInstance = $user;
        $this->defaults     = App::config()->get('system.user');
        $cnf                = $user->offsetGet('config', true);
        $config             = !empty($cnf) ? unserialize($cnf) : $this->defaults;
        parent::__construct($config, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Sets the value at the specified index
     *
     * @param string $key
     * @param mixed  $value
     * @throws InvalidArgumentException
     */
    public function offsetSet($key, $value)
    {
        if (!isset($this->defaults[$key])) {
            throw new InvalidArgumentException("Unknown key [$key]");
        }

        parent::offsetSet($key, $value);
    }

    /**
     * Save settings
     */
    public function save()
    {
        $config = $this->getArrayCopy();
        $this->userInstance->offsetSet('config', serialize($config), true);
        $this->userInstance->save();
    }

}
