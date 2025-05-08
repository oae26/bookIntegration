#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

function sendLog($message){
	$client = new rabbitMQClient("/rabbitmqini/rabbitMQ.ini",'distLogging');;
	$timestamp = date('c');
	$machine = gethostname();
	$log = "[$timestamp][$machine] $message";
	
	$request = array();
	$request['type'] = "log";
	$request['message'] = $log;
	$client->logPublish($request);
}

sendLog("NEW MESSAGE!!! YEAAAH!!");
?>
