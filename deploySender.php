#!/usr/bin/php
<?php
require_once('path.inc');
require_once('rabbitMQLib.inc');
if ($argc < 5) {
    die("Usage: php script.php <destination>, <request_type>, version, zipFile\n");
}

if ($argv[1] == 'qa' or 'prod') {
	$client = new rabbitMQClient("deploymentRabbitMQ.ini", "deployment");

try{
	$allowedTypes = ['sql','web','dmz'];
	if(!in_array($argv[2],$allowedTypes)){
		throw new Exception("unsupported request type");
}
	$request = array();
	$request['destination'] = $argv[1];
	$request['type'] = $argv[2];
	$request['version'] = $argv[3];
	$request['zipFile'] = $argv[4];
	$response = $client->send_request($request);

	echo "client received response: ".PHP_EOL;
print_r($response);
} catch(Exception $e) {
	echo 'Message: ' .$e->getMessage();
}
}

//$response = $client->publish($request);

echo "\n\n";

echo $argv[0]." END".PHP_EOL;
?>
