<?php

namespace Dore\Core\Http;

/**
 * Description of Session
 * @author hiddenbit
 */
class Session extends \Symfony\Component\HttpFoundation\Session\Session
{

    /**
     * It gets a simple set of instant messaging.
     * If the value of the message is sent is set, otherwise it will be removed.
     * Once the message is retrieved for the first time it is removed.
     *
     * @param $key
     *
     * @return mixed
     */
    public function getFlash($key)
    {
        $flashKey = $this->flashNameKey($key);
        $value = $this->get($flashKey);
        $this->remove($flashKey);
        return $value;
    }

    public function setFlash($key, $value)
    {
        $this->set($this->flashNameKey($key), $value);
    }

    private function flashNameKey($key)
    {
        return "flash_{$key}";
    }

}
