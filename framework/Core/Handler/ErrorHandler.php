<?php

namespace Dore\Core\Handler;

use Dore\Core\Foundation\App;
use Dore\Core\Exception\DisplayMessageException;

/**
 * @link http://habrahabr.ru/post/134499/
 * @link http://dklab.ru/lib/Debug_ErrorHook/
 * @link https://github.com/k1ng440/PHP_ErrorHandler
 * @link http://pastebin.com/krwVpdZz
 */
class ErrorHandler
{
    /** @var string */
    const EXCEPTION = 'exception';

    /** @var string */
    const ERROR = 'error';

    /** @var array */
    static protected $template = [
        'displayError' => "<div style='color: #cc0000'><strong>%error%</strong>: %message% in <strong>%file%</strong> on line <em>%line%</em></div>",
        'displayException' => "<div style='color: #cc8909'>Uncaught <strong>%class%</strong>: %message% on<strong>%file%</strong> in line <em>%line%</em></div>",
        'writeError' => "[%datetime%] %error%: %message% in %file% on line %line%\r\n",
        'writeException' => "[%datetime%] Uncaught %class%: %message% on %file% in line %line%\r\n"
    ];

    /** @var array */
    static protected $error = [
        0 => 'Unknown',
        E_ERROR => 'Fatal',
        E_RECOVERABLE_ERROR => 'Recoverable',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse',
        E_NOTICE => 'Notice',
        E_STRICT => 'Strict',
        E_DEPRECATED => 'Deprecated',
        E_CORE_ERROR => 'Fatal',
        E_CORE_WARNING => 'Warning',
        E_COMPILE_ERROR => 'Compile Fatal',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'Fatal',
        E_USER_WARNING => 'Warning',
        E_USER_NOTICE => 'Notice',
        E_USER_DEPRECATED => 'Deprecated'
    ];

    /** @var array */
    protected $fatalErrors = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR
    ];

    /** @var array */
    protected $setting = [
        'errorLevel' => 'E_ALL',
        'displayError' => false,
        'logFile' => false,
        'dateFormat' => 'd-m-Y H:i:s',
        'documentRoot' => null
    ];

    /**
     * @var array //TODO доработать
     * @link http://php.net/manual/ru/errorfunc.configuration.php
     */
    protected $ini = [
        'error_reporting' => -1,
        'report_memleaks' => 1,
        'ignore_repeated_errors' => 1,
        'ignore_repeated_source' => 1,
        'track_errors' => 1,
        'html_errors' => 0,
        'log_errors_max_len' => 0,
        'display_errors' => 0
    ];

    /**
     * The Constructor.
     *
     * @param int $errorLevel error_reporting level. (See http://php.net/manual/en/function.error-reporting.php ).
     *
     * @link http://php.net/manual/ru/ref.errorfunc.php
     */
    public function __construct($errorLevel = null)
    {
        if (!ob_get_level()) {
            ob_start();
        }

        /* Default settings. */
        $this->setting['errorLevel'] = is_null($errorLevel) ? ini_get('error_reporting') : (int)$errorLevel;
        $this->setting['documentRoot'] = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR,
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);

        $this->setIni($this->ini);

        /**
         * Задает определенный пользователем обработчик ошибок
         * @link http://php.net/manual/ru/function.set-error-handler.php
         */
        set_error_handler(array($this, 'handleErrors'), $this->setting['errorLevel']);

        /**
         * Задает пользовательский обработчик исключений
         * @link http://php.net/manual/ru/function.set-exception-handler.php
         */
        set_exception_handler(array($this, 'handleExceptions'));

        /**
         * Регистрирует функцию, которая выполнится по завершении работы скрипта
         * @link http://php.net/manual/ru/function.register-shutdown-function.php
         */
        register_shutdown_function(array($this, 'handleFatal'));
    }

    /**
     * Magic method for Settings
     *
     * @param  string $setting   Name of the setting.
     * @param  mixed  $arguments Value of setting.
     *
     * @return ErrorHandler      Fluent interface.
     */
    public function __call($setting, $arguments)
    {
        return $this->set($setting, $arguments[0]);
    }

    /**
     * Эта функция может использоваться для определения собственного пути обработки ошибок во время выполнения.
     *
     * @param string $errno      Уровне ошибки.
     * @param string $errstr     Сообщение об ошибке.
     * @param null   $errfile    Имя файла в котором возникла ошибка.
     * @param null   $errline    Номер строки где возникла ошибка.
     * @param array  $errcontext Необязательно. Массив, который указывает на активную таблицу символов в точке
     *                           возникновения ошибки.
     *
     * @return bool
     * @throws \ErrorException
     */
    public function handleErrors($errno, $errstr, $errfile = null, $errline = null, array $errcontext = array())
    {
        // если ошибка была подавлена с @
        if (0 === error_reporting()) {
            return true;
        }
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Обработчик исключений.
     *
     * @param \Exception $e Arguments passed by set_exception_handler() function
     */
    public function handleExceptions(\Exception $e)
    {
        $this->obClean();

        /* Write Errors to file. */
        if ($this->setting['logFile']) {
            $this->logToFile(self::EXCEPTION, $e);
        }

        /* Display Errors */
        if ($this->setting['displayError']) {
            $view = new View();
            $view->exception = $e;
            echo $view->render();
        } else {
            http_response_code(503);
            echo '<h1>503 Service Unavailable</h1>';
        }
    }

    /**
     * Обработчик фатальных ошибок
     */
    public function handleFatal()
    {
        $this->obClean();

        $lastError = error_get_last();
        if ($lastError && in_array($lastError['type'], $this->fatalErrors)) {
            /* Сбросить буфер, завершить работу буфера */
            ob_end_clean();

            /* Write Errors to file. */
            if ($this->setting['logFile']) {
                $this->logToFile(self::ERROR, $lastError);
            }

            /* Display Errors */
            if ($this->setting['displayError']) {
                $this->logToDisplay(self::ERROR, $lastError);
            } else {
                http_response_code(500);
                echo '<h1>500 Internal Server Error</h1>';
            }
        } else {
            //TODO на хостинге ругается, узнать нужно ли это тут
            /* Сброс (отправка) буфера вывода и отключение буферизации вывода */
            if (ob_get_level()) {
                ob_end_flush();
            }
        }
    }

    /**
     * Установить пользовательские настройки
     *
     * @param string $setting Name of the setting.
     * @param mixed  $value   Value of the setting.
     *
     * @return ErrorHandler   Fluent interface.
     */
    public function set($setting, $value)
    {
        if (array_key_exists($setting, $this->setting)) {
            $this->setting[$setting] = $value;
        }
        return $this;
    }

    /**
     * Writes errors to an error log file.
     *
     * @param string $type  This can be either self::ERROR or self::EXCEPTION
     * @param mixed  $error Arguments of HandleException or HandleError
     */
    protected function logToFile($type, $error)
    {
        switch ($type) {
            case self::ERROR:
                App::log()->error($this->lineFormat('writeError', $error));
                break;
            case self::EXCEPTION:
                App::log()->warning($this->lineFormat('writeException', $error));
                break;
        }
    }

    /**
     * Display errors on web page. I do not recommend this to be turned on a production site.
     *
     * @param string $type  This can be either self::ERROR or self::EXCEPTION
     * @param mixed  $error Arguments of HandleException or HandleError
     */
    protected function logToDisplay($type, $error)
    {
        switch ($type) {
            case self::ERROR:
                echo $this->lineFormat('displayError', $error);
                break;
            case self::EXCEPTION:
                echo $this->lineFormat('displayException', $error);
                break;
        }
    }

    /**
     * Format the templates.
     *
     * @param string $format Template name.
     * @param mixed  $error  Exception or array passed by error handler.
     *
     * @return mixed
     */
    protected function lineFormat($format, $error)
    {
        if ($error instanceof \Exception) {
            $template = array(
                '%datetime%' => date($this->setting['dateFormat']),
                '%class%' => get_class($error),
                '%message%' => str_replace($this->setting['documentRoot'], '', $error->getMessage()),
                '%fullpath%' => $error->getFile(),
                '%file%' => str_replace($this->setting['documentRoot'], '', $error->getFile()),
                '%line%' => $error->getLine(),
                '%code%' => $error->getCode()
            );
        } else {
            $template = array(
                '%datetime%' => date($this->setting['dateFormat']),
                '%error%' => array_key_exists($error['type'], self::$error) ? self::$error[$error['type']] : self::$error[0] . ' ' . $error['type'],
                '%message%' => str_replace($this->setting['documentRoot'], '', $error['message']),
                '%fullpath%' => $error['file'],
                '%file%' => str_replace($this->setting['documentRoot'], '', $error['file']),
                '%line%' => $error['line'],
                '%context%' => isset($error['context']) ? print_r($error['context'], true) : ""
            );
        }

        $result = self::$template[$format];
        foreach ($template as $placeholder => $value) {
            $result = str_replace($placeholder, $value, $result);
        }

        return $result;
    }

    protected function obClean()
    {
        $obGetLevel = ob_get_level();
        if ($obGetLevel > 1) {
            for ($i = 1; $i < $obGetLevel; $i++) {
                ob_end_clean();
            }
        }
    }

    protected function setIni(array $ini)
    {
        foreach ($ini as $key => $value) {
            ini_set($key, $value);
        }
    }
}
