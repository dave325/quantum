<?php 
	
	$url = parse_url(databaseUrl);
	$server = $url["host"];
	$username = $url["user"];
	$password = $url["pass"];
	$db = substr($url["path"], 1);
	
	//Amazon info
	$accessKey = key;
	$secretKey = key;
	
?>