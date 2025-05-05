#!/usr/bin/php
<?php
require('projectlogging/logSender.php');
require_once('/srv/rabbitMQLib.inc');


function activateServices(){

}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
  	return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "fail":
    	return doLogin($request['username'],$request['password']);
  }
}

$server = new rabbitMQServer("/rabbitmqini/rabbitMQ.ini","sqlFail");
$server->process_requests('requestProcessor');

exit();
?>
