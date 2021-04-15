<?php

namespace Models;

// Email
require_once "vendor/ultimate-email/email_builder.php";
require_once "vendor/ultimate-email/smtp.php";

class Email{

    // object properties
    protected $smtpOptions;
    public $toAddress;
    public $salutation;
    public $fromAddress;
    public $fromName;
    public $fromTitle;
    public $goCardlessLink;
    public $subject;

    // constructor
    public function __construct(){

        $password = getenv(\Core\Config::read('em.password_envkeyname'));

        $this->smtpOptions = array(
            "headers" => \SMTP::GetUserAgent("Thunderbird"),
            "server" => \Core\Config::read('em.host'),
            "port" => \Core\Config::read('em.port'),
            "secure" => \Core\Config::read('em.secure'),
            "username" => \Core\Config::read('em.user'),
            "password" => $password,
        );
    }

    public function send() {

        $this->smtpOptions['htmlmessage'] = $this->body;
        $this->smtpOptions['textmessage'] = \SMTP::ConvertHTMLToText($this->body);

        $result = \SMTP::SendEmail($this->fromAddress, $this->toAddress, $this->subject, $this->smtpOptions);
        if (!$result["success"]) {
            return false;
        } else {
            return true;
        }        
    }

    public function prepare_reminder() {        

        $content = array();
        array_push($content, $this->header());
        array_push($content, $this->reminder_content());
        array_push($content, $this->footer());

        $result = \EmailBuilder::Generate($this->styles(), $content);      
        if (!$result["success"]) {
            return false;
        } else {
            $this->body = $result['html'];
            $altered_body = preg_replace('/[\s\t\n\r]{2,}/', '', $result['html']);
            return true;
        } 
    }

    public function prepare_switchrequest() {        

        $content = array();
        array_push($content, $this->header());
        array_push($content, $this->switchrequest_content());
        array_push($content, $this->footer());

        $result = \EmailBuilder::Generate($this->styles(), $content);      
        if (!$result["success"]) {
            return false;
        } else {
            $this->body = $result['html'];
            $altered_body = preg_replace('/[\s\t\n\r]{2,}/', '', $result['html']);
            return true;
        } 
    }

    private function reminder_content() {

        $top_level = array(
			"type" => "layout",
			"width" => 600,
            //"padding" => 30,
			"content" => array(
                array(
                    "type" => "layout",
                    "id" => "contentwrap",
                    "width" => "90%",
                    "content" => array()
                )
            )
        );

        $top_level['content']['0']['content'] = array(
            array("type" => "space", "height" => 1),
            "<p>{$this->salutation}</p>",
            "<p>Your annual membership of the Knightsbridge Association is now due for renewal. We are very grateful for your support and hope that you will renew your subscription by one of the methods given below.</p>",
            // Display a clickable link as a centered button (e.g. a call to action).
            array(
                "type" => "layout",
                "align" => "center",
                "content" => array(
                    array(
                        "type" => "button",
                        "href" => "{$this->goCardlessLink}",
                        "class" => "bigbutton",
                        "bgcolor" => "#4E88C2",
                        "padding" => array(12, 18),
                        "text" => "Pay By Direct Debit"
                    )
                )
            ),
            "<p>We also accept payment by bank transfer. Please give your name/company name as a reference. Our bank details are : The Knightsbridge Association, sort code 40-04-10, account number 31595180.</p>",
            "<p>If you wish to pay by cheque, please send your cheque to The Knightsbridge Association, 6 Montpelier Street, London, SW7 1EZ.</p>",
            "<p>Membership Fees are as follows: Individual £20, Family or Household £30 and Corporate £40.</p>",
            "<p>Without your support we cannot continue our mission of maintaining Knightsbridge as a vibrant and welcoming place to live and work.</p>",

            // Spacer
            array("type" => "space", "height" => 20),

            "<p>Yours Sincerely,</p>",
            "<p>{$this->fromName}</p>",
            "<p>{$this->fromTitle}</p>",

            array("type" => "space", "height" => 5)
        );

        return $top_level;
    }

    private function switchrequest_content() {

        $top_level = array(
			"type" => "layout",
			"width" => 600,
            //"padding" => 30,
			"content" => array(
                array(
                    "type" => "layout",
                    "id" => "contentwrap",
                    "width" => "90%",
                    "content" => array()
                )
            )
        );

        $top_level['content']['0']['content'] = array(
            array("type" => "space", "height" => 1),
            "<p>{$this->salutation}</p>",
            "<p>Your annual membership of the Knightsbridge Association is now due for renewal. We are very grateful for your support and hope that you will renew your subscription by one of the methods given below.</p>",
            // Display a clickable link as a centered button (e.g. a call to action).
            array(
                "type" => "layout",
                "align" => "center",
                "content" => array(
                    array(
                        "type" => "button",
                        "href" => "{$this->goCardlessLink}",
                        "class" => "bigbutton",
                        "bgcolor" => "#4E88C2",
                        "padding" => array(12, 18),
                        "text" => "Pay By Direct Debit"
                    )
                )
            ),
            "<p>We also accept payment by bank transfer. Please give your name/company name as a reference. Our bank details are : The Knightsbridge Association, sort code 40-04-10, account number 31595180.</p>",
            "<p>If you wish to pay by cheque, please send your cheque to The Knightsbridge Association, 6 Montpelier Street, London, SW7 1EZ.</p>",
            "<p>Membership Fees are as follows: Individual £20, Family or Household £30 and Corporate £40.</p>",
            "<p>Without your support we cannot continue our mission of maintaining Knightsbridge as a vibrant and welcoming place to live and work.</p>",

            // Spacer
            array("type" => "space", "height" => 20),

            "<p>Yours Sincerely,</p>",
            "<p>{$this->fromName}</p>",
            "<p>{$this->fromTitle}</p>",

            array("type" => "space", "height" => 5)
        );

        return $top_level;
    }

    private function styles() {
        return array(
            "a" => "text-decoration: none;",
            "#headerwrap" => "font-family: Helvetica, Arial, sans-serif; font-size: 28px; line-height: 45px;  background-color: #CCB29D;",
            "#headerwrap p" => "padding-left: 5%;",
            "#contentwrap" => "font-family: Helvetica, Arial, sans-serif; font-size: 18px; line-height: 27px; color: #333333;",
            "#contentwrap a:not(.bigbutton)" => "color: #4E88C2;",
            "#contentwrap a.bigbutton" => "font-family: Helvetica, Arial, sans-serif; font-size: 18px; line-height: 27px; color: #FEFEFE;",
            "#footerwrap" => "font-family: Helvetica, Arial, sans-serif; font-size: 12px; line-height: 18px; color: #F0F0F0; text-align: center;",
            "#footerwrap a" => "color: #CCCCCC;"
        );
    }

    private function header() {
        return array(
			"type" => "layout",
			"id" => "headerwrap",
//			"table-bgcolor" => "#FF0000",
			"width" => 600,
			"content" => array(
				"<p><strong>KNIGHTSBRIDGE</strong> ASSOCIATION</p>",
			)
        );
    }

    private function footer() {

        return array(
			"type" => "layout",
			"width" => 600,
			"bgcolor" => "#11477B",
			"content" => array(
				array(
					"type" => "layout",
					"id" => "footerwrap",
					"width" => "90%",
					"content" => array(
						array("type" => "space", "height" => 1),

						"<p><a href=\"https://www.knightsbridgeassociation.com/\">Knightsbridge Association</a></p>",
                        "<p>6 Montpelier Street, London, SW7 1EZ</p>",
						"<p><a href=\"mailto:membership@knightsbridgeassociation.com?subject=Unsubscribe\">Unsubscribe</a></p>",
						

						array("type" => "space", "height" => 1),
					)
				)
			)
        );
    }

}