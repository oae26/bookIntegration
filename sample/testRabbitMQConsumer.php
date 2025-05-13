#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


function requestProcessor($request){

 echo "received request".PHP_EOL;
  var_dump($request);

}
$server = new rabbitMQServer('testRabbitMQ.ini','testServer');
$server->process_requests('requestProcessor');


?>
