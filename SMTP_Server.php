<?php

	require_once 'SMTP_Server_Socket.php';
	require_once 'SMTP_Server_Session.php';

	class SMTP_Server {
		var $socket;
		var $host;
		var $port;
		var $remote;
		
		function SMTP_Server($host = '127.0.0.1', $port = 25) {
			$this->host = $host;
			$this->port = $port;
			$this->domains = array();
			
			$this->socket = new SMTP_Server_Socket();
			$this->socket->bind($this->host, $this->port);
			$this->socket->listen();
		}
		
		function run() {
			while(true) {
				$this->remote = $this->socket->accept();
				
				$session = new SMTP_Server_Session($this->remote);
				$session->run();
			}
			
			$this->socket->close();
		}
	}

?>