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


function doGetWatchlist($userID){
	global $mydb;
	$googleBookIDArray = array();
	$bookNameArray = array();
	$releaseDateArray = array();
	
	$query = "select googleBookID, bookName, bookReleaseDate from watchlists where userID = ".$userID.";";
	
	if($response = $mydb->query($query)){
		while($row = $response->fetch_row()){
			$googleBookIDArray[] = $row[0];
			$bookNameArray[] = $row[1];
			$releaseDateArray[] = $row[2];
		}
	}
	if ($mydb->errno != 0)
	{
		return array('returnCode' => '2', 'message'=>'Query invalid');
	}
	echo "Returning watchlist for user: " . $userID . PHP_EOL;
	return array('returnCode'=>'0', 'googleBookIDArray'=>$googleBookIDArray, 'bookNameArray'=>$bookNameArray, 'releaseDateArray'=>$releaseDateArray);
}


function doAddWatchlist($googleBookID, $userID, $bookTitle, $bookReleaseDate){
	global $mydb;
	$rMessage = "";
	$bookTitle = str_replace("'", '', $bookTitle);
	
	if(($userID == "") || ($userID == "undefined") || ($userID == NULL)) return array('returnCode' => '2', 'message'=>'Query invalid');
	
	if(($bookTitle == "") || ($bookTitle == "undefined") || ($bookTitle == NULL)) return array('returnCode' => '2', 'message'=>'Query invalid');
	
	if(($googleBookID == "") || ($googleBookID == "undefined") || ($googleBookID == NULL)) return array('returnCode' => '2', 'message'=>'Query invalid');
	
	if(($bookReleaseDate == "") || ($bookReleaseDate == "undefined") || ($bookReleaseDate == NULL)) return array('returnCode' => '2', 'message'=>'Query invalid');
	
	$query = "insert into watchlists (googleBookID, userID, bookName, bookReleaseDate) values ('".$googleBookID."', ".$userID.", '".$bookTitle."', '".$bookReleaseDate."');";
	
	if($response = $mydb->query($query)){
		$rMessage = 'Book added to watchlist';
	}
	if ($mydb->errno != 0)
	{
		return array('returnCode' => '2', 'message'=>'Query invalid');
	}
	echo "Book added to watchlist" . PHP_EOL;
	return array('returnCode'=>'0', 'message'=>$rMessage);
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
    case "getwatchlist":
    	return doGetWatchlist($request['userID']);
    case "addwatchlist":
    	return doAddWatchlist($request['googleBookID'], $request['userID'], $request['bookTitle'], $request['bookReleaseDate']);
  }
}

$server = new rabbitMQServer("/srv/sql_programs/rabbitMQ.ini","watchlist");
$server->process_requests('requestProcessor');
exit();
