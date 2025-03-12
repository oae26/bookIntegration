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
$groupID = $_POST['groupID'] ?? ' ';
$newBookID = $_POST['newBookID'] ?? '';
$username = $_POST['username'] ?? ' ';
$readPage = $_POST['readPage'] ?? ' ';
$dueMonth = $_POST['dueMonth'] ?? ' ';
$dueDay = $_POST['dueDay'] ?? ' ';
$googleBookID = $_POST['googleBookID'] ?? ' ';
$bookReleaseDate = $_POST['bookReleaseDate'] ?? '';


try {
	$bookClient = new rabbitMQClient("testRabbitMQ.ini", "bookServer");
	$ratingClient = new rabbitMQClient("testRabbitMQ.ini", "ratingServer");	
	$reviewClient = new rabbitMQClient("testRabbitMQ.ini", "reviewServer");
	$groupClient =  new rabbitMQClient("testRabbitMQ.ini", "groupServer");
	$watchListClient =  new rabbitMQClient("testRabbitMQ.ini", "watchList");
	$emailClient = new rabbitMQClient("testRabbitMQ.ini","emailServer"); 

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
    $RMQresponse = $bookClient -> send_request($RMQrequest);
	echo json_encode($RMQresponse);
	break;
	case "addwatchlist":
		$RMQrequest = array();
		$RMQrequest['type'] = 'addwatchlist';
		$RMQrequest['googleBookID'] = $googleBookID;
		$RMQrequest['userID'] = $userID;
		$RMQrequest['bookTitle'] = $title;
		$RMQrequest['bookReleaseDate'] = $bookReleaseDate;
		$RMQresponse = $watchListClient -> send_request($RMQrequest);
		echo json_encode($RMQresponse);
		break;
		case "getwatchlist":
			$RMQrequest = array();
			$RMQrequest['type'] = 'getwatchlist';
			$RMQrequest['userID'] = $userID;
			
			$RMQresponse = $watchListClient -> send_request($RMQrequest);
			echo json_encode($RMQresponse);
			break;
	case "review":
	$RMQrequest = array();
	$RMQrequest['type'] = 'addreview';
	$RMQrequest['review'] = $review;
	$RMQrequest['userID'] = $userID;
	$RMQrequest['bookID'] = $bookID;
	$RMQresponse = $reviewClient -> send_request($RMQrequest);
	echo json_encode($RMQresponse);
	
	break;
	case "rate":
	$RMQrequest = array();
	$RMQrequest['type'] = 'rate';
	$RMQrequest['bookID'] = $bookID;
	$RMQrequest['rating'] = $rating;

	$RMQresponse = $ratingClient -> send_request($RMQrequest);
	echo json_encode($RMQresponse);
	break;
	case "creategroup":

	
		$RMQrequest = [
			'type' => 'creategroup',
			'ownerID' => $ownerID,
			'groupName' => $groupName
		];

	
		$RMQrequest = array();
		$RMQrequest['type'] = 'creategroup';
		$RMQrequest['ownerID'] = $ownerID;
		$RMQrequest['groupName'] = $groupName;
		$RMQresponse = $groupClient -> send_request($RMQrequest);
		echo json_encode($RMQresponse);
	break;
	case "getgroups":
		$RMQrequest = array();
		$RMQrequest['type'] = 'getgroups';
		$RMQrequest['userID'] = $userID;
		$RMQresponse = $groupClient -> send_request($RMQrequest);
		echo json_encode($RMQresponse);
	break;
	case "editbookid":

		$RMQrequest = array();
		$RMQrequest['type'] = 'editbookid';
		$RMQrequest['groupID'] = $groupID;
		$RMQrequest['newBookID'] = $newBookID;
		
		$RMQresponse = $groupClient -> send_request($RMQrequest);
		echo json_encode($RMQresponse);
		case "editduedetails":

			$RMQrequest = array();
			$RMQrequest['type'] = 'editduedetails';
			$RMQrequest['groupID'] = $groupID;
			$RMQrequest['readPage'] = $readPage;
			$RMQrequest['dueMonth'] = $dueMonth;
			$RMQrequest['dueDay'] = $dueDay;
			
			$RMQresponse = $groupClient -> send_request($RMQrequest);
			echo json_encode($RMQresponse);
	case  "recruituser":
		$notifRequest = [
			'type' => 'sendgroupemail',
			'username' => $username,
			'groupname' => $groupName
		];
		try {
			$emailClient->send_request($notifRequest);
		} catch (Exception $e) {
			error_log("Email request failed: " . $e->getMessage());
		}
		$RMQrequest = array();
		$RMQrequest['type'] = 'recruituser';
		$RMQrequest['groupID'] = $groupID;
		$RMQrequest['username'] = $username;
		
		$RMQresponse = $groupClient -> send_request($RMQrequest);
		echo json_encode($RMQresponse);
	}
	
		

	exit(0);

?>