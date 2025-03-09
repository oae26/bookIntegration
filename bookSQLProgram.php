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

function doBookSearch($title){
	global $mydb;
	$bookTitlesArray = array();
	
	$query = "select title from books where lower(title) like lower('%".$title."%')";
	
	if ($response = $mydb->query($query)){
        	while($row = $response -> fetch_row()){
        		$bookTitlesArray[] = $row;
        	}	
	}
	echo "Sending results..." . PHP_EOL;
	return array('returnCode' => '0', 'bookTitles'=>$bookTitlesArray);
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
    case "booksearch":
    	echo "Searching for books...";
    	return doBookSearch($request['title']);
  }
}

$server = new rabbitMQServer("rabbitMQ.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>
