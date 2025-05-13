#!/usr/bin/php
<?php
require_once('/srv/path.inc');
//require_once('/srv/get_host_info.inc');
require_once('/srv/rabbitMQLib.inc');

function sendLog($message){
	$client = new rabbitMQClient("/rabbitmqini/rabbitMQ.ini",'distLogging');
	$timestamp = date('c');
	$machine = gethostname();
	$log = "[$timestamp][$machine] $message";
	echo "Logging...";
	$request = array();
	$request['type'] = "log";
	$request['message'] = $log;
	$client->logPublish($request);
}
sendLog("Hello professor.");
?>
