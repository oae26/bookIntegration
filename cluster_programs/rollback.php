#!/usr/bin/php
<?php
//3 args to figure out these 4
//arg1 = cluster, arg2 = machine, arg3 = version
$remoteUser = "";
$remoteHost = "";
$remotePath = "";
$localPath = "";

if(null == ($argv[1] || ($argv[2] || ($argv[3]))))
{
	exit;
}

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

switch($argv[1]){
	case "qa":
		switch($argv[2]){
			case "web":
				$remoteUser = "oaeIT490";
				$remoteHost = parse_ini_file('clusterIPs.ini')["web-qa"];
				$remotePath = "/var/www/sample/";
				$localPath = "~/deploy/staging/web/".$argv[3]."/webBundle.zip";
				break;
			case "sql":
				$remoteUser = "franklin";
				$remoteHost = parse_ini_file('clusterIPs.ini')["sql-qa"];
				$remotePath = "/srv/";
				$localPath = "~/deploy/staging/sql/".$argv[3]."/mysqlBundle.zip";
				break;
			case "dmz":
				$remoteUser = "yousef";
				$remoteHost = parse_ini_file('clusterIPs.ini')["dmz-qa"];
				$remotePath = "/srv";
				$localPath = "~/deploy/staging/dmz/".$argv[3]."/dmzBundle.zip";
				break;
		}
		break;
	case "prod":
		switch($argv[2]){
			case "web":
				$remoteUser = "oaeIT490";
				$remoteHost = parse_ini_file('clusterIPs.ini')["web-prod"];
				$remotePath = "/var/www/sample/";
				$localPath = "~/deploy/staging/web/".$argv[3]."/webBundle.zip";
				break;
			case "sql":
				$remoteUser = "franklin";
				$remoteHost = parse_ini_file('clusterIPs.ini')["sql-prod"];
				$remotePath = "/srv/";
				$localPath = "~/deploy/staging/sql/".$argv[3]."/mysqlBundle.zip";
				break;
			case "dmz":
				$remoteUser = "yousef";
				$remoteHost = parse_ini_file('clusterIPs.ini')["dmz-prod"];
				$remotePath = "/srv";
				$localPath = "~/deploy/staging/dmz/".$argv[3]."/dmzBundle.zip";
				break;
		}
}

//scp the file, adjustments MAY need to be made
scpToCluster($localPath, $remoteUser, $remoteHost, $remotePath);
?>
