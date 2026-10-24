<?php
require_once "routeros_api.class.php";
$data = file_get_contents('php://input');
file_put_contents("callback_raw.log", date("Y-m-d H:i:s")." ".$data.PHP_EOL, FILE_APPEND);
$json = json_decode($data, true);
if(isset($json['Body']['stkCallback']['ResultCode']) && $json['Body']['stkCallback']['ResultCode']==0){
  $items = $json['Body']['stkCallback']['CallbackMetadata']['Item'];
  $amount=0;$phone="";$code="";
  foreach($items as $it){
    if($it['Name']=='Amount') $amount=$it['Value'];
    if($it['Name']=='MpesaReceiptNumber') $code=$it['Value'];
    if($it['Name']=='PhoneNumber') $phone=$it['Value'];
  }
  $map=[5=>"10MIN-2M",7=>"20MIN-2M",9=>"30MIN-3M",10=>"1HR-3M",13=>"2HRS-3M",16=>"3HRS-4M",49=>"1DAY-5M",250=>"1WEEK-5M",800=>"1MONTH-8M"];
  $profile=$map[$amount]??"1HR-3M";
  $API=new RouterosAPI();
  $MIKROTIK_USER="admin"; $MIKROTIK_PASS="1234";
  if($API->connect('hkd0amfcjgg.sn.mynetname.net', $MIKROTIK_USER, $MIKROTIK_PASS)){
    $API->comm("/ip/hotspot/user/add", ["name"=>$phone,"password"=>$code,"profile"=>$profile,"comment"=>"ShieldMNet KES $amount $code Care 0752115379"]);
    file_put_contents("sales.log", date("Y-m-d H:i:s").", $phone, $amount, $profile, $code\n", FILE_APPEND);
    $API->disconnect();
  }
}
echo json_encode(["ResultCode"=>0,"ResultDesc"=>"Shield M.Net OK"]);
?>
