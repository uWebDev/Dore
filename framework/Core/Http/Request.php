<?php

namespace Dore\Core\Http;

/**
 * Class Request
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{

    private $ip;
    private $userAgent;

    /**
     * Gets parameter GET
     *
     * @param string $key       Option key.
     * @param bool   $filterXss Do I need to filter the incoming data for XSS protection.
     * @param mixed  $default   The default value.
     *
     * @return mixed Returns the value of the key, if specified, or an array of GET parameters.
     */
    public function get($key = null, $filterXss = true, $default = null)
    {
        $res = parent::get($key, $default);
        return $this->getFilteredValue($res, $filterXss);
    }

    /**
     * Gets parameter POST
     *
     * @param string $key       Option key.
     * @param bool   $filterXss Do I need to filter the incoming data for XSS protection.
     * @param mixed  $default   The default value.
     *
     * @return mixed Returns the value of the key, if you specify an array or POST parameters.
     */
    public function post($key = null, $filterXss = true, $default = null)
    {
        return $this->getFilteredValue($this->request->get($key, $default), $filterXss);
    }

    /**
     * Gets parameter COOKIE
     *
     * @param string $key       Option key.
     * @param bool   $filterXss Do I need to filter the incoming data for XSS protection.
     * @param mixed  $default   The default value.
     *
     * @return mixed Returns the value of the key, if specified, or an array COOKIE parameters.
     */
    public function cookie($key = null, $filterXss = true, $default = null)
    {
        return $this->getFilteredValue($this->cookies->get($key, $default), $filterXss);
    }

    /**
     * Gets parameter SERVER
     *
     * @param string $key       Option key.
     * @param bool   $filterXss Do I need to filter the incoming data for XSS protection.
     * @param mixed  $default   The default value.
     *
     * @return mixed Returns the value of the key, if specified, or an array of parameters SERVER.
     */
    public function server($key = null, $filterXss = true, $default = null)
    {
        return $this->getFilteredValue($this->server->get($key, $default), $filterXss);
    }

    /**
     * Returns the client IP address
     * @return string
     */
    public function getClientIp()
    {
        if (null === $this->ip) {
            $this->ip = $this->getClientIps();
        }

        return sprintf('%u', ip2long($this->ip[0]));
    }

    /**
     * Check whether the IP address of the proxy server
     * @return bool
     */
    public function isProxyIp()
    {
        if (null === $this->ip) {
            $this->ip = $this->getClientIps();
        }

        return count($this->ip) > 1;
    }

    /**
     * Check, if the request is ajax
     * @return bool
     */
    public function isAjax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Method is POST
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod('POST');
    }

    /**
     * Generates a normalized URI (URL) for the Request.
     *
     * @param bool $params
     *
     * @return string
     */
    public function getUri($params = false)
    {
        $qs = '';
        if (true === $params && null !== $str = $this->getQueryString()) {
            $qs = '?' . $str;
        }

        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $this->getPathInfo() . $qs;
    }

    /**
     * Returns the User Agent
     * @return string
     */
    public function getUserAgent()
    {
        if (null === $this->userAgent) {
            $this->userAgent = $this->headers->get('User-Agent', 'User Agent not recognised');
        }

        return filter_var($this->userAgent, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * @param       $key
     * @param null  $default
     * @param null  $filter
     * @param array $filterOpts
     *
     * @return mixed
     */
    public function getFiltered($key, $default = null, $filter = null, array $filterOpts = [])
    {
        $filter = (null === $filter) ? FILTER_UNSAFE_RAW : $filter;

        return filter_var($this->get($key, $default), $filter, $filterOpts);
    }

    /**
     * @param mixed $value
     * @param bool  $filterXss
     *
     * @return mixed
     */
    private function getFilteredValue($value, $filterXss = true)
    {
        return $filterXss ? $this->filterXss($value) : $value;
    }

    /**
     * Filters input to prevent XSS-attacks.
     * If an array is passed, it filters all the elements recursively.
     *
     * @param mixed $value Input to sanitize.
     *
     * @return mixed Filtered values
     */
    private function filterXss($value)
    {
        if (null !== $value) {
            if (is_array($value)) {
                array_walk_recursive($value,
                    function (&$str) {
                        $str = strip_tags($str);
                    });
            } else {
                $value = strip_tags($value);
            }
        }

        return $value;
    }

}
