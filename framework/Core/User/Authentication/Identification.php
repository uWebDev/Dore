<?php

namespace Dore\Core\User\Authentication;

use Dore\Core\User\Exception\IUserException;
use Dore\Core\User\Facade;
use Dore\Core\User\User\AbstractUser;
use Dore\Core\Http\Request;
use Dore\Core\Http\Session;

class Identification
{

    /**
     * @var Facade
     */
    private $facade;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Facade $facade, Session $session, Request $request)
    {
        $this->session = $session;
        $this->request = $request;
        $this->facade = $facade;
    }

    /**
     * @return \Dore\Core\User\User\User|null
     */
    public function getUser()
    {
        $auth = $this->getAuthData();

        if (count($auth) === 0) {
            return null;
        }

        try {
            $userInstance = $this->facade->findById($auth[0]);

            if ($this->faceControlUser($userInstance)) {
                $userInstance->checkToken($auth[1]);
                $userInstance->ipHistory($this->request); //Фиксация истории ip адресов
                $userInstance->ban()->check(); // Проверка на бан
                $userInstance->updateAttributes($this->request);
                $userInstance->save();

                return $userInstance;
            } else {
                // Если авторизация не прошла, Увеличиваем счетчик неудачных попыток
                $userInstance->increaseCounterFailedAttempts();
                $this->facade->logout(true); //Уничтожаем данные авторизации юзера

                return null;
            }
        } catch (IUserException $e) {
            $this->facade->logout(); // если пользователь не существует, Уничтожаем данные авторизации юзера.

            return null;
        }
    }

    /**
     * @param AbstractUser $user
     *
     * @return boolean
     */
    private function faceControlUser(AbstractUser $user)
    {
        return true; //TODO
        // return (($user->offsetGet('failedLogins') < 3 || $user->offsetGet('failedLogins') > 2
        //         && $user->offsetGet('ip') == $this->request->getClientIp()
        //         && $user->offsetGet('userAgent') == $this->request->getUserAgent()));
        // return ($user->offsetGet('ip') == $this->request->getClientIp()
        // && $user->offsetGet('userAgent') == $this->request->getUserAgent()
        //  );
    }

    /**
     * Try to get Session or Cookies identification data
     * @return array
     */
    private function getAuthData()
    {
        if ($this->session->has($this->facade->domain)) {
            return explode(':', $this->session->get($this->facade->domain));
        } elseif ($this->request->cookies->has($this->facade->domain)) {
            return $this->getCookie();
        }

        return [];
    }

    /**
     * Get Cookies identification data
     * @return array
     */
    private function getCookie()
    {
        $auth = explode(':', base64_decode(trim($this->request->cookie($this->facade->domain))));

        if (empty($auth[0]) || empty($auth[1]) || !is_numeric($auth[0])) {
            $this->facade->logout();

            return [];
        }

        return $auth;
    }

}
