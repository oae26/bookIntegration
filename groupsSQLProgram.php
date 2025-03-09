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

function doCreateGroup($ownerID, $groupName){
	global $mydb;
	$query = "insert into readingGroups (ownerID, groupName) values (".$ownerID.", '".$groupName."');";
	
	if ($response = $mydb->query($query)){
		$groupID = $mydb->insert_id;
		echo "New reading group created, ID: ".$groupID.", name: " . $groupName . ", ownerID: " . $ownerID . PHP_EOL;
		$query2 = "insert into readingGroupUsers (readingGroupID, userID) values (".$groupID.", ".$ownerID.")";
		if($response2 = $mydb->query($query2)){
			return array('returnCode'=>'0', 'groupID'=>$groupID, 'groupName'=>$groupName, 'ownerID'=>$ownerID);		
		}
	}
	if ($mydb->errno != 0)
	{
		return array('returnCode'=> '2', 'message'=>'Query error');
	}
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
    case "creategroup":
    	return doCreateGroup($request['ownerID'], $request['groupName']);
  }
}

$server = new rabbitMQServer("rabbitMQ.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>
