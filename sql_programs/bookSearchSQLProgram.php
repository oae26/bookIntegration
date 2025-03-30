#!/usr/bin/php
<?php
require_once('/srv/path.inc');
require_once('/srv/get_host_info.inc');
require_once('/srv/newRabbitLib.inc');

//uncomment for local testing
//require_once('../path.inc');
//require_once('../get_host_info.inc');
//require_once('../newRabbitLib.inc');

$mydb = new mysqli('127.0.0.1','testUser','12345','projectdb');

if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        exit(0);
}
echo "Connected to project database." . PHP_EOL;

function doBookSearch($title){
	global $mydb;
	$bookKeysArray = array();
	$bookTitlesArray = array();
	$bookYearsArray = array();
	$bookAuthorsArray = array();
	
	$title = str_replace("'", '', $title);
	
	$query = "select id, title, year from booksB where lower(title) like lower('%".$title."%') limit 10";
	
	if ($response = $mydb->query($query)){
		if($response->num_rows != 0){
			while($row = $response -> fetch_row()){
        			$bookKeysArray[] = $row[0];
        			echo $row[0] . PHP_EOL;
        			$bookTitlesArray[] = $row[1];
        			echo $row[1] . PHP_EOL;
        			$bookYearsArray[] = $row[2];
        			echo $row[2] . PHP_EOL;
        		}
		}
		foreach($bookKeysArray as $bookKey){
			$insertString = "";
			$authorQuery  = " 
				select authorId, authorName, title from bookToAuthorB join booksB on bookToAuthorB.bookId = booksB.id join authorsB on bookToAuthorB.authorId = authorsB.id where booksB.id = '".$bookKey."'";
			if($authorResponse = $mydb->query($authorQuery)){
				while($authorRow = $authorResponse -> fetch_row()){
					$insertString = $insertString . $authorRow[1] . ", "; 
				}
				
			}
			$bookAuthorsArray[] = $insertString;
		} 
		
	}
	echo "Sending results..." . PHP_EOL;
	echo "Number of books found: " . sizeof($bookKeysArray) . PHP_EOL; 
	return array('returnCode' => '0', 'bookKeys'=>$bookKeysArray, 'bookTitles'=>$bookTitlesArray, 'bookYears'=>$bookYearsArray, 'bookAuthors'=>$bookAuthorsArray);
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

$server = new rabbitMQServer("/srv/sql_programs/rabbitMQ.ini","books");
$server->process_requests('requestProcessor');
exit();
?>
