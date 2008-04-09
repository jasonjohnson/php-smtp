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
	var $remote_address;
	
	/**
	 * Constructor with an optional argument of a socket resource
	 *
	 * @param resource $socket A socket resource as returned by socket_create() or socket_accept()
	 */
	function SMTP_Server_Socket($socket = null) {
		$this->log = new SMTP_Server_Log();
		$this->socket = $socket;
		$this->remote_address = '';
		
		if(!$this->socket) {
			$this->socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
		}
	}
	
	/**
	 * Binds the socket to a host and port
	 *
	 * @param string $host The host to bind the socket to
	 * @param int $port The port on the host to use
	 */
	function bind($host, $port) {
		while(!socket_bind($this->socket, $host, $port)) {
			$this->log->msg(SMTP_NOTICE, "Binding...");
						
			sleep(5);
		}
		
		$this->log->msg(SMTP_NOTICE, "Bound!");
	}
	
	/**
	 * Cause the socket to listen for incoming connections
	 */
	function listen() {
		$this->log->msg(SMTP_NOTICE, "Listening...");
		
		socket_listen($this->socket);
	}
	
	/**
	 * Block and accept an incoming connection
	 *
	 * @return SMTP_Server_Socket
	 */
	function accept() {
		$remote = socket_accept($this->socket);
		
		if(!socket_getpeername($remote, $this->remote_address)) {
			$this->log->msg(SMTP_WARNING, "Could not determine remote address");
		}
		
		$this->log->msg(SMTP_DEBUG, "Accepted connection from '".$this->remote_address."'");
		
		return new SMTP_Server_Socket($remote);
	}
	
	/**
	 * Returns the address of the remote client or server
	 *
	 * @return string
	 */
	function remote_address() {
		return $this->remote_address;
	}
	
	/**
	 * Writes the supplied buffer to the socket
	 *
	 * @param string $buffer The buffer to be written to the socket
	 */
	function write($buffer) {
		$this->log->msg(SMTP_DEBUG, ">>> $buffer");
		
		socket_write($this->socket, ($buffer."\r\n"));
	}
	
	/**
	 * Reads and returns a default length of bytes from the socket
	 *
	 * @return string
	 */
	function read() {
		$buffer = socket_read($this->socket, SMTP_CHUNK_SIZE);
		
		$this->log->msg(SMTP_DEBUG, "<<< $buffer");
			
		return $buffer;
	}
	
	/**
	 * Closes the socket
	 */
	function close() {
		$this->log->msg(SMTP_DEBUG, "Closing socket connection");
		
		socket_close($this->socket);
	}
}
?>