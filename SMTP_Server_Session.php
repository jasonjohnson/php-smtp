<?php
/**
 * SMTP_Server_Session
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

class SMTP_Server_Session {
	var $socket;
	var $buffer;
	var $date;
	var $to;
	var $from;
	var $domains;
	var $complete;
	
	function SMTP_Server_Session($socket) {
		$this->socket = $socket;
		$this->date = time();
		$this->to = array();
		$this->from = array();
		$this->complete = false;
		
		$this->domains = array(
			'localhost',
			'127.0.0.1',
		);
	}
	
	/**
	 * Enters a loop to read and process incoming commands, exits when $this->complete is true
	 */
	function run() {
		$this->socket->write(SMTP_220);
		
		while($this->buffer = $this->socket->read()) {
			$cmd = substr($this->buffer, 0, 4);
			$arg = trim(substr($this->buffer, 5));
			
			$this->dispatch($cmd, $arg);
			
			if($this->complete) {
				break;
			}
		}
		
		$this->socket->write(SMTP_221);
		$this->socket->close();
	}
	
	function dispatch($cmd, $arg) {			
		switch($cmd) {
			case 'HELO':
			case 'EHLO': $this->HELO($arg); break;
			case 'MAIL': $this->MAIL($arg); break;
			case 'RCPT': $this->RCPT($arg); break;
			case 'DATA': $this->DATA($arg); break;
			case 'HELP': $this->HELP($arg); break;
			case 'QUIT': $this->QUIT($arg); break;
			default: $this->NOT_IMPLEMENTED();
		}
	}
			
	function HELO($arg) {
		$this->socket->write(SMTP_250);
	} 
	
	function MAIL($arg) {
		$arg = trim($arg, 'FROM:<>');
		$arr = explode('@', $arg);
		
		$this->from['user'] = $arg[0];
		$this->from['domain'] = $arg[1];
		
		$this->socket->write(SMTP_250);
	}
	
	function RCPT($arg) {
		$arg = trim($arg, 'TO:<>');
		$arr = explode('@', $arg);
		
		$this->to['user'] = $arr[0];
		$this->to['domain'] = $arr[1];
		
		if(!in_array($this->to['domain'], $this->domains)) {
			$this->socket->write(SMTP_550);
			$this->socket->close();
			
			return;
		}
		
		$this->socket->write(SMTP_250);
	}
	
	function DATA($arg) {
		if(!$this->to) {
			$this->socket->write(SMTP_503);
			$this->socket->close();
			
			return;
		}
		
		$this->socket->write(SMTP_354);
		
		$file = SMTP_DEBUGSMTP_INBOUND.$this->date."-".$this->to['user']."-".$this->to['domain'];
		
		if($msg = fopen($file, 'w+')) {
			while($this->buffer = $this->socket->read()) {
				fwrite($msg, $this->buffer);
				
				if(substr($this->buffer, -5) == "\r\n.\r\n") {
					break;
				}
			}
			
			fclose($msg);
		}
		
		$this->socket->write(SMTP_250);
		
		$this->complete = true;
	}
	
	function HELP($arg) {
		$this->socket->write(SMTP_250);
	}
	
	function QUIT($arg) {
		$this->socket->write(SMTP_221);
		$this->socket->close();
	}
	
	function NOT_IMPLEMENTED() {
		$this->socket->write(SMTP_502);
	}
}
?>