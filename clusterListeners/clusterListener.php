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

	$cmd = "scp {$remoteUser}@{$remoteHost}:{$remotePath} {$localPath}";
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
	
	$version = $mydb->real_escape_string($request['version']);
	$source = $mydb->real_escape_string($request['source']);
	$destination = $mydb->real_escape_string($request['destination'] ?? (($source == "dev") ? "qa" : "prod"));
	$zipfile = $mydb->real_escape_string($request['zipFile']);
	$description = $mydb->real_escape_string($request['description'] ?? '');
	
	$query = "SELECT * from application_versions where source= '" . $source . "' and destination= '" . $destination . "' and version= '" . $version . "' and zip_file= '" . $zipfile . "';";
	if ($response = $mydb->query($query)) {
		if ($response->num_rows == 0) {
			$query = "INSERT INTO application_versions (version, source, destination, zip_file, description) VALUES ('$version', '$source', '$destination', '$zipfile', '$description')";
		}
	} else {
		$row = $reponse->fetch_row();
		$query = "update application_versions set source= '" . $source . "' and destination= '" . $destination . "' and version= '" . $version . "' and zip_file= '" . $zipfile . "' where id= " . $row[0] . ";";
	}
		  
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
	
	if (!isset($request['type'])) {
		return "ERROR: unsupported or missing message type";
	}
	
	$type = $request['type'];
	$version = $request['version'];
	$source = $request['source'];
	$destination = $request['destination'];
	$zipfile = $request['zipFile'];
	$description = $request['desc'];
	$user = $request['user'];
	
	$zipRemotePath = "~/staging/{$zipfile}";
	$zipLocalPath = "/home/yousef/deploy/staging/{$zipfile}";
	$targetPath = "~/releases/{$zipfile}";
	
	switch ($type) {
		case 'web':
			if (!isset($request['destination'])) {
				return "ERROR: Missing destination (qa or prod)";
			}
			
			$zipRemotePath = "~/staging/{$zipfile}";
			$zipLocalPath = "/home/yousef/deploy/staging/web/{$version}/{$zipfile}";
			$targetPath = "~/var/www/sample/{$zipfile}";
			
			$role = $type;
			$destination = $request['destination'];
			$hostKey = "{$role}-{$source}";
			$targetKey = "{$role}-{$destination}";
			
			$sourceHost = parse_ini_file('clusterIPs.ini')[$hostKey];
			$targetHost = parse_ini_file('clusterIPs.ini')[$targetKey];
			
			if (!$targetHost) {
				return "ERROR: Target host for '$hostKey' not found in clusterIPs.ini";
			}
			
			scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
			logDeployment($request);
			scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
			echo "Sent zip to $type node ($targetHost)" . PHP_EOL;
			break;
		case 'sql':
			if (!isset($request['destination'])) {
				return "ERROR: Missing destination (qa or prod)";
			}
			$zipRemotePath = "~/staging/{$zipfile}";
			$zipLocalPath = "/home/yousef/deploy/staging/sql/{$version}/{$zipfile}";
			$targetPath = "~/srv/{$zipfile}";
			
			$role = $type;
			$destination = $request['destination'];
			$hostKey = "{$role}-{$source}";
			$targetKey = "{$role}-{$destination}";
			
			$sourceHost = parse_ini_file('clusterIPs.ini')[$hostKey];
			$targetHost = parse_ini_file('clusterIPs.ini')[$targetKey];
			
			if (!$targetHost) {
				return "ERROR: Target host for '$hostKey' not found in clusterIPs.ini";
			}
			
			scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
			logDeployment($request);
			scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
			echo "Sent zip to $type node ($targetHost)" . PHP_EOL;
			break;
		case 'dmz':
			if (!isset($request['destination'])) {
				return "ERROR: Missing destination (qa or prod)";
			}
			$zipRemotePath = "~/staging/{$zipfile}";
			$zipLocalPath = "/home/yousef/deploy/staging/dmz/{$version}/{$zipfile}";
			$targetPath = "~/srv/{$zipfile}";
			
			$role = $type;
			$destination = $request['destination'];
			$hostKey = "{$role}-{$source}";
			$targetKey = "{$role}-{$destination}";
			
			$sourceHost = parse_ini_file('clusterIPs.ini')[$hostKey];
			$targetHost = parse_ini_file('clusterIPs.ini')[$targetKey];
			
			if (!$targetHost) {
				return "ERROR: Target host for '$hostKey' not found in clusterIPs.ini";
			}
			
			scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
			logDeployment($request);
			scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
			echo "Sent zip to $type node ($targetHost)" . PHP_EOL;
			break;
			
		default:
			return "ERROR: Unsupported message type '{$type}'";
	}	
	
	echo "Deployment process complete." . PHP_EOL;
	return array("status" => "success", "version" => $version);

}

$server = new rabbitMQServer("/home/yousef/git/bookIntegration/clusterListeners/deploymentRabbitMQ.ini","mysqlDeployment");
$server->process_requests('requestProcessor');
exit();
?>
