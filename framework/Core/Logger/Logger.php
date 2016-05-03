<?php

namespace Dore\Core\Logger;

use Apix\Log\Logger\File;
use Apix\Log\Logger\Mail;

/**
 * Class Logger
 * @package Dore\Core\Logger
 */
class Logger extends \Apix\Log\Logger
{

    public function __construct(array $loggers = array())
    {
        parent::__construct($loggers);

        $objFileLog = new File(LOG_PATH . 'error.log');
        $objFileLog->setMinLevel('debug'); // same as Psr\Log\LogLevel::DEBUG

        $objMailLog = new Mail('sckrol@ya.ru'); //TODO: вынести адрес почты в настройки
        $objMailLog->setMinLevel('critical'); // or Psr\Log\LogLevel::CRITICAL

        $this->add($objMailLog);
        $this->add($objFileLog);
    }

}
