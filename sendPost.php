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

if (send_email( 'salma@selcom.net', 'recepient name', 'reverse funds', 'reverse transaction ref num:' ))
 echo "sent";
else
  echo "Error";

die();
require_once ("jwt_encode.php");
require_once ("DB.php");


$openAccount ='{
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "openAccount",
	"requestParams": {
    "transid": "'.DB::getToken(12).'",
		"firstName": "David",
		"lastName": "Beckham",
		"addressCity": "Iringa",
		"addressCountry": "Tanzania",
		"dob": "1977-01-10",
		"currency": "TZS",
		"customerNo": "255789654204",
		"msisdn": "255789654204"
		
	}
}
';
$updateAccount =' {
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "updateAccount",
	"requestParams": {
    "transid": "'.DB::getToken(12).'",
		"addressCity": "Dodoma",
		"dob": "1998-01-10",
		"customerNo": "255789654700",
    "msisdn": "255789654700",
    "accountNo": "10"

	}
}
';

$transferFunds='{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "fundTransfer",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "utilityref": "255789654555",
    "amount": "10",
    "accountNo": "10",
    "currency": "TZS"
  }
}
';
$transfundsWithinfo='{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "fundTransfer",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "accountNo": "10",
    "utilityref": "255754200200",
    "transtype": "fee",
    "geocode": {"lat":"-6.802353","lng":"39.279556"},
    "amount": "10",
    "currency": "TZS"
  }
}';

$nameLookup='{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "nameLookup",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654555",
    "accountNo": "9"
  }
}';
$transactionLookup='{
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "transactionLookup",
	"requestParams": {
		"transid": "'.DB::getToken(12).'",
    "transref": "411053821712",
    "msisdn": "255789654700",
    "accountNo": "10"
	}
}';

$checkBalance='
{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "checkBalance",
  "requestParams": {
    "transid": "010520181610210",
    "msisdn": "255789654700",
    "accountNo": "10"
  }
}

';
$getStatement='
{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "getStatement",
  "requestParams": {
    "transid": "010520181610210",
    "msisdn": "255789654700",
    "accountNo": "10"
  }
}';

$reserveAccount='
{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "reserveAccount",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654700",
    "customerNo": "255789654700",
    "amount":"10",
    "currency":"TZS"
  }
}';
$unReserveAccount='
{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "unReserveAccount",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654700",
    "reference":"011504192621",
    "customerNo": "255789654700",
    "amount":"10",
    "currency":"TZS"
  }
}';

$changeState ='
{
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "changeStatus",
	"requestParams": {
    "transid": "'.DB::getToken(12).'",
		"statustxt": "open",
		"accountNo": "10"
	}
}';
$requestCard ='
{
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "requestCard",
	"requestParams": {
    "transid": "'.DB::getToken(12).'",
    "name": "Salma Kanji Lalji",
    "msisdn": "255789654700",
    "accountNo": "10"

	}
}';
$search ='
{
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "search",
	"requestParams": {
    "transid": "'.DB::getToken(12).'",
    "search": "transferFunds"

	}
}';
$cashin = '{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "cashin",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654700",
    "amount":"10"
  }
}';

$payutility = '{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "payutility",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654700",
    "utilitycode":"AZAMTV",
    "utilityref": "255789654700",
    "amount":"10"
  }
}';
$test =' {
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "openAccount",
  "requestParams": {
  "addressCity": "shenzhen",
"addressCountry": "china",
"addressLine1": "shenzhen",
"currency": "TZS",
"firstName": "yin",
"lastName": "qi",
"msisdn": "255758238772",
"transid": "99991532920151320"
}
}';
$data =  $openAccount ;
$bearer = Token::sign($data);


$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "http://127.0.0.1/transsnet/",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    "content-type:application/json",
    "authorization: Bearer " . $bearer,
    "cache-control: no-cache",
    "content-type: application/json"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}