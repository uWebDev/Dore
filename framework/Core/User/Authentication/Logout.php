<?php

namespace Dore\Core\User\Authentication;

use Dore\Core\User\Facade;
use Dore\Core\Http\Request;
use Dore\Core\User\User\EmptyUser;
use Symfony\Component\HttpFoundation\Response;
use Dore\Core\Http\Session;

/**
 * Class Logout
 * @package Dore\Core\User\Authentication
 */
class Logout
{

    /**
     * Logout constructor.
     *
     * @param Facade   $facade
     * @param Request  $request
     * @param Response $response
     * @param Session  $session
     * @param bool     $clearToken
     */
    public function __construct(Facade $facade, Request $request, Response $response, Session $session,
                                $clearToken = false)
    {
        $this->clearToken($facade, $clearToken);
        $this->clearCookie($request, $response, $facade->domain);
        $this->clearSession($session);
        $facade->setUser(new EmptyUser());
    }

    /**
     * Clear authorization token
     *
     * @param Facade $facade
     * @param bool   $clearToken
     */
    private function clearToken(Facade $facade, $clearToken)
    {
        if ($clearToken && $facade->isValid()) {
            $user = $facade->get();
            $user->setToken(null);
            $user->save();
        }
    }

    /**
     * Clear authorization Cookie
     *
     * @param Request  $request
     * @param Response $response
     * @param          $authDomain
     */
    private function clearCookie(Request $request, Response $response, $authDomain)
    {
        if ($request->cookies->has($authDomain)) {
            $response->headers->clearCookie($authDomain);
        }
    }

    /**
     * Clear Session
     *
     * @param Session $session
     */
    private function clearSession(Session $session)
    {
        if ($session->isStarted()) {
            $session->clear();
        }
    }

}
