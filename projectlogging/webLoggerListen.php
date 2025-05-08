#!/usr/bin/php
<?php
//require_once('/srv/path.inc');
//require_once('/srv/get_host_info.inc');
//require_once('/srv/rabbitMQLib.inc');

//uncomment for local testing
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

$logFile = fopen("log.txt", "a");

function requestProcessor($request)
{
	global $logFile;
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
  	return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "log":
    	echo "Trying to write out: " . $request['message'] . PHP_EOL;
	fwrite($logFile, $request['message'] . "\n");
	return array('returnCode' => 0, 'message'=>'Message logged');
  }
}

$server = new rabbitMQServer("/rabbitmqini/rabbitMQ.ini","webLogging");
$server->process_requests('requestProcessor');
exit();

?>
