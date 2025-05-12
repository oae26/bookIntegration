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
	$zipfile = $mydb->real_escape_string($request['zipfile']);
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
	$allowTypes = ['PHP', 'HTML', 'CSS', 'JS', 'SQL', 'SQLDEPENDENCIES', 'SQLINI', 'API', 'EMAIL'];
	
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
	$zipfile = $request['zipfile'];
	$version = $request['version'];
	
	
	switch ($type) {
		case 'web':
			if (!isset($request['destination'])) {
				return "ERROR: Missing destination (qa or prod)";
			}
			
				
			$source = 'dev';			
			$role = $type;
			$hostKey = "{$role}-{$source}";
			$targetKey = "{$role}-{$destination}";
			$user = 'oaeIT490';
			$file_type = detectFileType($zipfile);
			
			$sourceHost = parse_ini_file('clusterIPs.ini')[$hostKey];
			$targetHost = parse_ini_file('clusterIPs.ini')[$targetKey];
			
			if (!$targetHost) {
				return "ERROR: Target host for '$hostKey' not found in clusterIPs.ini";
			}
			
			switch ($file_type) {
				case 'HTML':
					$zipRemotePath = "/home/oaeIT490/staging/html/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/web/html/{$version}/{$zipfile}";
					$targetPath = "/var/www/sample/html/{$zipfile}";
					
					$localDir = dirname($zipLocalPath);
			
					if (!is_dir($localDir)) {
						echo "creating directory: $localDir" . PHP_EOL;
						mkdir($localDir, 0775, true);
					}
					
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'CSS':
					$zipRemotePath = "/home/oaeIT490/staging/css/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/web/css/{$version}/{$zipfile}";
					$targetPath = "/var/www/sample/css/{$zipfile}";
					
					$localDir = dirname($zipLocalPath);
			
					if (!is_dir($localDir)) {
						echo "creating directory: $localDir" . PHP_EOL;
						mkdir($localDir, 0775, true);
					}
					
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'PHP':
					$zipRemotePath = "/home/oaeIT490/staging/php/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/web/php/{$version}/{$zipfile}";
					$targetPath = "/var/www/sample/php/{$zipfile}";
					
					$localDir = dirname($zipLocalPath);
			
					if (!is_dir($localDir)) {
						echo "creating directory: $localDir" . PHP_EOL;
						mkdir($localDir, 0775, true);
					}
					
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'JS':
					$zipRemotePath = "/home/oaeIT490/staging/js/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/web/js/{$version}/{$zipfile}";
					$targetPath = "/var/www/sample/js/{$zipfile}";
					
					$localDir = dirname($zipLocalPath);
			
					if (!is_dir($localDir)) {
						echo "creating directory: $localDir" . PHP_EOL;
						mkdir($localDir, 0775, true);
					}
					
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
			
			$source = 'dev';
			$role = $type;
			$destination = $request['destination'];
			$hostKey = "{$role}-{$source}";
			$targetKey = "{$role}-{$destination}";
			$user = 'franklin';
			$file_type = detectFileType($zipfile);
			
			$sourceHost = parse_ini_file('clusterIPs.ini')[$hostKey];
			$targetHost = parse_ini_file('clusterIPs.ini')[$targetKey];
			
			if (!$targetHost) {
				return "ERROR: Target host for '$hostKey' not found in clusterIPs.ini";
			}
			
			switch ($file_type) {
				case 'SQL':
					$zipRemotePath = "/home/franklin/staging/sql/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/sql/{$version}/{$zipfile}";
					$targetPath = "/srv/{$zipfile}";
					
					$localDir = dirname($zipLocalPath);
			
					if (!is_dir($localDir)) {
						echo "creating directory: $localDir" . PHP_EOL;
						mkdir($localDir, 0775, true);
					}
					
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'SQLDEPENDENCIES':
					$zipRemotePath = "/home/franklin/staging/sqldependencies/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/sql/sqldependencies/{$version}/{$zipfile}";
					$targetPath = "/srv/{$zipfile}";
					
					$localDir = dirname($zipLocalPath);
			
					if (!is_dir($localDir)) {
						echo "creating directory: $localDir" . PHP_EOL;
						mkdir($localDir, 0775, true);
					}
					
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'SQLINI':
					$zipRemotePath = "/home/franklin/staging/sqlini/{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/sql/sqlini/{$version}/{$zipfile}";
					$targetPath = "/rabbitmqini/{$zipfile}";
					
					$localDir = dirname($zipLocalPath);
			
					if (!is_dir($localDir)) {
						echo "creating directory: $localDir" . PHP_EOL;
						mkdir($localDir, 0775, true);
					}
			
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
			
			$source = 'dev';
			$role = $type;
			$destination = $request['destination'];
			$hostKey = "{$role}-{$source}";
			$targetKey = "{$role}-{$destination}";
			$user = 'nina';
			$file_type = detectFileType($zipfile);
			
			$sourceHost = parse_ini_file('clusterIPs.ini')[$hostKey];
			$targetHost = parse_ini_file('clusterIPs.ini')[$targetKey];
			
			if (!$targetHost) {
				return "ERROR: Target host for '$hostKey' not found in clusterIPs.ini";
			}
			
			
			switch($file_type) {
				case 'API':
					$zipRemotePath = "/home/nina/staging/api{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/dmz/api{$version}/{$zipfile}";
					$targetPath = "/srv/api/{$zipfile}";
					
					$localDir = dirname($zipLocalPath);
			
					if (!is_dir($localDir)) {
						echo "creating directory: $localDir" . PHP_EOL;
						mkdir($localDir, 0775, true);
					}
					
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
				case 'EMAIL':
					$zipRemotePath = "/home/nina/staging/email{$zipfile}";
					$zipLocalPath = "/home/yousef/deploy/staging/dmz/email{$version}/{$zipfile}";
					$targetPath = "/srv/{$zipfile}";
					
					$localDir = dirname($zipLocalPath);
			
					if (!is_dir($localDir)) {
						echo "creating directory: $localDir" . PHP_EOL;
						mkdir($localDir, 0775, true);
					}
					
					scpFile($user, $sourceHost, $zipRemotePath, $zipLocalPath);
					scpToCluster($zipLocalPath, $user, $targetHost, $targetPath);
					unzipDeployedFile($user, $targetHost, $targetPath);
					break;
			
			}
			
			logDeployment($request);

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
