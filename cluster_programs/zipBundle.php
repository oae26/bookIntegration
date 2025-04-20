#!/usr/bin/php
<?php
$zip = new ZipArchive();
if($argv[1] == 'mysql'){ //run on database/rabbit machine
	$res = $zip->open('mysqlBundle.zip', (ZipArchive::CREATE | ZipArchive::OVERWRITE));
	if($res === true){
		echo "Making zip..." . PHP_EOL;
		$options = array('add_path' => '/', 'remove_all_path' => TRUE);
		$options2 = array('add_path' => 'sql_programs/', 'remove_all_path' => TRUE);
		$zip->addGlob('/srv/*.*', GLOB_BRACE, $options);
		$zip->addGlob('/srv/sql_programs/*.*', GLOB_BRACE, $options2);
		$zip->close();	
	}
}
else if($argv[1] == 'web'){ //run on web/backend machine
	$res = $zip->open('webBundle.zip', (ZipArchive::CREATE | ZipArchive::OVERWRITE));
	if($res === true){
		echo "Making zip..." . PHP_EOL;
		$options = array('add_path' => '/', 'remove_all_path' => TRUE);
		$options2 = array('add_path' => 'node_modules/', 'remove_all_path' => TRUE);
		$options3 = array('add_path' => 'vendor/', 'remove_all_path' => TRUE);
		$zip->addGlob('/var/www/sample/*.*', GLOB_BRACE, $options);
		$zip->addGlob('/var/www/sample/node_modules/*.*', GLOB_BRACE, $options2);
		$zip->addGlob('/var/www/sample/vendor/*.*', GLOB_BRACE, $options3);
		$zip->close();	
		
	}
}
else if($argv[1] == 'dmz'){ //run on dmz machine
	$machine = $zip->open('dmzBundle.zip', (ZipArchive::CREATE | ZipArchive::OVERWRITE));
	if($res === true){
		echo "Making zip..." . PHP_EOL;
		$options = array('add_path' => '/', 'remove_all_path' => TRUE);
		$zip->addGlob('/srv/*.*', GLOB_BRACE, $options);
		$zip->close();	
	}
}


?>
