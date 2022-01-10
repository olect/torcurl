<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';

use \Fucx\TorCurl\Curl;
use \Fucx\TorCurl\ProxyConfig;

$proxyConfig = new ProxyConfig();
//$proxyConfig->verbose();

$curl = Curl::init('http://ip-api.com/json', $proxyConfig);
$myLocation = json_decode($curl->get());

echo "====================================\n";
echo "My IP Address is: $myLocation->query\n";
echo "Country: $myLocation->country\n";
echo "City/Zip: $myLocation->city, $myLocation->city\n";
echo "====================================\n";