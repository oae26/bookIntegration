#!/usr/bin/php
<?php
require_once('/home/yousef/git/bookIntegration/path.inc');
require_once('/home/yousef/git/bookIntegration/get_host_info.inc');
require_once('/home/yousef/git/bookIntegration/rabbitMQLib.inc');
require_once('/home/yousef/git/bookIntegration/sample/vendor/autoload.php');

$mydb = new mysqli('127.0.0.1','deploy','deploy','deploymentdb');

if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        exit(0);
}
echo "Connected to deployment database." . PHP_EOL;

function scpFile ($remoteUser, $remoteHost, $remotePath, $localPath) {

	$cmd = "scp {$remoteUser}@ {$remoteUser}@{$remoteHost}:{$remotePath}";
	echo "Deploying: $cmd" . PHP_EOL;
	return shell_exec($cmd);
	
}

function scpToCluster ($localPath, $remoteUser, $remoteHost, $remotePath) {

	$cmd = "scp {$localPath} {$remoteUser}@{$remoteHost}:{$remotePath}";
	echo "Deploying: $cmd" . PHP_EOL;
	return shell_exec($cmd);

}



//Logs version info to deploymentdb
function logDeployment($request) {

	global $mydb;
	
	$version = $mydb->real_escape_string($version);
	$source = $mydb->real_escape_string($source);
	$destination = $mydb->real_escape_string($destination);
	$zipfile = $mydb->real_escape_string($zipName);
	
	$query = "INSERT INTO application_versions (version, source, destination, zip_file) VALUES ('$version', '$source', '$destination', '$zipfile')";
		  
	if (!$mydb->query($query)) {
		echo "Error logging deployment: " . $mydb->error . PHP_EOL;
		return false;
	}
	
	echo "Deployment logged successfully." . PHP_EOL;
	return true;
	
}

function requestProcessor($request) {
	
	echo "Received deployment message..." . PHP_EOL;
	var_dump($request);
	
	if (!isset($request['type']) || $request['type'] !== 'deploy') {
		return "ERROR: unsupported or missing message type";
	}
	
	$version = $request['version'];
	$source = $request['source'];
	$destination = ($source == "dev") ? "qa" : "prod";
	$zipfile = $request['zipFile'];
	$zipRemotePath = "../git/bookintegration/zips/{$zipFile}";
	$zipLocalPath = "../git/bookintegration/deploy/staging/{$zipFile}";
	$targetPath = "../git/bookintegration/deploy/releases/{$zipFile}";
	
	$sourceHost = parse_ini_file('clusterIPs.ini')[$source];
	scpFile('deploy', $sourceHost, $zipRemotePath, $zipLocalPath);
	
	logDeployment($request);
	
	$destHost = parse_ini_file('clusterIPs.ini')[$destination];
	scpToCluster($zipLocalPath, 'deploy', $destHost, $targetPatah);
	
	echo "Deployment process complete." . PHP_EOL;
	return array("status" => "success", "version" => $version);

}

$server = new rabbitMQServer("/home/yousef/git/bookIntegration/clusterListeners/deploymentRabbitMQ.ini","deployment");
$server->process_requests('requestProcessor');
exit();
?>
