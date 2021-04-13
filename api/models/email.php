<?php

namespace Models;

// Email
require_once "vendor/ultimate-email/email_builder.php";
require_once "vendor/ultimate-email/smtp.php";

class Email{

    // object properties
    public $host;
    public $username;
    public $password;
    public $secure;
    public $port;
    public $smtpoptions;

    // constructor
    public function __construct(){
        $this->host = \Core\Config::read('em.host');
        $this->username = \Core\Config::read('em.user');
        $this->password = \Core\Config::read('em.password');
        $this->secure = \Core\Config::read('em.host');
        $this->port = \Core\Config::read('em.port');


    }

    public function send() {

        $toaddr = "nsc@scpgwiki.com";
        $body = "<html><body>Your message goes here</body></html>";
        $fromaddr = \Core\Config::read('em.replyto');
        $subject = "Thanks for signing up!";

        $smtpoptions = array(
            "headers" => \SMTP::GetUserAgent("Thunderbird"),
            "server" => $this->host,
            "port" => $this->port,
            "secure" => $this->secure,
            "username" => $this->username,
            "password" => $this->password,
            "htmlmessage" => $body,
            "textmessage" => \SMTP::ConvertHTMLToText($body),
        );

        return \SMTP::SendEmail($fromaddr, $toaddr, $subject, $smtpoptions);

    }

}