#!/usr/bin/php
<?php

require_once('/home/nina/Team/rabbitmqphp_example/path.inc');
require_once('/home/nina/Team/rabbitmqphp_example/get_host_info.inc');
require_once('/home/nina/Team/rabbitmqphp_example/rabbitMQLib.inc');
require_once('/home/nina/Team/rabbitmqphp_example/sample/vendor/autoload.php');

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
		//echo "String has space bars".PHP_EOL;
		$searchString = preg_replace('/\\s/',"+",$searchString);
		//echo $searchString.PHP_EOL;
	}
	
	$apiLink = "https://openlibrary.org/search.json?q=";	// Used for searching with the API
	
	$header = [
		"User-Agent: BookIntergration/1.0 (it490group@gmail.com)"
	];
	
	$options = [
		'http' => [
			'method' => "GET",
			'header' => implode("\r\n",$header)
		],
		'ssl' => [
			'verify_peer' => true,
			'verify_peer_name' => true,
			'allow_self_signed' => false,
			'cafile' => "/etc/ssl/certs/ca-certificates.crt",
			'capture_peer_cert' => true
		]
	];
	
	// Gets options ready for header
	$context = stream_context_create($options);
	
	// Check if there are signs of MitM attacks
	$certContext = stream_context_create([
		'ssl' => ['capture_peer_cert' => true]
	]);
	
	$socket = stream_socket_client(
		"ssl://openlibrary.org:443",
		$errno, $errstr, 30,
		STREAM_CLIENT_CONNECT,
		$certContext
	);
	
	if (!$socket){
		die("TLS handshake failed: $errstr ($errno)\n");
	}
	
	$certParams = stream_context_get_params($socket);
	$cert = $certParams['options']['ssl']['peer_certificate'];
	$fingerprint = openssl_x509_fingerprint($cert, 'sha256');
	$fingerprint = strtoupper(trim($fingerprint));
	
	$openlib_fingerprint = '0B2A5104622A3FFE0402A9A9AE31CD0C86E6BBE0AA333DBF5A5938A250C88183';
	$openlib_fingerprint = strtoupper(trim($openlib_fingerprint));
	
	if ($fingerprint !== $openlib_fingerprint){
		die("ERROR: Under attack by MitM.\nOpenlib: $openlib_fingerprint \nActual: $fingerprint\n");
	}
	
	// Get Json file and run it
	echo "Running API search for: ".$searchString.PHP_EOL;
	$apiLink .= $searchString;
	if ($data = file_get_contents($apiLink,false,$context)){
		//echo "Data".$data.PHP_EOL;
		
		$json = json_decode($data, true);
		
		// Get the data of each book found
		foreach($json['docs'] as $field => $value){
			$insertString = "";
			$bookKeysArray[] = str_replace("/works/",'',$json['docs'][$field]['key']);
			$bookTitlesArray[] = $json['docs'][$field]['title'];
			$bookYearsArray[] = $json['docs'][$field]['first_publish_year'];
			
			//Do author array
			foreach($json['docs'][$field]['author_name'] as $field2 => $value2){
				$insertString = $insertString.$json['docs'][$field]['author_name'][$field2].", "; 
			}
			$bookAuthorsArray[] = $insertString;
			//$bookAuthorsArray[] = $json['docs'][$field]['author_name'][0];
			
			//Do cover array
			if ($json['docs'][$field]['cover_edition_key'] == NULL){
				$bookCoversArray[] = 1;
			}else{
				$bookCoversArray[] = $json['docs'][$field]['cover_edition_key'];
			}
			//Debug
			echo 'Key: '.$bookKeysArray[$field].PHP_EOL;
			echo 'Title: '.$bookTitlesArray[$field].PHP_EOL;
			echo 'Year: '.$bookYearsArray[$field].PHP_EOL;
			
			
		}
	}
	
	echo "Sending API results..." . PHP_EOL;
	// Get json file results
	echo "Number of books found: " . sizeof($bookKeysArray) . PHP_EOL;
	if (sizeof($bookKeysArray) == 0){
		return array ('returnCode' => '1');
	}
	return array('returnCode' => '0', 'bookKeys'=>$bookKeysArray, 'bookTitles'=>$bookTitlesArray, 'bookYears'=>$bookYearsArray, 'bookAuthors'=>$bookAuthorsArray, 'bookCovers'=>$bookCoversArray);
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
    	return olAPISearch($request['username'],$request['title']); 
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

// Replace emailServer with noBookAPI
$server = new rabbitMQServer("rabbitMQ.ini","noBookAPI");

echo "testRabbitMQServer BEGIN".PHP_EOL;

//olAPISearch("testUser", "test my pack");
$server->process_requests('requestProcessor'); // Comment this out if testing this solo
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>
