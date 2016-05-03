<?php

namespace Dore\Core\Http;

/**
 * Class Token
 * @package Dore\Core\Http
 */
class Token
{

    protected $session;
    protected $name = 'form_token';

    /**
     * Token constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        if (!$this->has()) {
            $this->generate();
        }
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->session->get($this->name);
    }

    /**
     * @param string $value
     * @return boolean
     */
    public function check($value)
    {
        return ($this->has() && $value === $this->get());
    }

    /**
     * @return string
     */
    private function generate()
    {
        return $this->session->set($this->name, md5(hash('sha256', mt_rand()) . microtime(true)));
    }

    /**
     * @return bool
     */
    private function has()
    {
        return $this->session->has($this->name);
    }

}
