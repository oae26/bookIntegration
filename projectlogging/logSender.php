#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

$client = new rabbitMQClient("../rabbitMQ.ini",'distLogging');

function sendLog($message){
	global $client;
	
	$request = array();
	$request['type'] = "log";
	$request['message'] = time() . "this is a message" . PHP_EOL;
	$client->logPublish($request);
}

$message = time() . "this is a test message" . PHP_EOL;
sendLog($message);
?>
