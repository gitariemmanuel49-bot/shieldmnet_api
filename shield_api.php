<?php
$CONSUMER_KEY = "FcZBIcM1A3Ge7L1ZNjBUeoie8wWc2j8OS393mSPpikNsaKi1";
$CONSUMER_SECRET = "VzGyyxbKlAw4ETQKtutPE63QeSdSsH2CJTBGHAQQ5WYwZ5MnE0u3QTAGyB0ThgJu";
$TILL_NUMBER = "8574864";
$PASSKEY = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
$RENDER_URL = "https://shieldmnet-api-1.onrender.com";
$CALLBACK_URL = $RENDER_URL."/callback.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$phone = $_GET['phone'] ?? ''; $amount = $_GET['amount'] ?? ''; $pkg = $_GET['pkg'] ?? '';
if(strlen($phone)==10 && $phone[0]=='0'){ $phone='254'.substr($phone,1); }
if(!$phone || !$amount){ echo json_encode(["error"=>"missing data"]); exit; }

$ch = curl_init("https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
curl_setopt($ch, CURLOPT_USERPWD, $CONSUMER_KEY.":".$CONSUMER_SECRET);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = curl_exec($ch); $token = json_decode($res)->access_token ?? ''; curl_close($ch);
if(!$token){ echo json_encode(["error"=>"Token failed","raw"=>$res]); exit; }

$timestamp = date("YmdHis"); $password = base64_encode($TILL_NUMBER.$PASSKEY.$timestamp);
$payload = ["BusinessShortCode"=>$TILL_NUMBER,"Password"=>$password,"Timestamp"=>$timestamp,"TransactionType"=>"CustomerBuyGoodsOnline","Amount"=>(int)$amount,"PartyA"=>$phone,"PartyB"=>$TILL_NUMBER,"PhoneNumber"=>$phone,"CallBackURL"=>$CALLBACK_URL,"AccountReference"=>"Shield-".$pkg,"TransactionDesc"=>"Shield M.Net WiFi"];
$ch = curl_init("https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token","Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch); curl_close($ch); echo $response;
file_put_contents("payments.log", date("Y-m-d H:i:s")." | $phone | $amount | $pkg | $response\n", FILE_APPEND);
?>
