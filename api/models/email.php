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

        $password = getenv(\Core\Config::read('em.password_envkeyname'));

        $smtpoptions = array(
            "headers" => \SMTP::GetUserAgent("Thunderbird"),
            "server" => \Core\Config::read('em.host'),
            "port" => \Core\Config::read('em.port'),
            "secure" => \Core\Config::read('em.secure'),
            "username" => \Core\Config::read('em.user'),
            "password" => $password,
//            "htmlmessage" => $body,
//            "textmessage" => \SMTP::ConvertHTMLToText($body),
        );
    }

    public function send() {

        $toaddr = "nsc@scpgwiki.com";
        $body = "<html><body>Your message goes here</body></html>";
        $fromaddr = \Core\Config::read('em.replyto');
        $subject = "Thanks for signing up!";

        $content = array();
        array_push($content, $this->header());
        array_push($content, $this->main_content('Mr. Smith', 'Olivia Cox'));
        array_push($content, $this->footer());

        $body = \EmailBuilder::Generate($this->styles(), $content);

        $this->smtpoptions['htmlmessage'] = $body;
        file_put_contents('php://stderr', print_r($body, TRUE));
        $this->smtpoptions['textmessage'] = \SMTP::ConvertHTMLToText($body);

        return \SMTP::SendEmail($fromaddr, $toaddr, $subject, $this->smtpoptions);

    }

    private function main_content($to, $from, $goCardlessLink) {

        $top_level = array(
			"type" => "layout",
			"width" => 600,
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
            "<p>Dear {$to},</p>",
            $this->floating_image(),
            "<p>Your annual membership of the Knightsbridge Association is now due for renewal. We are very grateful for your support and hope that you will renew your subscription by one of the methods given below.</p>",
            // Display a clickable link as a centered button (e.g. a call to action).
            array(
                "type" => "layout",
                "align" => "center",
                "content" => array(
                    array(
                        "type" => "button",
                        "href" => "{$goCardlessLink}",
                        "class" => "bigbutton",
                        "bgcolor" => "#4E88C2",
                        "padding" => array(12, 18),
                        "text" => "Take the survey"
                    )
                )
            ),
            "<p>Blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah</p>",
            "<p><a href=\"#\">Blah blah blah</a></p>",
            "<p>Bank transfer : If you use online banking, you may wish to make a bank transfer. Please give your name/company name as a reference. Our bank details are : The Knightsbridge Association. HSBC - sort code 40-04-10, Account No : 31595180.</p>",

            // Display a clickable link as a centered button (e.g. a call to action).
            array(
                "type" => "layout",
                "align" => "center",
                "content" => array(
                    array(
                        "type" => "button",
                        "href" => "#",
//									"target" => "_blank",
                        "class" => "bigbutton",
                        "bgcolor" => "#4E88C2",
                        "padding" => array(12, 18),
                        "text" => "Take the survey"
                    )
                )
            ),

            // Spacers and a splitter.
            array("type" => "space", "height" => 20),
            array("type" => "split", "bgcolor" => "#F0F0F0"),
            array("type" => "space", "height" => 5),

            "<p>Sincerely,</p>",
            "<p>{$from}</p>",
            "<p>Treasurer</p>",

            array("type" => "space", "height" => 1),
        );

        return $top_level;
    }

    // Float an image to the right.
    private function floating_image() {
        return array(
                "type" => "layout",
                "table-align" => "right",
                "width" => 100,
                "padding" => array(5, 0, 20, 20),
                "content" => array(
                    array(
                        "type" => "image",
                        "width" => 100,
                        "src" => "https://admin.knightsbridgeassociation.co.uk/assets/images/marque.png",
                        "alt" => "KA marque"
                    )
                )
                );
    }

    private function styles() {
        return array(
            "a" => "text-decoration: none;",
            "#headerwrap" => "font-size: 0; line-height: 0; background-color: #CCB29D;",
            "#contentwrap" => "font-family: Helvetica, Arial, sans-serif; font-size: 18px; line-height: 27px; color: #333333;",
            "#contentwrap a:not(.bigbutton)" => "color: #4E88C2;",
            "#contentwrap a.bigbutton" => "font-family: Helvetica, Arial, sans-serif; font-size: 18px; line-height: 27px; color: #FEFEFE;",
            "#footerwrap" => "font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 21px; color: #F0F0F0;",
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
				array(
					"type" => "image",
					"width" => 400,
					"src" => "https://admin.knightsbridgeassociation.co.uk/assets/images/logo_white.png",
//					"file" => "./test_newsletter_header.png"
				)
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