<?php
	
	error_reporting(E_ERROR);
	set_time_limit(0);
	
	require_once 'SMTP_Server.php';
	
	$server = new SMTP_Server('70.87.154.59');
	$server->run();
	
?>