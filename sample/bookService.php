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
$review = $_POST['review'] ?? ' ';
$userID = $_POST['userID'] ?? ' ';
$bookID = $_POST['bookID'] ?? ' ';
$rating = $_POST['rating'] ?? ' ';
$groupName = $_POST['groupName'] ?? ' '; 
$ownerID = $_POST['ownerID'] ?? ' ';
try {
	$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
} catch (Exception $e) {
	error_log("RabbitMQClient error, could not connect: " . $e->getMessage());
	echo json_encode(["error" => "Could not connect to RabbitMQ"]);
	exit(0);
}
switch ($request["type"])
{	default:
	echo json_encode(["error" => "Invalid request type"]);
	exit(0);
	case "booksearch":
	$RMQrequest = array();
	$RMQrequest['type'] = 'booksearch';
	$RMQrequest['title'] = $title;
    $RMQresponse = $client -> send_request($RMQrequest);
	echo json_encode($RMQresponse);
	break;
	case "review":
	$RMQrequest = array();
	$RMQrequest['type'] = 'addreview';
	$RMQrequest['review'] = $review;
	$RMQrequest['userID'] = $userID;
	$RMQrequest['bookID'] = $bookID;
	$RMQresponse = $client -> send_request($RMQrequest);
	echo json_encode($RMQresponse);
	
	break;
	case "rating":
	$RMQrequest = array();
	$RMQrequest['type'] = 'rate';
	$RMQrequest['bookID'] = $bookID;
	$RMQrequest['rating'] = $rating;

	$RMQresponse = $client -> send_request($RMQrequest);
	echo json_encode($RMQresponse);
	break;
	case "creategroup":
		$RMQrequest = array();
		$RMQrequest['type'] = 'creategroup';
		$RMQrequest['ownerID'] = $ownerID;
		$RMQrequest['groupName'] = $groupName;
		$RMQresponse = $client -> send_request($RMQrequest);
		echo json_encode($RMQresponse);
}

	exit(0);

?>