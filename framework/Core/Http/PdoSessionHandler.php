<?php

namespace Dore\Core\Http;

use Dore\Core\Database\PDOmysql;

/**
 * Class Session
 */
class PdoSessionHandler implements \SessionHandlerInterface
{

    /**
     * @var PDOmysql
     */
    private $db;

    /**
     * @var Request
     */
    private $request;
    private $transaction = false;
    private $doGc        = false;
    private $views       = 1;
    private $movings     = 1;
    private $userId      = 0;
    private $place       = '';
    private $stopPlace;
    private $dispatcher;

    /**
     * @var int session.gc_maxlifetime
     */
    public $lifetime;

    public function __construct(PDOmysql $db, Request $request, $dispatcher, $stopPlace = [])
    {
        $this->db         = $db;
        $this->request    = $request;
        $this->dispatcher = $dispatcher;
        $this->stopPlace  = $stopPlace;
        $this->lifetime   = (int) ini_get('session.gc_maxlifetime');
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function setPlace($place)
    {
        $this->place = $place;
    }

    public function setStopPlace(array $value)
    {
        $this->stopPlace = $value;
    }

    /**
     * Open Session
     *
     * @param string $savePath
     * @param string $sessionId
     * @return bool
     */
    public function open($savePath, $sessionId)
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        try {
            $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $this->db->beginTransaction();
            $this->transaction = true;

            $stmt = $this->db->prepare('SELECT * FROM `sessions` WHERE `id` = :id FOR UPDATE');
            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() ? $this->getResult($stmt) : $this->insertRecord($stmt, $sessionId);
        } catch (\PDOException $e) {
            $this->rollback();

            throw $e;
        }
    }

    /**
     * Garbage collector
     *
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        $this->doGc = true;

        return true;
    }

    /**
     * Destroy Session
     *
     * @param string $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM `sessions` WHERE `id` = :id');
            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * Write session data
     *
     * @param string $sessionId
     * @param string $data
     * @return bool
     */
    public function write($sessionId, $data)
    {
        try {
            $stmt = $this->db->prepare(
                    'UPDATE `sessions` SET
                `data`      = :data,
                `timestamp` = :time,
                `ip`        = :ip,
                `userAgent` = :ua,
                `place`     = :place,
                `views`     = :views,
                `movings`   = :movings,
                `userId`    = :uid
                WHERE `id`  = :id'
            );

            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $stmt->bindParam(':data', $data, \PDO::PARAM_LOB);
            $stmt->bindValue(':time', time(), \PDO::PARAM_INT);
            $stmt->bindValue(':ip', $this->request->getClientIp(), \PDO::PARAM_STR);
            $stmt->bindValue(':ua', $this->request->getUserAgent(), \PDO::PARAM_STR);
            $stmt->bindValue(':place', $this->place, \PDO::PARAM_STR);
            $stmt->bindValue(':views', $this->views, \PDO::PARAM_INT);
            $stmt->bindValue(':movings', $this->movings, \PDO::PARAM_INT);
            $stmt->bindValue(':uid', $this->userId, \PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Close the session
     *
     * @return bool
     */
    public function close()
    {
        $this->commit();
        $this->garbage();

        return true;
    }

    private function commit()
    {
        if ($this->transaction) {
            try {
                $this->db->commit();
                $this->transaction = false;
            } catch (\PDOException $e) {
                $this->rollback();
                throw $e;
            }
        }
    }

    /**
     * Rollback a transaction.
     */
    private function rollback()
    {
        if ($this->transaction) {
            $this->db->rollback();
            $this->transaction = false;
        }
    }

    /**
     * Get session data
     *
     * @param \PDOStatement $stmt
     * @return string
     */
    private function getResult(\PDOStatement $stmt)
    {

        $result = $stmt->fetch();

        // If the session is expired
        if ($result['timestamp'] < time() - $this->lifetime) {
            return '';
        }

        $this->countViews($result['views'], $result['timestamp']);
        $this->countMovings($result['movings'], $result['place'], $result['timestamp']);

        return $result['data'];
    }

    /**
     * Insert new session record
     *
     * @param \PDOStatement $stmt
     * @param string        $sessionId
     * @return string
     * @throws \HttpRuntimeException
     */
    private function insertRecord(\PDOStatement $stmt, $sessionId)
    {
        try {
            $insertStmt = $this->db->prepare('INSERT
                INTO `sessions` (`id`, `data`, `timestamp`, `ip`, `userAgent`, `place`, `views`, `movings`)
                VALUES (:id, :data, :time, :ip, :ua, :place, 1, 1)'
            );
            $insertStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $insertStmt->bindValue(':data', '', \PDO::PARAM_LOB);
            $insertStmt->bindValue(':time', time(), \PDO::PARAM_INT);
            $insertStmt->bindValue(':ip', $this->request->getClientIp(), \PDO::PARAM_STR);
            $insertStmt->bindValue(':ua', $this->request->getUserAgent(), \PDO::PARAM_STR);
            $insertStmt->bindValue(':place', '', \PDO::PARAM_STR);
            $insertStmt->execute();
        } catch (\PDOException $e) {
            $this->catchDuplicateKeyError($e, $stmt);
        }

        return '';
    }

    /**
     * Catch duplicate key error
     *
     * @param \PDOException $e
     * @param \PDOStatement $stmt
     * @return string
     */
    private function catchDuplicateKeyError(\PDOException $e, \PDOStatement $stmt)
    {
        if (0 === strpos($e->getCode(), '23')) {
            $stmt->execute();

            return $stmt->rowCount() ? $this->getResult($stmt) : '';
        }

        throw $e;
    }

    /**
     * Garbage collector
     */
    private function garbage()
    {
        if ($this->doGc) {
            $this->doGc = false;
            $stmt       = $this->db->prepare('DELETE FROM `sessions` WHERE `timestamp` < :time');
            $stmt->bindValue(':time', time() - $this->lifetime, \PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    private function countViews($views, $timestamp)
    {
        if ($timestamp > time() - 300) {
            $this->views = ($this->request->isAjax()) ? $views : $views + 1;
        }
    }

    private function countMovings($movings, $place, $timestamp)
    {
        $this->place($place);

        if ($timestamp > time() - 300) {
            $this->movings = ($this->request->isAjax() || ($place == $this->place)) ? $movings : $movings + 1;
        }
    }

    private function place($place)
    {
        $nameRoute   = $this->dispatcher->getNameRoute();
        $this->place = ($this->request->isAjax() || in_array($nameRoute, $this->stopPlace)) ? $place : $nameRoute;
    }

}
