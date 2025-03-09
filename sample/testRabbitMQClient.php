<?php
require_once('path.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[1]))
{
 
}
else
{
  $msg = "test message";
}

$request = array();
$request['type'] = "login";
$request['username'] = "steve";
$request['password'] =  "password";
$request['message'] = "login";
$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

