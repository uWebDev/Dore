<?php

namespace Dore\Core\User\User;

use Dore\Core\Exception\SemanticExceptions\InvalidArgumentException;
use Dore\Core\Http\Request;
use Dore\Core\User\Exception\InvalidTokenException;

/**
 * Class AbstractUser
 * @package Dore\Core\User\User
 */
abstract class AbstractUser extends \ArrayObject
{

    /**
     * @var \PDO
     */
    protected $db;

    /** @var Config */
    protected $configInstance;
    /** @var  Ban */
    protected $banInstance;
    /** Only for reading */
    protected $readonlyFields = ['id', 'passwordHash', 'remembermeToken', 'rights', 'config'];
    /** Hidden fields */
    protected $hiddenFields = ['passwordHash', 'remembermeToken'];
    protected $changed = [];

    /**
     * Returns the value at the specified index
     *
     * @param string     $key
     * @param bool|false $ignore
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function offsetGet($key, $ignore = false)
    {
        if (!$ignore && in_array($key, $this->hiddenFields)) {
            throw new InvalidArgumentException("[$key] is hidden field");
        }

        return parent::offsetGet($key);
    }

    /**
     * Sets the value at the specified index
     *
     * @param string     $key
     * @param mixed      $value
     * @param bool|false $ignore
     *
     * @throws InvalidArgumentException
     */
    public function offsetSet($key, $value, $ignore = false)
    {
        if (in_array($key, $this->readonlyFields) && !$ignore) {
            throw new InvalidArgumentException("[$key] is read only field");
        }

        $this->updateChangedFields($key, $value);
        parent::offsetSet($key, $value);
    }

    /**
     * Set new password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->offsetSet('passwordHash', password_hash($password, PASSWORD_BCRYPT), true);
    }

    /**
     * Set new token
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->offsetSet('remembermeToken', $token, true);
    }

    /**
     * @param $token
     *
     * @throws InvalidTokenException
     */

    public function checkToken($token)
    {
        $userToken = $this->offsetGet('remembermeToken', true);

        if (empty($userToken) || $userToken !== $token) {
            throw new InvalidTokenException('Invalid token');
        }
    }

    /**
     * Get User configuration
     * @return Config
     */
    public function config()
    {
        if (null === $this->configInstance) {
            $this->configInstance = new Config($this);
        }

        return $this->configInstance;
    }

    /**
     * Blocking user access
     * @return Ban
     */
    public function ban()
    {
        if (null === $this->banInstance) {
            $this->banInstance = new Ban($this, $this->db);
        }

        return $this->banInstance;
    }

    /**
     * Check password
     *
     * @param string $password
     *
     * @return bool
     */
    public function checkPassword($password)
    {
        return password_verify($password, $this->offsetGet('passwordHash', true));
    }


    /**
     * Fixing the history of IP addresses
     *
     * @param Request $request
     */
    public function ipHistory(Request $request)
    {

        if ($this->offsetGet('ip') == $request->getClientIp()) {
            return;
        }

        $sql = 'SELECT `id` FROM `users_ip` WHERE `id` = ? AND `ip` = ? LIMIT 1';
        $STH = $this->db->prepare($sql);
        $STH->execute([
            $this->offsetGet('id'),
            $request->getClientIp()
        ]);

        if ($STH->rowCount()) {
            // Обновляем имеющуюся запись
            $result = $STH->fetch();
            $sqlf = 'UPDATE `users_ip` SET `userAgent` = ?, `timestamp` = UNIX_TIMESTAMP() WHERE `id` = ?';
            $STHF = $this->db->prepare($sqlf);
            $STHF->execute([
                $request->getUserAgent(),
                $result['id']
            ]);
            $STHF = null;
        } else {
            // Вставляем новую запись
            $sqlf = 'INSERT INTO `users_ip` SET `userId` = ?, `ip` = ?, `userAgent` = ?, `timestamp` = UNIX_TIMESTAMP()';
            $STHF = $this->db->prepare($sqlf);
            $STHF->execute([
                $this->offsetGet('id'),
                $request->getClientIp(),
                $request->getUserAgent()
            ]);
            $STHF = null;
        }
    }

    /**
     * Save changes
     */
    public function save()
    {
        if (count($this->changed)) {
            $sql = [];
            $data = [];

            foreach ($this->changed as $key => $val) {
                $sql[] = '`' . $key . '` = ?';
                $data[] = $this->offsetGet($key, true);
            }

            $stmt = $this->db->prepare('UPDATE users SET ' . implode(',', $sql)
                . " WHERE `id` = {$this->offsetGet('id')} LIMIT 1");
            $stmt->execute($data);
            $stmt = null;
        }
    }

    /**
     * Update attributes
     *
     * @param Request $request
     */
    public function updateAttributes(Request $request)
    {
        $lastVisit = $this->offsetGet('lastVisit');

        if ($lastVisit > (time() - 120)) {
            $this->offsetSet('totalTime', ($this->offsetGet('totalTime') + time()) - $lastVisit);
        }
        $this->offsetSet('lastVisit', time());
        $this->offsetSet('ip', $request->getClientIp());
        $this->offsetSet('userAgent', $request->getUserAgent());
    }

    /**
     * Increase the counter of failed attempts
     * @return boolean
     * @throws NotExistsException
     */
    public function increaseCounterFailedAttempts()
    {
        $count = $this->offsetGet('failedLogins');
        $this->offsetSet('failedLogins', ++$count);
        $this->offsetSet('lastFailedLogin', time());
        $this->save();
    }

    /**
     * Update list of changed fields
     *
     * @param string $key
     * @param mixed  $value
     */
    private function updateChangedFields($key, $value)
    {
        if ($value != $this->offsetGet($key, true)) {
            $this->changed[$key] = true;
        }
    }

}
