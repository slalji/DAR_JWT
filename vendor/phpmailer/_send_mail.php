<?php


function send_email($toemail, $toname, $subject, $body){


	//include phpmailer
	require_once('class.phpmailer.php');

	//SMTP Settings
	$mail = new PHPMailer(true);
	//$mail->SetLanguage("en", 'phpmailer/language/');
	$mail->SetLanguage("en", 'language');

	$mail->IsSMTP();
	$mail->Mailer = "smtp";
	$mail->SMTPAuth   = true;
	$mail->SMTPSecure = "tls";

	$mail->Host       = "email-smtp.us-east-1.amazonaws.com";
	//$mail->Port = 465;
	$mail->Username   = "AKIAIA5CT6HJXO64FTDQ";
	$mail->Password   = "Av3xchdaVqcV/YfH7fmVePGgUcCfJD+F3RtxFXBeoh2L";
	//

	$mail->SetFrom('no-reply@selcom.net', 'Selcom Settlement'); //from (verified email address)
	$mail->Subject = $subject; //subject


	//$body = preg_replace("/[\\]/",'',$body);
	$mail->MsgHTML($body);
	//

	//recipient
	$mail->AddAddress($toemail, $toname);

	$mail->Send();
}


?>
