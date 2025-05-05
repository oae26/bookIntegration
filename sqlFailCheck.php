#!/usr/bin/php
<?php
require('projectlogging/logSender.php');
require_once('/srv/rabbitMQLib.inc');
$error = false;

$output1 = shell_exec("systemctl is-active projectlogin.service");
$output2 = shell_exec("systemctl is-active projectbooksearch.service");
$output3 = shell_exec("systemctl is-active projectrating.service");
$output4 = shell_exec("systemctl is-active projectreview.service");
$output5 = shell_exec("systemctl is-active projectgroups.service");
$output6 = shell_exec("systemctl is-active projectwatchlist.service");

if(trim($output1) != "active"){
	sendLog("Login Service Error");
	$error = true;
}
if(trim($output2) != "active"){
	sendLog("Book Service Error");
	$error = true;
}
if(trim($output3) != "active"){
	sendLog("Rating Service Error");
	$error = true;
}
if(trim($output4) != "active"){
	sendLog("Review Service Error");
	$error = true;
}
if(trim($output5) != "active"){
	sendLog("Group Service Error");
	$error = true;
}
if(trim($output6) != "active"){
	sendLog("Watchlist Service Error");
	$error = true;
}

if($error){
	$request = array();
	$client = new rabbitMQClient("/rabbitmqini/failover.ini",'sqlFail');
	$request['type'] = "fail";
	$response = $client->send_request($request);
	//shell exec stop this chronjob, stop services, start listener
	
}

?>
