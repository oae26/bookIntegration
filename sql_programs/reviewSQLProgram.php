#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$mydb = new mysqli('127.0.0.1','testUser','12345','projectdb');

if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        exit(0);
}
echo "Connected to project database." . PHP_EOL;

function doAddReview($review, $userID, $bookID){
	global $mydb;
	
	$prequery = "select userID, bookID from reviews where userID = ".$userID." and bookID = '".$bookID."';";
	if($preresponse = $mydb->query($prequery)){
		if($preresponse->num_rows == 0){  //user hasn't reviewed the book
			$query = "insert into reviews (review, userID, bookID) values ('".$review."','".$userID."','".$bookID."')";
	
			if ($response = $mydb->query($query)){ 
				echo "Review added" . PHP_EOL;
				return array('returnCode'=>'0', 'message'=>'Review Added');
			}
			else if ($mydb->errno != 0){
				echo "Failed to insert review" . PHP_EOL;
				return array('returnCode' => '1', 'message'=> 'Failed to insert review to DB');
			}
		}
		else{ //user has reviewed the book
			$query = "update reviews set review = '".$review."' where userID = ".$userID." and bookID = '".$bookID."';";
			if ($response = $mydb->query($query)){
				echo "Review updated" . PHP_EOL;
				return array('returnCode'=>'0', 'message'=>'Review Added');
			}
			else if ($mydb->errno != 0){
				echo "Failed to update review" . PHP_EOL;
				return array('returnCode' => '1', 'message'=> 'Failed to update review');
			}
		}
	}
	return array('returnCode'=>'2', 'message'=>"Something went wrong.");
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
    case "addreview":
    	return doAddReview($request['review'],$request['userID'], $request['bookID']);
  }
}

$server = new rabbitMQServer("rabbitMQ.ini","testServer");
$server->process_requests('requestProcessor');
exit();

?>
