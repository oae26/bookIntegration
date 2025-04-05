#!/usr/bin/php
<?php

require_once('path.inc');
require_once('get_host_info.inc');
//require_once('newRabbitLib.inc');
require_once('rabbitMQLib.inc');

// We are going to intergate logging for each API search
function olAPISearch($username, $searchString){
	// Create API call to search for book and return array for rabbitMQ
	
	$bookKeysArray = array();
	$bookTitlesArray = array();
	$bookYearsArray = array();
	$bookAuthorsArray = array();
	$bookCoversArray = array();		//Check if exist, if not set the value to one
	
	// Check if the search string has spaces and replace those spaces with plus symbol
	if (preg_match('/\\s/',$searchString)){
		echo "String has space bars".PHP_EOL;
		$searchString = preg_replace('/\\s/',"+",$searchString);
		echo $searchString;
	}
	
	$apiLink = "https://openlibrary.org/search.json?q=";	// Used for searching with the API
	
	$header = [
		"User-Agent: BookIntergration/1.0 (it490group@gmail.com)"
	];
	
	$options = [
		'http' => [
			'method' => "GET",
			'header' => $header,
		]
	];
	
	// Gets options ready for header
	$context = stream_context_create($options);
	
	// Get Json file and run it
	echo "Running API search for: ".$searchString.PHP_EOL;
	$apiLink .= $searchString;
	if ($data = file_get_contents($apiLink,false,$context)){
		echo "Data".$data.PHP_EOL;
	}
	
	// BookAPI needs HEADER that specifies a User-Agent string with the name of our application and our contact email
	
		// Go to sql_programs/bookSearchSQLProgram.php to see how to do json and array managment
		/*if($data->num_rows != 0){
			while($row = $data -> fetch_row()){
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
		} */
	
	//echo "Sending API results..." . PHP_EOL;
	// Get json file results
	//echo "Number of books found: " . sizeof($bookKeysArray) . PHP_EOL; 
	//return array('returnCode' => '0', 'bookKeys'=>$bookKeysArray, 'bookTitles'=>$bookTitlesArray, 'bookYears'=>$bookYearsArray, 'bookAuthors'=>$bookAuthorsArray, 'bookCovers'=>$bookCoversArray);
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
    case "Login":
      return doLogin($request['username'],$request['password']);
    case "OLBookSearch":
    	return; //new function that will take in a request from user and their search string and return the book from api, else return null if book does not exists.
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
olAPISearch("testUser", "test my pack");
//$server->process_requests('requestProcessor'); // Comment this out if testing this solo
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>
