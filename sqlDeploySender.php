#!/usr/bin/php
<?php
require_once('path.inc');
require_once('rabbitMQLib.inc');
if ($argc < 3) {
    die("Usage: php script.php devDeployment <request_type>, version, source, dest, desc,zipfile,user\n");
}

if($argv[1] == 'devSQLDeployment'){
	$client = new rabbitMQClient("deploymentRabbitMQ.ini",$argv[1]);
}
else if($argv[1] == 'qaSQLDeployment'){
	$client = new rabbitMQClient("deploymentRabbitMQ.ini",$argv[1]);
}
else if($argv[1] == 'prodSQLDeployment'){
	$client = new rabbitMQClient("deploymentRabbitMQ.ini",$argv[1]);
}
else
{
	die("No server specified or improper server specified. devDeployment expected.\n");
}
try{
	$allowedSources= ['dev','qa','prod'];
	$allowedTypes = ['sql','web','dmz'];
	if(!in_array($argv[4], $allowedSources) && !in_array($argv[5],$allowedTypes)){
		throw new Exception("unsupported request type");
}
	$request = array();
	$request['type'] = $argv[2];
	$request['version'] = $argv[3];
	$request['source']= $argv[4];
	$request['destination'] = $argv[5];
	$request['desc'] = $argv[6];
	$request['zipFile'] = $argv[7];
	$request['user'] = $argv[8];
	$request['iniCase'] = $argv[1];
	$response = $client->send_request($request);

	echo "client received response: ".PHP_EOL;
print_r($response);
}
catch(Exception $e) {
	echo 'Message: ' .$e->getMessage();
}

//$response = $client->publish($request);

echo "\n\n";

echo $argv[0]." END".PHP_EOL;
?>
