<?php

namespace Dore\Core\User\Authentication;

use Dore\Core\User\Exception\InvalidInputException;
use Dore\Core\User\Exception\IUserException;
use Dore\Core\User\Exception\UserLockedException;
use Dore\Core\User\Exception\UserNotActiveExcetpion;
use Dore\Core\User\Exception\WrongPasswordException;
use Dore\Core\User\Facade;
use Dore\Core\User\User\AbstractUser;
use Dore\Core\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Dore\Core\Http\Session;

/**
 * Class Login
 * @package Dore\Core\User\Authentication
 */
class Login
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

    /**
     * @var Response
     */
    private $response;

    /**
     * Login constructor.
     *
     * @param Facade   $facade
     * @param Session  $session
     * @param Request  $request
     * @param Response $response
     */
    public function __construct(Facade $facade, Session $session, Request $request, Response $response)
    {
        $this->session = $session;
        $this->request = $request;
        $this->response = $response;
        $this->facade = $facade;
    }

    /**
     * Authenticate the User with credentials
     *
     * @param AbstractUser $userInstance
     * @param              $password
     * @param bool         $remember
     *
     * @throws IUserException
     * @throws InvalidInputException
     */
    public function authenticate(AbstractUser $userInstance, $password, $remember = false)
    {
        try {
            $this->checkFailedLogin($userInstance);
            $this->checkPassword($userInstance, $password);
            $this->checkActive($userInstance);
            $this->facade->setUser($userInstance);
            $this->checkBan($userInstance);
        } catch (IUserException $e) {
            throw $e;
        }

        $this->checkToken($userInstance);
        $this->writeSession($userInstance);
        $this->writeCookie($userInstance, $remember);
        $userInstance->updateAttributes($this->request);
        $userInstance->offsetSet('failedLogins', 0);
        $userInstance->offsetSet('lastFailedLogin', 0);
        $userInstance->offsetSet('passwordResetHash', null);
        $userInstance->offsetSet('passwordResetTimestamp', 0);
        $userInstance->save();
    }

    /**
     * Resetting a user password
     *
     * @param AbstractUser $userInstance
     *
     * @return mixed|string
     * @throws IUserException
     * @throws InvalidInputException
     */
    public function resetPassword(AbstractUser $userInstance)
    {
        try {
            $this->checkActive($userInstance);
            $this->checkBan($userInstance);
        } catch (IUserException $e) {
            throw $e;
        }

        $code = $this->checkRestoreCode($userInstance);
        $userInstance->offsetSet('passwordResetTimestamp', time());
        $userInstance->save();

        return $code;
    }

    /**
     * Change Password
     *
     * @param AbstractUser $userInstance
     * @param              $password
     *
     * @throws IUserException
     * @throws InvalidInputException
     */
    public function changePassword(AbstractUser $userInstance, $password)
    {
        try {
            $this->checkActive($userInstance);
            $this->checkBan($userInstance);
        } catch (IUserException $e) {
            throw $e;
        }

        $userInstance->setPassword($password);
        $userInstance->offsetSet('passwordResetHash', null);
        $userInstance->offsetSet('passwordResetTimestamp', 0);
        $userInstance->save();
    }

    /**
     * Activation user
     *
     * @param AbstractUser $userInstance
     */
    public function activation(AbstractUser $userInstance)
    {
        //TODO добавить проверку
        $userInstance->offsetSet('activeted', 0);
        $userInstance->offsetSet('activationHash', null);
        $userInstance->save();
    }

    /**
     * Check your password
     *
     * @param AbstractUser $userInstance
     * @param              $password
     *
     * @throws InvalidInputException
     * @throws WrongPasswordException
     */
    private function checkPassword(AbstractUser $userInstance, $password)
    {
        if (($userInstance->offsetGet('provider') == 'native') && !$userInstance->checkPassword($password)) {
            //Защита от подбора пароля
            $userInstance->increaseCounterFailedAttempts();
            throw new WrongPasswordException(_s('Login or password is not entered correctly'));
        }
    }

    /**
     * Checks limit failed login attempts
     *
     * @param AbstractUser $userInstance
     *
     * @throws InvalidInputException
     * @throws \Dore\Core\Exception\SemanticExceptions\InvalidArgumentException
     */
    private function checkFailedLogin(AbstractUser $userInstance)
    {
        if (($userInstance->offsetGet('failedLogins') >= 3)
            && ($userInstance->offsetGet('lastFailedLogin') > (time() - 30))
        ) {
            throw new InvalidInputException(
                sprintf(_s('You entered the wrong password for %1$d times or more. Please wait %2$d seconds to try again'), 3, 30)
            );
        }
    }

    /**
     * Checks account activation
     *
     * @param AbstractUser $userInstance
     *
     * @throws InvalidInputException
     * @throws UserNotActiveExcetpion
     */
    private function checkActive(AbstractUser $userInstance)
    {
        if (0 != $userInstance->offsetGet('activeted')) {
            throw new UserNotActiveExcetpion(_s('Your account is not yet activated. Click the link in the email to activate'));
        }
    }

    /**
     * Checks account suspension
     *
     * @param AbstractUser $userInstance
     *
     * @throws UserLockedException
     */
    private function checkBan(AbstractUser $userInstance)
    {
        if ($userInstance->ban()->has(1)) {
            throw new UserLockedException(_s('Your account is blocked'));
        }
    }

    /**
     * Check identification token
     *
     * @param AbstractUser $userInstance
     */
    private function checkToken(AbstractUser $userInstance)
    {
        $token = $userInstance->offsetGet('remembermeToken', true);

        if (empty($token)) {
            $token = $this->facade->generateToken(64);
            $userInstance->setToken($token);
        }
    }

    /**
     * @param AbstractUser $userInstance
     *
     * @return mixed|string
     */
    private function checkRestoreCode(AbstractUser $userInstance)
    {
        $code = $userInstance->offsetGet('passwordResetHash', true);

        if (empty($code)) {
            $code = $this->facade->generateToken();
            $userInstance->offsetSet('passwordResetHash', $code, true);
        }

        return $code;
    }

    /**
     * Write identification session
     *
     * @param AbstractUser $userInstance
     */
    private function writeSession(AbstractUser $userInstance)
    {
        $this->session->set($this->facade->domain,
            $userInstance->offsetGet('id') . ':' . $userInstance->offsetGet('remembermeToken', true));
    }

    /**
     * Write identification Cookie
     *
     * @param AbstractUser $userInstance
     * @param bool         $remember
     */
    private function writeCookie(AbstractUser $userInstance, $remember)
    {
        if ($remember) {
            $this->response->headers->setCookie(
                new Cookie(
                    $this->facade->domain,
                    base64_encode($userInstance->offsetGet('id') . ':' . $userInstance->offsetGet('remembermeToken',
                            true)), time() + 3600 * 24 * 31
                )
            );
        }
    }

}
