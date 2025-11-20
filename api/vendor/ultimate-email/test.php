<?php
	require_once "./smtp.php";
	require_once "./pop3.php";
    require_once "./email_builder.php";
    require_once '../autoload.php';

	$toaddr = "neil.carthy42@gmail.com";
	$body = "<html><body>Your message goes here</body></html>";

	// Send the e-mail to the user.
	// Change the stuff in '[]' to your server settings.
	$smtpoptions = array(
		"headers" => SMTP::GetUserAgent("Thunderbird"),
		"htmlmessage" => $body,
		"textmessage" => SMTP::ConvertHTMLToText($body),
		"server" => "uk1.cp.netnerd.com",
		"port" => 465,
		"secure" => true,
		"username" => "<< REMOVED >>",
		"password" => "<< REMOVED >>" # The password that was here has been revoked
	);

	$pop3options = array(
		"server" => "uk1.cp.netnerd.com",
		"port" => 995,
		"secure" => true
	);

    $r = new Net_DNS2_Resolver(array('nameservers' => array('8.8.8.8')));    

    try
    {
        $result = $r->query('google.com', 'A');     

        foreach($result->answer as $record)
        {
            echo $record->address, "\n";
        }

    } catch(Net_DNS2_Exception $e)  
    {
        echo "::query() failed: ", $e->getMessage(), "\n";    
    }

    // Write normal CSS.
	$styles = array(
		"a" => "text-decoration: none;",
		"#headerwrap" => "font-size: 0; line-height: 0;",
		"#contentwrap" => "font-family: Helvetica, Arial, sans-serif; font-size: 18px; line-height: 27px; color: #333333;",
		"#contentwrap a:not(.bigbutton)" => "color: #4E88C2;",
		"#contentwrap a.bigbutton" => "font-family: Helvetica, Arial, sans-serif; font-size: 18px; line-height: 27px; color: #FEFEFE;",
		"#footerwrap" => "font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 21px; color: #F0F0F0;",
		"#footerwrap a" => "color: #CCCCCC;"
	);

	$content = array(
		// Header.
		array(
			"type" => "layout",
			"id" => "headerwrap",
//			"table-bgcolor" => "#FF0000",
			"width" => 600,
			"content" => array(
				array(
					"type" => "image",
					"width" => 600,
					"src" => "https://github.com/cubiclesoft/ultimate-email/blob/master/test_suite/test_newsletter_header.png",
//					"file" => "./test_newsletter_header.png"
				)
			)
		),

		// Main content.
		array(
			"type" => "layout",
			"width" => 600,
			"content" => array(
				array(
					"type" => "layout",
					"id" => "contentwrap",
					"width" => "90%",
					"content" => array(
						array("type" => "space", "height" => 1),

						"<p>Hello valued humanoid!</p>",

						// Float an image to the right.
						array(
							"type" => "layout",
//							"table-width" => "35%",
							"table-align" => "right",
							"width" => 100,
							"padding" => array(5, 0, 20, 20),
							"content" => array(
								array(
									"type" => "image",
									"width" => 100,
									"src" => "https://placekitten.com/100/80",
									"alt" => "Cat"
								)
							)
						),

						"<p>Blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah</p>",
						"<p>Blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah</p>",
						"<p><a href=\"#\">Blah blah blah</a></p>",
						"<p>Blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah</p>",

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
						"<p>Blah blah blah</p>",

						array("type" => "space", "height" => 1),
					)
				)
			)
		),

		// Footer.
		array(
			"type" => "layout",
			"width" => 600,
			"bgcolor" => "#182434",
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
		)
	);

	$result = EmailBuilder::Generate($styles, $content);
	file_put_contents('php://stderr', print_r($result["html"], TRUE));
    $smtpoptions = array(
		"headers" => SMTP::GetUserAgent("Thunderbird"),
		"htmlmessage" => $result["html"],
		"textmessage" => SMTP::ConvertHTMLToText($result["html"]),
		"server" => "uk1.cp.netnerd.com",
		"port" => 465,
		"secure" => true,
		"username" => "member_admin+knightsbridgeassociation.com",
		"password" => "K*-ESnyw]C6F7*JD"
	);


	$fromaddr = "membership@knightsbridgeassociation.com";
	$subject = "Thanks for signing up!";
	$result = SMTP::SendEmail($fromaddr, $toaddr, $subject, $smtpoptions);
	if (!$result["success"])
	{
		// This is usually the correct thing
		// to do to implement POP-before-SMTP.
		if ($smtpoptions["username"] != "" && $smtpoptions["password"] != "")
		{
			$pop3 = new POP3;
			$result = $pop3->Connect($smtpoptions["username"], $smtpoptions["password"], $pop3options);
			if ($result["success"])
			{
				$pop3->Disconnect();

				$result = SMTP::SendEmail($fromaddr, $toaddr, $subject, $smtpoptions);
			}
		}

		if (!$result["success"])
		{
			echo "Failed to send e-mail.\n";

			exit();
		}
	}