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
	
	$source = 'dev';
	$destination = $mydb->real_escape_string($request['destination']);
	$zipfile = $mydb->real_escape_string($request['zipFile']);
	$version = $mydb->real_escape_string($request['version']);
	
	$query = "SELECT * from application_versions where source= '" . $source . "' and destination= '" . $destination . "' and version= '" . $version . "' and zip_file= '" . $zipfile . "';";
	if ($response = $mydb->query($query)) {
		if ($response->num_rows == 0) {
			$query = "INSERT INTO application_versions (version, source, destination, zip_file) VALUES ('$version', '$source', '$destination', '$zipfile' )";
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

function unzipDeployedFile($remoteUser, $remoteHost, $targetPath) {
	$cmd = "ssh {$remoteUser}@{$remoteHost} 'unzip -o {$targetPath} -d " .dirname($targetPath) . "'";
	echo "Unzipping on remote: $cmd" . PHP_EOL;
	return shell_exec($cmd);
}

function detectFileType($zipfile) {
	$baseName = pathinfo($zipfile, PATHINFO_FILENAME);
	$fileType = strtoupper($baseName);
	$allowTypes = ['php', 'html', 'css', 'js', 'sql', 'sqldependencies', 'sqlini'];
	
	if (in_array($fileType, $allowTypes)) {
		return $fileType;
	} else {
		return 'Unknown';
	}
}

function requestProcessor($request) {
	
	echo "Received deployment message..." . PHP_EOL;
	var_dump($request);
	
	if (!isset($request['type'])) {
		return "ERROR: unsupported or missing message type";
	}
	
	$type = $request['type'];
	$destination = $request['destination'];
	$zipfile = $request['zipFile'];
	$version = $request['version'];
	
	switch ($type) {
		case 'web':
			if (!isset($request['destination'])) {
				return "ERROR: Missing destination (qa or prod)";
			}
			
			$localDir = dirname($zipLocalPath);
			
			if (!is_dir($localDir)) {
				echo "creating directory: $localDir" . PHP_EOL;
				mkdir($localDir, 0775, true);
			}
				
			$source = 'dev';			
			$role = $type;
			$file_type = detectFileType($zipFile);
			$hostKey = "{$role}-{$source}";
			$targetKey = "{$role}-{$destination}";
			$user = 'oaeIT490';
			
			$sourceHost = parse_ini_file('clusterIPs.ini')[$hostKey];
			$targetHost = parse_ini_file('clusterIPs.ini')[$targetKey];
			
			if (!$targetHost) {
				return "ERROR: Target host for '$hostKey' not found in clusterIPs.ini";
			}
			
			switch ($file_type) {
				case 'html':
					$zipRemotePath = "/home/oaeIT490/staging/html/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/web/html/{$version}/{$zipfile}";
					$targetPath = "/var/www/sample/html/{$zipfile}";
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'css':
					$zipRemotePath = "/home/oaeIT490/staging/css/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/web/css/{$version}/{$zipfile}";
					$targetPath = "/var/www/sample/css/{$zipfile}";
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'php':
					$zipRemotePath = "/home/oaeIT490/staging/php/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/web/php/{$version}/{$zipfile}";
					$targetPath = "/var/www/sample/php/{$zipfile}";
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'js':
					$zipRemotePath = "/home/oaeIT490/staging/js/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/web/js/{$version}/{$zipfile}";
					$targetPath = "/var/www/sample/js/{$zipfile}";
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
			}
			logDeployment($request);
			
			echo "Sent zip to $type node ($targetHost)" . PHP_EOL;
			break;
			
		case 'sql':
			if (!isset($request['destination'])) {
				return "ERROR: Missing destination (qa or prod)";
			}
			$zipRemotePath = "/home/franklin/staging/{$zipfile}";
			$zipLocalPath = "/home/yousef/deploy/staging/sql/{$version}/{$zipfile}";
			$targetPath = "/srv/{$zipfile}";
			
			$localDir = dirname($zipLocalPath);
			
			if (!is_dir($localDir)) {
				echo "creating directory: $localDir" . PHP_EOL;
				mkdir($localDir, 0775, true);
			}
			
			$source = 'dev';
			$role = $type;
			$destination = $request['destination'];
			$hostKey = "{$role}-{$source}";
			$targetKey = "{$role}-{$destination}";
			$user = 'franklin';
			
			$sourceHost = parse_ini_file('clusterIPs.ini')[$hostKey];
			$targetHost = parse_ini_file('clusterIPs.ini')[$targetKey];
			
			if (!$targetHost) {
				return "ERROR: Target host for '$hostKey' not found in clusterIPs.ini";
			}
			
			switch ($file_type) {
				case 'sql':
					$zipRemotePath = "/home/franklin/staging/sql/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/sql/{$version}/{$zipfile}";
					$targetPath = "/srv/sql_programs/{$zipfile}";
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'sqldependencies':
					$zipRemotePath = "/home/franklin/staging/sqldependencies/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/sql/sqldependencies/{$version}/{$zipfile}";
					$targetPath = "/srv/{$zipfile}";
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'sqlini':
					$zipRemotePath = "/home/franklin/staging/sqlini/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/sql/sqlini/{$version}/{$zipfile}";
					$targetPath = "/srv/rabbitmqini/{$zipfile}";
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
			}
			logDeployment($request);
			
			echo "Sent zip to $type node ($targetHost)" . PHP_EOL;
			break;
		
		case 'dmz':
			if (!isset($request['destination'])) {
				return "ERROR: Missing destination (qa or prod)";
			}
			$zipRemotePath = "~/deploy/staging/{$zipfile}";
			$zipLocalPath = "/home/yousef/deploy/staging/dmz/{$version}/{$zipfile}";
			$targetPath = "/srv/{$zipfile}";
			
			$localDir = dirname($zipLocalPath);
			
			if (!is_dir($localDir)) {
				echo "creating directory: $localDir" . PHP_EOL;
				mkdir($localDir, 0775, true);
			}
			
			$source = 'dev';
			$role = $type;
			$destination = $request['destination'];
			$hostKey = "{$role}-{$source}";
			$targetKey = "{$role}-{$destination}";
			$user = 'yousef';
			
			$sourceHost = parse_ini_file('clusterIPs.ini')[$hostKey];
			$targetHost = parse_ini_file('clusterIPs.ini')[$targetKey];
			
			if (!$targetHost) {
				return "ERROR: Target host for '$hostKey' not found in clusterIPs.ini";
			}
			
			scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
			logDeployment($request);
			scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);

			unzipDeployedFile($user, $targetHost, $targetPath);
			
			echo "Sent zip to $type node ($targetHost)" . PHP_EOL;
			break;
			
		default:
			return "ERROR: Unsupported message type '{$type}'";
	}	
	
	echo "Deployment process complete." . PHP_EOL;
	return array("status" => "success", "version" => $version);

}

$server = new rabbitMQServer("deploymentRabbitMQ.ini","deployment");
$server->process_requests('requestProcessor');
exit();
?>
