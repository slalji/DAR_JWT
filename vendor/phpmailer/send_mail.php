<?php

function send_email($toemail, $toname, $subject, $body){


	//include phpmailer
	require_once('vendor/phpmailer/class.phpmailer.php');

	//SMTP Settings
	$mail = new PHPMailer(true);
	//$mail->SetLanguage("en", 'phpmailer/language/');
	$mail->SetLanguage("en", 'language');

	$mail->IsSMTP();
	$mail->Mailer = "smtp";
	$mail->SMTPAuth   = true;
	//$mail->SMTPSecure = "tls";

  $mail->Host       = "smtp.mailtrap.io";
  //$mail->Host       = "smtp.gmail.com";
	//$mail->Port = 465;
  $mail->Username   = "60c327adad8cbd";
  $mail->Password   = "74c53ac6371764";


	$mail->SetFrom('no-reply@selcom.net', 'Selcom API'); //from (verified email address)
	$mail->Subject = $subject; //subject


	//$body = preg_replace("/[\\]/",'',$body);
	$mail->MsgHTML($body);
	//

	//recipient
	$mail->AddAddress($toemail, $toname);

	return $mail->Send();
}