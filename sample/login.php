<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('vendor/autoload.php');
use Firebase\JWT\JWT;
if (!isset($_POST))
{
	$msg = "NO POST MESSAGE SET";
	echo json_encode($msg);
	exit(0);
}
$request = $_POST;

$username = $_POST['username'] ?? ' ';
$password = $_POST['password'] ?? ' ';
$sessionKey = $_POST['sessionKey'] ?? ' ';
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
	try{
		if($RMQresponse["returnCode"] === "0"){
			$key = (string)$RMQresponse["sessionKey"];

			$payload = [
				"username" => $RMQresponse["message"],
				"sessionKey" => $RMQresponse["sessionKey"],
				"expireTime" => time() + 3600,
			];
			$jwt = JWT::encode($payload, $key, 'HS256');
			echo json_encode($jwt);
			}
	}catch(Exception $e){
		error_log("Something failed, whoopsie");
		echo "something went wrong" . $e -> getMessage();
	}
	break;
	case "logout":
		$RMQrequest = array();
		$RMQrequest['type'] = 'logout';
		$RMQrequest['username'] = $username;
		$RMQrequest['sessionKey'] = (int)$sessionKey;
		$RMQresponse = $client -> send_request($RMQrequest);
		echo json_encode($RMQresponse);
	break;
	case "validate_session":
		$RMQrequest = array();
		$RMQrequest['type'] = 'validate_session';
		$RMQrequest['username'] = $username;
		$RMQrequest['sessionKey'] = (int) $sessionKey;
		$RMQresponse = $client -> send_request($RMQrequest);
		echo json_encode($RMQresponse);
	break;
	case "register":
		
		$RMQrequest = array();
		$RMQrequest['type'] = 'register';
		$RMQrequest['username'] = $username;
		$RMQrequest['password'] = $password;
		$RMQresponse = $client -> send_request($RMQrequest);
		echo json_encode($RMQresponse);
	}
	exit(0);

?>
