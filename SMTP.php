<?php
	
	error_reporting(E_ERROR);
	set_time_limit(0);
	
	require_once 'SMTP_Server_Log.php';
	require_once 'SMTP_Server_Socket.php';
	require_once 'SMTP_Server_Session.php';
	require_once 'SMTP_Server.php';
	
	$server = new SMTP_Server();
	$server->run();
	
?>