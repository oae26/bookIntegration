#!/usr/bin/php
<?php

$mydb = new mysqli('127.0.0.1','testUser','12345','projectdb');



if ($mydb->errno != 0)
{
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
}

echo "successfully connected to database".PHP_EOL;

$query = "select * from users;";



if ($response = $mydb->query($query)){
	while($row = $response -> fetch_row()){
	$db_username = $row[1];
	$db_password = $row[2];
	echo "user: " . $db_username . ", password: " . $db_password . PHP_EOL;
	}
}
if ($mydb->errno != 0)
{
	echo "failed to execute query:".PHP_EOL;
	echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	exit(0);
}
return($db_username);
return($db_password);

?>
