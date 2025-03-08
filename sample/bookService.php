<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('vendor/autoload.php');

if (!isset($_POST))
{
	$msg = "NO POST MESSAGE SET";
	echo json_encode($msg);
	exit(0);
}
$request = $_POST;

$title= $_POST['title'] ?? ' ';

try{
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
} catch(Exception $e){
	error_log("RabbitMQClient error, could not connect" . $e ->getMessage());
	die("error, please see log for deets, bye");

}
switch ($request["type"])
{
	case "booksearch":
	$RMQrequest = array();
	$RMQrequest['type'] = 'booksearch';
	$RMQrequest['title'] = $title;
    $RMQresponse = $client -> send_request($RMQrequest);
	echo json_encode($RMQresponse);
    }
	exit(0);

?>