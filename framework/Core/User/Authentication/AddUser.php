<?php

namespace Dore\Core\User\Authentication;

use Dore\Core\User\Facade;
use Dore\Core\Http\Request;

/**
 * Class AddUser
 * @package Dore\Core\User\Authentication
 */
class AddUser
{

    /**
     * @var Facade
     */
    private $facade;

    /**
     * @var \PDO
     */
    private $db;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $codeActivation;

    /**
     * Registration the User
     *
     * @param Facade  $facade
     * @param \PDO    $db
     * @param Request $request
     */
    public function __construct(Facade $facade, \PDO $db, Request $request)
    {
        $this->facade = $facade;
        $this->db = $db;
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getCodeActivation()
    {
        return $this->codeActivation;
    }

    /**
     * @param string $nickname
     * @param string $password
     * @param string $email
     * @param bool   $activate
     *
     * @return int
     */
    public function add($nickname, $password, $email = '', $activate = true)
    {
        $this->codeActivation = ($activate === true) ? $this->facade->generateToken() : null;
        $active = ($activate === true) ? 1 : 0;
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        return $this->insertUser($nickname, $passwordHash, $email, 0, 'native', $active, $this->codeActivation);
    }

    /**
     * @param int    $socId
     * @param string $provider
     *
     * @return int
     */
    public function addSocial($socId, $provider)
    {
        return $this->insertUser(null, null, null, $socId, $provider, 0, null);
    }

    /**
     * @param string $nickname
     * @param string $passwordHash
     * @param string $email
     * @param int    $sid
     * @param string $provider
     * @param bool   $active
     * @param string $activationCode
     *
     * @return int User ID
     */
    private function insertUser($nickname, $passwordHash, $email, $sid, $provider, $active, $activationCode)
    {
        $sql = 'INSERT INTO users (nickname, passwordHash, email, '
            . 'socialId, provider, activeted, activationHash,'
            . ' registrationIp, registrationUserAgent, registrationDatetime) '
            . 'VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())';
        $STH = $this->db->prepare($sql);
        $STH->execute([
            $nickname,
            $passwordHash,
            $email,
            $sid,
            $provider,
            $active,
            $activationCode,
            $this->request->getClientIp(),
            $this->request->getUserAgent(),
        ]);

        return $this->db->lastInsertId();
    }

}
