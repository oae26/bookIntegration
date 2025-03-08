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


function doLogin($username,$password)
{
        global $db_username;
        global $db_password;
        global $mydb;

	echo $username . " " . $password . PHP_EOL;
        
        //query for username with matching password (hashed)
        $query = "select * from users where username = '" . $username . "' and password = '" . $password . "';";
        
        //no need to check for multiple rows, a query like this where a hashed password is being searched for SHOULDN'T (big shouldn't) return anymore than 1 row
       
        if ($response = $mydb->query($query)){
        	while($row = $response -> fetch_row()){
        		$db_username = $row[1];
        		$db_password = $row[2];
        	}	
	}
	if ($mydb->errno != 0)
	{
		return array('requestProcessor',array("returnCode" => '2', 'message'=>"Query invalid"));
	}
        
        
        if($username == $db_username && $password == $db_password){
        	$sessionKey = rand(0, 999);
        	echo $sessionKey . PHP_EOL;
        	echo "Inserting session key into sessions table where username = fetched db username". PHP_EOL;
        	$sessionInsertQuery = "update sessions set sessionKey = " . $sessionKey . " where username = '" . $db_username . "';";
        	if($sessionResponse = $mydb->query($sessionInsertQuery)){
        		echo "Sending back valid login/username and session key" . PHP_EOL;
        		return array('returnCode' => '0', 'message'=>$db_username, 'sessionKey'=>$sessionKey);
        	}
        } 
        else{
        	echo "invalid credentials" . PHP_EOL;
        	return array('returnCode' => '1', 'message'=>'Invalid credentials');
        }
        
}

function doRegister($username,$password)
{
        global $mydb;
        
        //query for if username exists
        $query = "select * from users where username = '" . $username . "';";
        
       
        if ($response = $mydb->query($query)){
        	if($response->num_rows == 0){
        		echo "Username not in use, registering..." . PHP_EOL;
        		$insertQuery = "insert into users (username, password) values ('" . $username ."', '" . $password . "');";
        		if($insertResponse = $mydb->query($insertQuery)){
        			$sessionInsertQuery = "insert into sessions (username) values ('" . $username ."')";
        			if($sessionInsertResponse = $mydb->query($sessionInsertQuery)){
        				echo "User registered, sessions table entry created" . PHP_EOL;
        				return array("returnCode" => '0', 'message'=>"User registered");
        			}
        			else if ($mydb->errno != 0)
				{
					return array('returnCode' => '2', 'message'=>'Query invalid');
				}
        			
        		}
        		else if ($mydb->errno != 0)
			{
				return array('returnCode' => '2', 'message'=>'Query invalid');
			}
        	}
        	//if query is borked
        	else{
        		echo "Username found, cancelling registration" . PHP_EOL;
        		return array('returnCode' => '1', 'message'=>'Username already in use');
        	}
        }
        
        if ($mydb->errno != 0)
	{
		return array('returnCode' => '2', 'message'=>'Query invalid');
	}
}

function doValidate($username, $sessionKey){
	global $db_username;
	global $db_sessionKey;
	global $mydb;
	
	$query = "select * from sessions where username = '" . $username . "';";
	
	if ($response = $mydb->query($query)){
        	while($row = $response -> fetch_row()){
        		$db_username = $row[0];
        		$db_sessionKey = $row[1];
        	}	
        	if($username == $db_username){
        		if($db_sessionKey === null){
        			echo "Session key is null, invalid" . PHP_EOL;
        			return array('returnCode' => '1', 'message'=>'Session invalid');
        		}
        		else if ($sessionKey == $db_sessionKey){
        			echo "Key's match, session valid" . PHP_EOL;
        			return array('returnCode' => '0', 'message'=>'Session validated');
        		}
        		else if ($sessionKey != $db_sessionKey){
        			echo "Key's mismatch, session invalid" . PHP_EOL;
        			return array('returnCode' => '1', 'message'=>'Session invalid');
        		}
        	}
	}
	if ($mydb->errno != 0)
	{
		return array('returnCode' => '2', 'message'=>'Query invalid');
	}
}

function doLogout($username, $sessionKey){
	global $db_username;
	global $db_sessionKey;
	global $mydb;
	
	$query = "select * from sessions where username = '" . $username . "' and sessionKey = " . $sessionKey . ";";
	
	if ($response = $mydb->query($query)){
        	while($row = $response -> fetch_row()){
        		$db_username = $row[0];
        		$db_sessionKey = $row[1];
        	}	
        	if($username == $db_username && $sessionKey == $db_sessionKey){
        		$logoutQuery = "update sessions set sessionKey = NULL where username = '" . $db_username . "';";
        		if($logoutResponse = $mydb->query($logoutQuery)){
        			if ($mydb->errno == 0) {
					echo "user session key nulled, logout okay" . PHP_EOL;
					return array('returnCode' => '0', 'message'=>'Logout Successful');
				}
        		}
        	}
	}
	if ($mydb->errno != 0)
	{
		return array('returnCode' => '2', 'message'=>'Query invalid');
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
    case "login":
    	return doLogin($request['username'],$request['password']);
    case "register":
    	return doRegister($request['username'],$request['password']);
    case "validate_session":
    	return doValidate($request['username'],$request['sessionKey']);
    case "logout":
    	return doLogout($request['username'],$request['sessionKey']);
  }
}

$server = new rabbitMQServer("rabbitMQ.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>
