#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../newRabbitLib.inc');

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

function doGetGroups($userID){
	global $mydb;
	$groupIDArray = array();
	$groupNameArray = array();
	$ownerIDArray = array();
	$bookIDArray = array();
	$readPageArray = array();
	$dueMonthArray = array();
	$dueDayArray = array();
	
	if($userID == "" || $userID == " "){
		echo 'Unreadable or blank user id';
		return array('returnCode' => '1', 'message'=>'Unreadable or blank user id');
	}
	
	$query = "select readingGroups.id, groupName, ownerID, bookID, readPage, dueMonth, dueDay from readingGroups join readingGroupUsers on readingGroups.id = readingGroupUsers.readingGroupID where readingGroupUsers.userID = ".$userID.";";
	
	if ($response = $mydb->query($query)){
		if($response->num_rows != 0){
			while($row = $response -> fetch_row()){
				$groupIDArray[] = $row[0];
				$groupNameArray[] = $row[1];
				$ownerIDArray[] = $row[2];
				$bookIDArray[] = $row[3];
				$readPageArray[] = $row[4];
				$dueMonthArray[] = $row[5];
				$dueDayArray[] = $row[6];
			}
		}
	}
	if ($mydb->errno != 0){
		return array('returnCode' => '1', 'message'=>'Query error');
	}
	echo "Returning " . $response->num_rows . " groups to user" . PHP_EOL;
	return array('returnCode'=>'0', 'groupIDArray'=>$groupIDArray, 'groupNameArray'=>$groupNameArray, 'ownerIDArray'=>$ownerIDArray, 'bookIDArray'=>$bookIDArray, 'readPageArray'=>$readPageArray, 'dueMonthArray'=>$dueMonthArray, 'dueDayArray'=>$dueDayArray);
}


function doEditBookID($groupID, $newBookID){
	global $mydb;
	
	$query = "update readingGroups set bookID = '".$newBookID."' where id = ".$groupID.";";
	
	if ($response = $mydb->query($query)){
			echo "Group ID " .$groupID. " bookID updated to " . $newBookID . PHP_EOL;
			return array('returnCode' => '0', 'message'=>'Group Book update success');
	}
	echo "Book ID update query failure";
	return array('returnCode' => '1', 'message'=>'Query error');
}


function doEditDue($groupID, $newReadPage, $newDueMonth, $newDueDay){
	global $mydb;
	
	$query = "update readingGroups set readPage = ".$newReadPage.", dueMonth = ".$newDueMonth.", dueDay = ".$newDueDay." where id = ".$groupID.";";
	
	if ($response = $mydb->query($query)){
			echo "Group ID " .$groupID. " readPage updated to " . $newReadPage.", due date updated to " . $newDueMonth . "/" . $newDueDay. PHP_EOL;
			return array('returnCode' => '0', 'message'=>'Group readPage, dueMonth, dueDay updated');
	}
	echo "Due update query failure" . PHP_EOL;
	return array('returnCode' => '1', 'message'=>'Query error');
}

function doAddUser($groupID, $username){
	global $mydb;
	
	$query = "select userID from users where username = '".$username."';";
	if($response = $mydb->query($query)){
		if($response->num_rows != 0){
			$newUserID = $response->fetch_row()[0];
			echo "User ID: " . $newUserID . PHP_EOL;
			$query1 = "select * from readingGroupUsers where readingGroupID = ".$groupID." and userID = ".$newUserID.";";
			if ($response1 = $mydb->query($query1)){
				if($response1->num_rows == 0){
					echo "user not in reading group" . PHP_EOL;
					$query2 = "insert into readingGroupUsers (readingGroupID, userID) values (".$groupID.", ".$newUserID.");";
					if ($response2 = $mydb->query($query2)){
						echo "User added to reading group" . PHP_EOL;
						$query3 = "select groupName from readingGroups where id = ".$groupID.";";
						if($response3 = $mydb->query($query3)){
							$row = $response3->fetch_row();
							$groupName = $row[0];
							return array('returnCode'=>'0', 'message'=>'User added to reading group', 'username'=>$username, 'groupName'=>$groupName);
						}
					}
				}
				else{
					return array('returnCode'=>'1', 'message'=>'User already in reading group');
				}
			}
		}
	}
	return array('returnCode'=>'2', 'message'=>'Query error');
		
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
    case "getgroups":
    	return doGetGroups($request['userID']);
    case "editbookid":
    	return doEditBookID($request['groupID'], $request['newBookID']);
    case "editduedetails":
    	return doEditDue($request['groupID'], $request['readPage'], $request['dueMonth'], $request['dueDay']); 
    case "recruituser":
    	return doAddUser($request['groupID'], $request['username']);
  }
}

$server = new rabbitMQServer("rabbitMQ.ini","groups");
$server->process_requests('requestProcessor');
exit();
?>
