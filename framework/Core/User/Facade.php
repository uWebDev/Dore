<?php

namespace Dore\Core\User;

use Dore\Core\User\Exception\IUserException;
use Dore\Core\User\Authentication\Identification;
use Dore\Core\User\Authentication\Login;
use Dore\Core\User\Authentication\Logout;
use Dore\Core\User\Authentication\AddUser;
use Dore\Core\User\Tools\FindById;
use Dore\Core\User\Tools\FindByLogin;
use Dore\Core\User\Tools\FindByResetCode;
use Dore\Core\User\Tools\FindBySocialIdAndProvider;
use Dore\Core\User\Tools\FindByActivationCode;
use Dore\Core\User\Tools\DeleteById;
use Dore\Core\User\User\AbstractUser;
use Dore\Core\User\User\EmptyUser;
use Dore\Core\User\User\User;
use Dore\Core\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Dore\Core\Http\Session;

/**
 * Class Facade
 * @package Dore\Core\User
 */
class Facade
{

    /**
     * @var \PDO
     */
    private $db;

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
     * @var AbstractUser
     */
    private $userInstance;

    /**
     * @var string
     */
    public $domain = 'user_auth';

    /**
     * Facade constructor.
     *
     * @param \PDO     $db
     * @param Request  $request
     * @param Response $response
     * @param Session  $session
     */
    public function __construct(\PDO $db, Request $request, Response $response, Session $session)
    {
        $this->db = $db;
        $this->session = $session;
        $this->request = $request;
        $this->response = $response;
        $this->userInstance = (new Identification($this, $session, $request))->getUser();
    }

    /**
     * Check whether a user is valid (is authenticated)?
     * Проверьте, может ли пользователь является допустимым (проходит проверку подлинности) ?
     * @return bool
     */
    public function isValid()
    {
        return ($this->userInstance !== null && $this->userInstance->offsetGet('activeted') == 0);
    }

    /**
     * This user is a guest?
     * Пользователь является гостем?
     * @return boolean
     */
    public function isGuest()
    {
        return !($this->userInstance !== null && $this->userInstance->offsetGet('id') != 0);
    }

    /**
     * The user is the administrator?
     * Пользователь является админом?
     * @return boolean
     */
    public function isAdmin()
    {
        return ($this->userInstance !== null && $this->userInstance->offsetGet('rights') == 99);
    }

    /**
     * Get current User
     * @return User|EmptyUser
     */
    public function get()
    {
        if ($this->isValid()) {
            return $this->userInstance;
        }

        return new EmptyUser;
    }

    /**
     * Set current User
     *
     * @param AbstractUser $user
     */
    public function setUser(AbstractUser $user)
    {
        $this->userInstance = $user;
    }

    /**
     * Search of the User by ID
     *
     * @param int $id
     *
     * @return User
     * @throws IUserException
     */
    public function findById($id)
    {
        try {
            return new User(FindById::find($id, $this->db), $this->db);
        } catch (IUserException $e) {
            throw $e;
        }
    }

    /**
     * Search of the User by Social ID and Provider
     *
     * @param int    $sid
     * @param string $provider
     *
     * @return User
     * @throws IUserException
     */
    public function findBySocialIdAndProvider($sid, $provider)
    {
        try {
            return new User(FindBySocialIdAndProvider::find($sid, $provider, $this->db), $this->db);
        } catch (IUserException $e) {
            throw $e;
        }
    }

    /**
     * Search of the User by Activation Code
     *
     * @param string $code
     *
     * @return User
     * @throws IUserException
     */
    public function findByActivationCode($code)
    {
        try {
            return new User(FindByActivationCode::find($code, $this->db), $this->db);
        } catch (IUserException $e) {
            throw $e;
        }
    }

    /**
     * Search of the User by Password Restore Code
     *
     * @param string $code
     *
     * @return User
     * @throws IUserException
     */
    public function findByResetCode($code)
    {
        try {
            return new User(FindByResetCode::find($code, $this->db), $this->db);
        } catch (IUserException $e) {
            throw $e;
        }
    }

    /**
     * Search of the User by a Nickname or Email
     *
     * @param string $login
     *
     * @return User
     * @throws IUserException
     */
    public function findByLogin($login)
    {
        try {
            return new User(FindByLogin::find($login, $this->db), $this->db);
        } catch (IUserException $e) {
            throw $e;
        }
    }

    /**
     * Login tre User with credentials
     * @return Login
     */
    public function login()
    {
        return new Login($this, $this->session, $this->request, $this->response);
    }

    /**
     * Logout the User
     *
     * @param bool $clearToken
     */
    public function logout($clearToken = false)
    {
        new Logout($this, $this->request, $this->response, $this->session, $clearToken);
    }

    /**
     * Add new User class instance
     * @return AddUser
     */
    public function addUser()
    {
        return new AddUser($this, $this->db, $this->request);
    }

    /**
     * Удалить аккаунт пользователя
     *
     * @param int $id ID пользователя
     */
    public function delete($id)
    {
        DeleteById::delete($id, $this->db);
    }

    /**
     * Generate Token
     *
     * @param int    $length
     * @param string $pool
     * @return string
     */
    public function generateToken($length = 40, $pool = '')
    {
        if (empty($pool)) {
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        return substr(str_shuffle(str_repeat($pool, 3)), 0, $length);
    }
}
