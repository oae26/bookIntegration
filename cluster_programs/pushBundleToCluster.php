#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

$client = new rabbitMQClient("rabbitCluster.ini","testServer");

$request = array();
$request['type'] = "SQLDevBundleToQA";
$request['message'] = "Bundle items from dev cluster and have them SCPd to QA";
$response = $client->send_request($request);

?>
