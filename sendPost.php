<?php

require_once ("jwt_encode.php");
require_once ("DB.php");

$payload = '{
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "openAccount",
	"requestParams": {
		"transid": "01052018161000",
		"firstName": "David",
		"lastName": "Beckham",
		"addressCity": "Tunduma",
		"addressCountry": "Tanzania",
		"dob": "1987-04-10",
		"currency": "TZS",
		"customerNo": "255754200204",
		"msisdn": "255754200204"
		
	}
}';
$updateAccount =' {
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "updateAccount",
	"requestParams": {
		"transid": "01052018161212",
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
    "toAccountNo": "255754200200",
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
    "toAccountNo": "255754200200",
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
    "msisdn": "255789654700",
    "accountNo": "10"
  }
}';
$transactionLookup='{ 
	"iss": "Selcom Transsnet API",
	"timestamp": "2018-07-06 12:14:33",
	"method": "transactionLookup",
	"requestParams": {
		"transid": "'.DB::getToken(12).'",
    "transref": "112217916212",
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
    "msisdn": "255789654555",
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
    "msisdn": "255789654555",
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
		"statustxt": "close",
		"accountNo": "10",
		"msisdn": "255789654555"
		
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
    "search": "\"responseCode\":501",
    "name": "Salma Kanji Lalji",
    "msisdn": "255789654700",
    "accountNo": "10"
		
	}
}';
$cashin = '{
  "iss": "Selcom Transsnet API",
  "timestamp": "2018-07-06 12:14:33",
  "method": "cashin",
  "requestParams": {
    "transid": "'.DB::getToken(12).'",
    "msisdn": "255789654700",
    "accountNo": "10",
    "utilityref":"255789654555",
    "amount":"10",
    "currency":"TZS"
  }
}';


$bearer = Token::sign($payload);


$curl = curl_init(); 
curl_setopt_array($curl, array(
  CURLOPT_URL => "http://127.0.0.1/selcomTranssnet/",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10, 
  CURLOPT_TIMEOUT => 30,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => /*payload*/($cashin),
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