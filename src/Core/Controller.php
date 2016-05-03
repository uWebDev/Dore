<?php

namespace App\Core;

use Dore\Core\Foundation\Controller as ControllerBase;
use Dore\Core\Foundation\App;

/**
 * Class Controller
 * @package App\Core
 */
abstract class Controller extends ControllerBase
{

    private $modulePath;
    private $moduleName;

    /**
     * Automatically executes before the action of the controller.
     */
    public function before()
    {
        parent::before();

        if (!$this->container['user']->isGuest()) {
            $this->setSessionHandlerUserId();
            $this->hasRegistrationThroughServices();
            $this->hasFullBan();
        }

        $this->container['view']->addFolder('module', $this->getPath() . DS . 'View');
        $this->container['i18n']->setModule($this->getName());
        // TODO изменить: добавление папки
        App::config()->getPaths()->add($this->getPath() . DS . 'Assets' . DS . 'Config' . DS);
    }

    /**
     * Check after registration through third-party services
     */
    private function hasRegistrationThroughServices()
    {
        if (null === $this->container['user']->get()->nickname
            && 'uregister' !== $this->container['dispatcher']->getNameRoute()
        ) {
            $this->container->redirect($this->container['router']->generate('uregister'));
        }
    }

    /**
     * Check user on full ban
     */
    private function hasFullBan()
    {
        if (!$this->container['user']->get()->ban()->has(1)) {
            return false;
        }
        $this->container['user']->logout(true);
        $this->container['session']->setFlash('error', ['error' => 'error_account_is_locked']);
        $this->container->redirect($this->container['router']->generate('login'));
    }

    /**
     * Set handler session user ID
     */
    private function setSessionHandlerUserId()
    {
        $this->container['sessionHandler']->setUserId($this->container['user']->get()->id);
    }


    /**
     * @return string
     */
    private function getPath()
    {
        if (null === $this->modulePath) {
            $this->modulePath = dirname(dirname((new \ReflectionClass($this))->getFileName()));
        }

        return $this->modulePath;
    }

    /**
     * @return string
     */
    private function getName()
    {
        if (null === $this->moduleName) {
            $path = explode('/', $this->getPath());
            $this->moduleName = $path[count($path) - 1];
        }

        return $this->moduleName;
    }
}
