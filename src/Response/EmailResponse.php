<?php

namespace SypherLev\Chassis\Response;

use SypherLev\Chassis\Error\ChassisException;

class EmailResponse
{
    private $emailto;
    private $emailfrom;
    private $subject;
    private $message;
    private $devMode = true;
    private $mailer;
    private $isHTML = true;
    private $emailfolder = '..'. DIRECTORY_SEPARATOR . 'emails';

    public function __construct(\PHPMailer $mailer = null)
    {
        if(getenv('devmode') === 'false') {
            $this->setDevMode(false);
        }
        if(!is_null($mailer)) {
            $this->mailer = $mailer;
        }
        else {
            $this->mailer = new \PHPMailer();
        }
    }

    public function attachFile(string $filepath, string $name = '')
    {
        if($name != '') {
            $this->mailer->addAttachment($filepath);
        }
        else {
            $this->mailer->addAttachment($filepath, $name);
        }
    }

    public function setEmailFolder(string $string) {
        $this->emailfolder = $string;
    }

    public function out()
    {
        $this->mailer->setFrom($this->emailfrom);
        if(strpos($this->emailto, ',') !== false) {
            $emails = explode(',', $this->emailto);
            foreach ($emails as $mail) {
                $this->mailer->addAddress($mail);
            }
        }
        else {
            $this->mailer->addAddress($this->emailto);
        }
        if($this->devMode) {
            $timestamp = time();
            $folder = $this->emailfolder;
            if(!file_exists($folder)) {
                mkdir($folder);
            }
            $filename = $folder . DIRECTORY_SEPARATOR . "$timestamp-$this->emailto";
            touch($filename);
            if(file_exists($filename)) {
                $compiledemail = "";
                $compiledemail .= "To: $this->emailto\n";
                $compiledemail .= "Subject: $this->subject\n\n";
                $compiledemail .= "$this->message";
                file_put_contents($filename, $compiledemail);
            }
            else {
                throw (new ChassisException('Error: can\'t save email output'));
            }
        }
        else {
            if($this->isHTML) {
                $this->mailer->isHTML(true);
            }
            else {
                $this->mailer->isHTML(false);
            }
            $this->mailer->Subject = $this->subject;
            $this->mailer->Body = $this->message;
            $output = $this->mailer->send();
            if(!$output) {
                throw (new \Exception($this->mailer->ErrorInfo));
            }
            $mailerclass = get_class($this->mailer);
            $this->mailer = new $mailerclass;
        }
    }

    public function setEmailParams(string $emailto, string $subject = 'Email Output', string $message = '', string $emailfrom = '') {
        $this->emailto = $emailto;
        $this->emailfrom = $emailfrom;
        $this->message = $message;
        $this->subject = $subject;
    }

    public function sendPlainText() {
        $this->isHTML = false;
    }

    public function setDevMode(bool $switch = true) {
        $this->devMode = $switch;
    }
}