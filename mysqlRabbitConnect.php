#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$mydb = new mysqli('127.0.0.1','testUser','12345','projectdb');


//$mydb = new mysqli('127.0.0.1','testUser','12345','projectdb');

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
        	echo "Sending back valid login" . PHP_EOL;
        	return array('requestProcessor',array("returnCode" => '0', 'message'=>"Matching username and password found"));
        } 
        else{
        	echo "invalid credentials" . PHP_EOL;
        	return array('requestProcessor',array("returnCode" => '1', 'message'=>"Invalid credentials"));
        }
        
}

function doRegister($username,$password)
{
        global $mydb;
        
        //query for if username exists
        $query = "select * from users where username = '" . $username . "';";
        
       
        if ($response = $mydb->query($query)){
        	if($response->num_rows == 0){
        		$insertQuery = "insert into users (username, password) values ('" . $username ."', '" . $password . "');";
        		if($insertResponse = $mydb->query($insertQuery)){
        			return array('requestProcessor',array("returnCode" => '0', 'message'=>"User registered"));
        		}
        	}
        }
        //if query is borked
        else if ($mydb->errno != 0)
	{
		return array('requestProcessor',array("returnCode" => '2', 'message'=>"Query invalid"));
	}
        //if query returned a matching username
        else{
        	return array('requestProcessor',array("returnCode" => '1', 'message'=>"Username already in use"));
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
      return doValidate($request['username'],$request['sessionId']);
  }
}

$server = new rabbitMQServer("loginRabbitMQ.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>
