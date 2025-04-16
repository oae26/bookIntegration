#!/usr/bin/php
<?php
require_once('/srv/path.inc');
require_once('/srv/get_host_info.inc');
require_once('/srv/newRabbitLib.inc');

//uncomment for local testing
//require_once('../path.inc');
//require_once('../get_host_info.inc');
//require_once('../newRabbitLib.inc');


$mydb = new mysqli('127.0.0.1','testUser','12345','projectdb2');

if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        exit(0);
}
echo "Connected to project database." . PHP_EOL;


function doRate($bookID, $rating){
	global $mydb;
	$sumOfRatings;
	$totalRatings;
	$newRating;
	
	$query1 = "select * from bookRatings where bookID = '".$bookID."';";
	if ($response = $mydb->query($query1)){ 
		if($response->num_rows == 0){ //create an entry for the ratings if there is none
			echo "Book not found, creating rating entry" . PHP_EOL;
			$insert = "insert into bookRatings (bookID) values ('".$bookID."');";
			if ($insertResponse = $mydb->query($insert)){
				echo "Rating created" . PHP_EOL;
				$response = $mydb->query($query1); //refresh query, which will always return an entry now
			}
			
		}
		while($row = $response -> fetch_row()){ //update book rating
			$sumOfRatings = $row[2];
			$totalRatings = $row[3];
		}
		$sumOfRatings += $rating;
		$totalRatings += 1;
		$newRating = (float)number_format($sumOfRatings / $totalRatings, 1);
		
		$query2 = "update bookRatings set rating = ".$newRating.", sumOfRatings = ".$sumOfRatings.", totalRatings = ".$totalRatings." where bookID = '".$bookID."';";
		if($response2 = $mydb->query($query2)){
			echo "Rating for book updated: " . $newRating. " for bookID: ". $bookID . PHP_EOL;
			return array('returnCode' => '0', 'newRating' => $newRating);
		}
	}
	return array('returnCode' => 'Error', 'message' => "Unable to add rating to database");

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
    case "rate":
    	return doRate($request['bookID'],$request['rating']);
  }
}

$server = new rabbitMQServer("/rabbitmqini/rabbitMQ.ini","ratings");
$server->process_requests('requestProcessor');
exit();
?>
