<?php

namespace Dore\Core\Mail;

use PHPMailer;

/**
 * Class Mail
 * @package Dore\Core\Mail
 */
class Mail
{
    protected $mail;

    /**
     * Mail constructor.
     *
     * @param PHPMailer $mail
     */
    public function __construct(PHPMailer $mail)
    {
        $this->mail = $mail;
    }

    /**
     * @param $email
     * @param $subject
     * @param $body
     *
     * @return bool
     * @throws \phpmailerException
     */
    public function sendMail($email, $subject, $body)
    {

        // пожалуйста, посмотрите в конфигурационном / config.php
        // для гораздо большего количества информации о том, как использовать это!
        // use SMTP or use mail()
        if (EMAIL_USE_SMTP) {
            // Установите почтовая использовать SMTP
            $this->mail->IsSMTP();
            //полезно для отладки, показывает полные ошибок SMTP
            $this->mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
            // Включение проверки подлинности SMTP
            $this->mail->SMTPAuth = EMAIL_SMTP_AUTH;
            // Включите шифрование, как правило, SSL / TLS
            if (defined(EMAIL_SMTP_ENCRYPTION)) {
                $this->mail->SMTPSecure = EMAIL_SMTP_ENCRYPTION;
            }
            // Укажите хост-серверу
            $this->mail->Host = EMAIL_SMTP_HOST;
            $this->mail->Username = EMAIL_SMTP_USERNAME;
            $this->mail->Password = EMAIL_SMTP_PASSWORD;
            $this->mail->Port = EMAIL_SMTP_PORT;
        } else {
            $this->mail->IsMail();
        }

        $this->mail->CharSet = "utf-8";
        $this->mail->From = "myfortis@yandex.ru";
        $this->mail->FromName = "My Project";
        $this->mail->AddAddress($email);
        $this->mail->Subject = $subject;
        $this->mail->Body = $body;

        return $this->mail->Send();
    }

    /**
     * @return string
     */
    public function error()
    {
        return $this->mail->ErrorInfo;
    }
}
