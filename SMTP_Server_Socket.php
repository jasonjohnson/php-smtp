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
	var $socket;
	var $length;
	var $remote_address;
	var $debug;
	
	function SMTP_Server_Socket($socket = null) {
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
			if($this->debug)
				print("[XXX] Binding...\n");
			
			sleep(5);
		}
		
		if($this->debug)
			print("[XXX] Bound!\n");
	}
			
	function listen() {
		if($this->debug)
			print("[XXX] Listening...\n");
		
		socket_listen($this->socket);
	}
	
	function accept() {
		$remote = socket_accept($this->socket);
		
		if(!socket_getpeername($remote, $this->remote_address)) {
			print("[XXX] Could not determine remote hostname!\n");
		}
		
		if($this->debug) {
			print("[XXX] Accepted connection from '".$this->remote_address."'\n");
		}
		
		return $remote;
	}
	
	function remote_address() {
		return $this->remote_address;
	}
	
	function write($buffer) {
		if($this->debug)
			print("[>>>] ".$buffer."\n");
		
		socket_write($this->socket, ($buffer."\r\n"));
	}
	
	function read() {
		$buffer = socket_read($this->socket, $this->length);
		
		if($this->debug)
			print("[<<<] ".$buffer."\n");
			
		return $buffer;
	}
			
	function close() {
		if($this->debug)
			print("[XXX] Closing socket connection\n");
		
		socket_close($this->socket);
	}
}
?>