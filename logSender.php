#!/usr/bin/php
<?php
require_once('path.inc');
require_once('rabbitMQLib.inc');
function log_event($message){
    $logClient = new rabbitMQClient("rabbitMQ.ini","distlogging");

$timestamp = date('c');

$machine = gethostname();

$logFormat = "[$timestamp][$machine] $message";


$request = array();
$request['type'] = "log";
$request['message'] = $logFormat;
$request.setReplyTo(NULL);
$response = $logClient->send_request($request);
$response = $logClient->publish($request);

print_r($response);
}

$logMessage= "test log";

log_event($logMessage);
