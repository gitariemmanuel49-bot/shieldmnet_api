<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Shield M.Net Sales - Till 8574864</h2>";
echo "<p>DDNS: hkd0amfcjgg.sn.mynetname.net | Care: 0752115379</p><hr><pre>";
if(file_exists("sales.log")){
  echo htmlspecialchars(file_get_contents("sales.log"));
} else {
  echo "No sales yet. Sales will appear here after first M-Pesa STK payment.\n";
  echo "Test: Pay KES 5 to Till 8574864\n";
}
echo "\n\n--- Callback logs ---\n";
if(file_exists("callback_raw.log")){
  echo htmlspecialchars(file_get_contents("callback_raw.log"));
}
echo "</pre>";
?>
