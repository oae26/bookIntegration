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

function doBookSearch($title){
	global $mydb;
	$bookKeysArray = array();
	$bookTitlesArray = array();
	$bookYearsArray = array();
	$bookAuthorsArray = array();
	
	$title = str_replace("'", '', $title);
	
	$query = "select id, title, year from books where lower(title) like lower('%".$title."%')";
	
	if ($response = $mydb->query($query)){
		if($response->num_rows != 0){
			while($row = $response -> fetch_row()){
        			$bookKeysArray[] = $row[0];
        			$bookTitlesArray[] = $row[1];
        			$bookYearsArray[] = $row[2];
        		}
		}
		foreach($bookKeysArray as $bookKey){
			$insertString = "";
			$authorQuery  = " 
				select authorId, authorName, title from bookToAuthor join books on bookToAuthor.bookId = books.id join authors on bookToAuthor.authorId = authors.id where books.id = '".$bookKey."'";
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

$server = new rabbitMQServer("rabbitMQ.ini","books");
$server->process_requests('requestProcessor');
exit();
?>
