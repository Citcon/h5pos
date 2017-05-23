<?php
$price = $_POST["price"];
$tip = $_POST["tip"];
$code = $_POST["code"];
$pos_local_time=$_POST["pos_local_time"];

$parameters = 
    'total='.$price.
    '&tip='.$tip.
    '&currency=USD'.
    '&token=555'.
    '&barcode='.$code.
    '&pos_local_time='.$pos_local_time;

//error_log(">>> $parameters");
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://dev.citconpay.com/posp/rest/pay');
//curl_setopt($curl, CURLOPT_GET, 1);
curl_setopt($curl,CURLOPT_POST, 7);
curl_setopt($curl,CURLOPT_POSTFIELDS, $parameters);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($curl);
curl_close($curl);
echo $result;?>
