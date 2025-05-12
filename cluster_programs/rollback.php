#!/usr/bin/php
<?php
//4 args to figure out these 4
//arg1 = cluster, arg2 = machine, arg3 = version, argv4 = filetype
$remoteUser = "";
$remoteHost = "";
$remotePath = "";
$localPath = "";

if(null == ($argv[1] || ($argv[2] || ($argv[3]) || ($argv[4]))))
{
	exit;
}

function scpFile ($remoteUser, $remoteHost, $remotePath, $localPath) {

	$cmd = "scp {$remoteUser}@{$remoteHost}:{$remotePath} {$localPath}";
	echo "Deploying: $cmd" . PHP_EOL;
	return shell_exec($cmd);
	
}

function unzipDeployedFile($remoteUser, $remoteHost, $targetPath) {
	$cmd = "ssh {$remoteUser}@{$remoteHost} 'unzip -o {$targetPath} -d " .dirname($targetPath) . "'";
	echo "Unzipping on remote: $cmd" . PHP_EOL;
	return shell_exec($cmd);
}

function scpToCluster ($localPath, $remoteUser, $remoteHost, $remotePath) {

	$cmd = "scp {$localPath} {$remoteUser}@{$remoteHost}:{$remotePath}";
	echo "Deploying: $cmd" . PHP_EOL;
	return shell_exec($cmd);

}

switch($argv[1]){
	case "qa":
		switch($argv[2]){
			case "web":
				$remoteUser = "oaeIT490";
				$remoteHost = parse_ini_file('/home/yousef/git/bookIntegration/clusterListeners/clusterIPs.ini')["web-qa"];
				switch($argv[4]) {
					case "PHP":
						$remotePath = "/var/www/sample/php/";
						$localPath = "/home/yousef/deploy/staging/web/php/".$argv[3]."/php.zip";
						$targetPath = "/var/www/sample/php/php.zip";
						scpToCluster($localPath, $remoteUser, $remoteHost, $remotePath);
						unzipDeployedFile($remoteUser, $remoteHost, $remotePath);
						break;
					case "HTML":
						echo "html case started";
						$remotePath = "/var/www/sample/html/";
						$localPath = "/home/yousef/deploy/staging/web/html/".$argv[3]."/html.zip";
						$targetPath = "/var/www/sample/html/html.zip";
						scpToCluster($localPath, $remoteUser, $remoteHost, $remotePath);
						unzipDeployedFile($remoteUser, $remoteHost, $targetPath);
						break;
					case "CSS":
						$remotePath = "/var/www/sample/css/";
						$localPath = "/home/yousef/deploy/staging/web/css/".$argv[3]."/css.zip";
						$targetPath = "/var/www/sample/css/css.zip";
						scpToCluster($localPath, $remoteUser, $remoteHost, $remotePath);
						unzipDeployedFile($remoteUser, $remoteHost, $targetPath);
						break;
					
					case "JS":
						$remotePath = "/var/www/sample/js/";
						$localPath = "/home/yousef/deploy/staging/web/js/".$argv[3]."/js.zip";
						$targetPath = "/var/www/sample/js/js.zip";
						scpToCluster($localPath, $remoteUser, $remoteHost, $remotePath);
						unzipDeployedFile($remoteUser, $remoteHost, $targetPath);
						break;
				}
				break;
			case "sql":
				$remoteUser = "franklin";
				$remoteHost = parse_ini_file('/home/yousef/git/bookIntegration/clusterListeners/clusterIPs.ini')["sql-qa"];
				switch($argv[4]) {
					case "SQL":
						$localPath = "/home/yousef/deploy/staging/sql/".$argv[3]."/sql.zip";
						$remotePath = "/srv/";
						$targetPath = "/srv/";
						scpToCluster($localPath, $remoteUser, $remoteHost, $remotePath);
						unzipDeployedFile($remoteUser, $remoteHost, $targetPath);
						break;
					case "SQLDEPENDENCIES":
						$localPath = "/home/yousef/deploy/staging/sql/sqldependencies/".$argv[3]."/sqldependencies.zip";
						$remotePath = "/srv/";
						$targetPath = "/srv/";
						scpToCluster($localPath, $remoteUser, $remoteHost, $remotePath);
						unzipDeployedFile($remoteUser, $remoteHost, $targetPath);
						break;
					case "SQLINI":
						$localPath = "/home/yousef/deploy/staging/sql/sqlini/".$argv[3]."/sqlini.zip";
						$remotePath = "/rabbitmqini/";
						$targetPath = "/srv/";
						scpToCluster($localPath, $remoteUser, $remoteHost, $remotePath);
						unzipDeployedFile($remoteUser, $remoteHost, $targetPath);
						break;
				}
				break;
			case "dmz":
				$remoteUser = "yousef";
				$remoteHost = parse_ini_file('/home/yousef/git/bookIntegration/clusterListeners/clusterIPs.ini')["dmz-qa"];
				$remotePath = "/srv";

				$localPath = "/home/yousef/deploy/staging/dmz/".$argv[3]."/dmzBundle.zip";
				
				scpToCluster($localPath, $remoteUser, $remoteHost, $remotePath);
				unzipDeployedFile($remoteUser, $remoteHost, $remotePath);
				break;
		}
	
}

//scp the file, adjustments MAY need to be made
//scpToCluster($localPath, $remoteUser, $remoteHost, $remotePath);
?>
