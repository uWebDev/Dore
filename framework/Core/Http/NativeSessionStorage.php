<?php

namespace Dore\Core\Http;

/**
 * Class NativeSessionStorage
 *
 * @package Mobicms\Http
 */
class NativeSessionStorage extends \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage
{
    public function __construct($handler = null)
    {
        session_cache_limiter('');
        ini_set('session.use_cookies', 1);

        $this->setMetadataBag(null);
        $this->setSaveHandler($handler);
    }
}
