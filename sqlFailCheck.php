#!/usr/bin/php
<?php
//require('/srv/projectlogging/logSender.php');
require_once('/srv/rabbitMQLib.inc');
$error = false;

while(true){
	$outputListener = shell_exec("systemctl is-active sqlfaillistener.service");
	if(trim($outputListener) == "active"){
		sleep(5);
		continue;
	}
	else{
		$output1 = shell_exec("systemctl is-active projectlogin.service");
		$output2 = shell_exec("systemctl is-active projectbooksearch.service");
		$output3 = shell_exec("systemctl is-active projectrating.service");
		$output4 = shell_exec("systemctl is-active projectreview.service");
		$output5 = shell_exec("systemctl is-active projectgroups.service");
		$output6 = shell_exec("systemctl is-active projectwatchlist.service");

		if(trim($output1) != "active"){
			//sendLog("Login Service Error");
			$error = true;
			echo("Login Service Error");
		}
		if(trim($output2) != "active"){
			//sendLog("Book Service Error");
			$error = true;
			echo("Book Service Error");
		}
		if(trim($output3) != "active"){
			//sendLog("Rating Service Error");
			$error = true;
			echo("Rating Service Error");
		}
		if(trim($output4) != "active"){
			//sendLog("Review Service Error");
			$error = true;
			echo("Review Service Error");
		}
		if(trim($output5) != "active"){
			//sendLog("Group Service Error");
			$error = true;
			echo("Group Service Error");
		}
		if(trim($output6) != "active"){
			//sendLog("Watchlist Service Error");
			$error = true;
			echo("Watchlist Service Error");
		}

		if($error){
			$request = array();
			$client = new rabbitMQClient("/rabbitmqini/failover.ini",'sqlFail');
			$request['type'] = "fail";
			//$response = $client->send_request($request);
			$client->publish($request);
			//shell exec stop this service, stop project services, start listener
			shell_exec("systemctl stop projectlogin.service");
			shell_exec("systemctl stop projectbooksearch.service");
			shell_exec("systemctl stop projectrating.service");
			shell_exec("systemctl stop projectreview.service");
			shell_exec("systemctl stop projectgroups.service");
			shell_exec("systemctl stop projectwatchlist.service");
			
			shell_exec("systemctl start sqlfaillistener.service");
			shell_exec("systemctl stop sqlfailcheck.service");
			
			exit();
		}
		else{
			sleep(5);
		}
	}
}

?>
