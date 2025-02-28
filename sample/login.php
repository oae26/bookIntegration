<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if (!isset($_POST))
{
	$msg = "NO POST MESSAGE SET, POLITELY FUCK OFF";
	echo json_encode($msg);
	exit(0);
}
$request = $_POST;

$username = $_POST['username'] ?? ' ';
$password = $_POST['password'] ?? ' ';
try{
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
} catch(Exception $e){
	error_log("RabbitMQClient error, could not connect" . $e ->getMessage());
	die("error, please see log for deets, bye");

}

$response = "unsupported request type, politely FUCK OFF";

switch ($request["type"])
{
	case "login":
	$RMQrequest = array();
	$RMQrequest['type'] = 'login';
	$RMQrequest['username'] = $username;
	$RMQrequest['password'] = $password;
	$RMQresponse = $client -> send_request($RMQrequest);
	}
	
	$json = json_encode($RMQresponse);
	var_dump($json);
	return($json);
	exit(0);

?>
