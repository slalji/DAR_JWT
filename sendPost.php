<?php
$payload = '{
	"iss": "Selcom Transnet API",
	"method": "openAccount",
	"requestParams": {
		"transId": "580929745048",
		"customerNo": "567567556002",
		"msisdn": "255686400149",
		"amount": "100000",
		"period":"1272509157",
		"currency": "TZS"

	}
}';
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://127.0.0.1/selcomJWT/jwt_decoder.php",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode($payload),
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImlzcyI6IlNlbGNvbSBUcmFuc3NuZXQiLCJzdWIiOiJzZWxjb21AdHJhbnNzbmV0Lm5ldCIsImF1ZCI6Imh0dHBzOlwvXC90cmFuc3NldC5zZWxjb20ubmV0IiwiZXhwIjoiMjRoIn19.eyJpc3MiOiJTZWxjb20gVHJhbnNuZXQgQVBJIiwibWV0aG9kIjoicmVzZXJ2ZUFtb3VudCIsInRpbWVzdGFtcCI6IjE1Mjk5OTg3NDMiLCJyZXF1ZXN0UGFyYW1zIjp7InRyYW5zSWQiOiI1ODA5Mjk3NDUwNDgiLCJhY2NvdW50Tm8iOiI1Njc1Njc1NTYwMDIiLCJtc2lzZG4iOiIyNTU2ODY0MDAxNDkiLCJhbW91bnQiOiIxMDAwMDAiLCJwZXJpb2QiOiIxMjcyNTA5MTU3IiwiY3VycmVuY3kiOiJUWlMifX0.Hw2OrfPxULZP3RH171O1eP9_AHF5DqeTh9ET2avYvURHSUGMWCKtD9Y4LEjO9KRHoZQDiz_5RxSURU1zOQ3YGPdSUkdTlHpnMsTYA63mKr2AQ8Rz_HDjgtNuq--MB8f4DZYZd4REKoFAgGkJs2ludw2NrjZGkgSVuTlWT6cXzHw",
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