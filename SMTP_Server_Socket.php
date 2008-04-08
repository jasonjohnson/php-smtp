<?php
/**
 * SMTP_Server_Socket
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

class SMTP_Server_Socket {
	var $log;
	var $socket;
	var $length;
	var $remote_address;
	var $debug;
	
	function SMTP_Server_Socket($socket = null) {
		$this->log = new SMTP_Server_Log();
		$this->socket = $socket;
		$this->length = 1024;
		$this->debug = true;
		$this->remote_address = '';
		
		if(!$this->socket) {
			$this->socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
		}
	}
	
	function bind($host, $port) {
		while(!socket_bind($this->socket, $host, $port)) {
			$this->log->msg(SMTP_NOTICE, "Binding...");
						
			sleep(5);
		}
		
		$this->log->msg(SMTP_NOTICE, "Bound!");
	}
			
	function listen() {
		$this->log->msg(SMTP_NOTICE, "Listening...");
		
		socket_listen($this->socket);
	}
	
	function accept() {
		$remote = socket_accept($this->socket);
		
		if(!socket_getpeername($remote, $this->remote_address)) {
			$this->log->msg(SMTP_WARNING, "Could not determine remote address");
		}
		
		$this->log->msg(SMTP_DEBUG, "Accepted connection from '".$this->remote_address."'");
		
		return new SMTP_Server_Socket($remote);
	}
	
	function remote_address() {
		return $this->remote_address;
	}
	
	function write($buffer) {
		$this->log->msg(SMTP_DEBUG, ">>> $buffer");
		
		socket_write($this->socket, ($buffer."\r\n"));
	}
	
	function read() {
		$buffer = socket_read($this->socket, $this->length);
		
		$this->log->msg(SMTP_DEBUG, "<<< $buffer");
			
		return $buffer;
	}
			
	function close() {
		$this->log->msg(SMTP_DEBUG, "Closing socket connection");
		
		socket_close($this->socket);
	}
}
?>