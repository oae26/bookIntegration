#!/usr/bin/php
<?php
//require('projectlogging/logSender.php');
require_once('/srv/rabbitMQLib.inc');
$doExit = false;

function activateServices(){
	global $doExit;
	shell_exec("systemctl restart projectlogin.service");
	shell_exec("systemctl restart projectbooksearch.service");
	shell_exec("systemctl restart projectrating.service");
	shell_exec("systemctl restart projectreview.service");
	shell_exec("systemctl restart projectgroups.service");
	shell_exec("systemctl restart projectwatchlist.service");
	
	shell_exec("systemctl restart sqlfailcheck.servive");
	shell_exec("systemctl stop sqlfaillistener.service");
	
	exit();
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
  	echo "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "fail":
    	activateServices();
  }
}

$server = new rabbitMQServer("/rabbitmqini/rabbitMQ.ini","sqlFailover");
$server->process_requests('requestProcessor');
exit();
?>
