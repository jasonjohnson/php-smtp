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
	var $log;
	var $socket;
	var $buffer;
	var $date;
	var $to;
	var $from;
	var $domains;
	var $complete;
	
	var $is_authenticated;
	var $is_local_account;
	
	function SMTP_Server_Session($socket) {
		$this->log = new SMTP_Server_Log();
		$this->socket = $socket;
		$this->date = time();
		$this->to = array();
		$this->from = array();
		$this->complete = false;
		
		$this->domains = array(
			'localhost',
			'127.0.0.1',
		);
		
		$this->is_authenticated = false;
		$this->is_local_account = false;
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
			case 'AUTH': $this->AUTH($arg); break;
			case 'MAIL': $this->MAIL($arg); break;
			case 'RCPT': $this->RCPT($arg); break;
			case 'DATA': $this->DATA($arg); break;
			case 'RSET': $this->RSET($arg); break;
			case 'HELP': $this->HELP($arg); break;
			case 'QUIT': $this->QUIT($arg); break;
			default: $this->NOT_IMPLEMENTED();
		}
	}
			
	function HELO($arg) {
		$this->socket->write(SMTP_250);
	} 
	
	function AUTH($arg) {
		if(substr($arg, 0, 8) == 'CRAM-MD5') {
			$this->AUTH_CRAM_MD5();
		}
		
		if(substr($arg, 0, 5) == 'PLAIN') {
			$this->AUTH_PLAIN(substr($arg, 6));
		}
		
		if(substr($arg, 0, 5) == 'LOGIN') {
			$this->AUTH_LOGIN();
		}
	}
	
	function AUTH_CRAM_MD5() {
		$this->socket->write(SMTP_504);
	}
	
	function AUTH_LOGIN() {
		$this->socket->write(SMTP_504);
	}
	
	function AUTH_PLAIN($arg) {
		list($auth_id, $user_id, $password) = explode(chr(0),base64_decode($arg));
		
		$this->log->msg(SMTP_DEBUG, "AUTH ID: ".$auth_id);
		$this->log->msg(SMTP_DEBUG, "USER ID: ".$user_id);
		$this->log->msg(SMTP_DEBUG, "PASSWORD: ".$password);
		
		$this->socket->write(SMTP_235);
		
		$this->is_authenticated = true;
	}
	
	function MAIL($arg) {
		$arg = trim($arg, 'FROM: <>');
		$arr = explode('@', $arg);
		
		$this->from['user'] = $arg[0];
		$this->from['domain'] = $arg[1];
		
		$this->socket->write(SMTP_250);
	}
	
	function RCPT($arg) {
		$arg = trim($arg, 'TO: <>');
		$arr = explode('@', $arg);
		
		$this->to['user'] = $arr[0];
		$this->to['domain'] = $arr[1];
		
		if(!in_array($this->to['domain'], $this->domains) && !$this->is_authenticated) {
			$this->socket->write(SMTP_550);
			return;
		}
		
		$this->socket->write(SMTP_250);
	}
	
	function DATA($arg) {
		if(!$this->to) {
			$this->socket->write(SMTP_503);
			return;
		}
		
		$this->socket->write(SMTP_354);
		
		$file = $this->is_authenticated?SMTP_OUTBOUND:SMTP_INBOUND;
		$file .= $this->date."-".$this->to['user']."-".$this->to['domain'];
		
		$size = 0;
		$size_exceeded = false;
		
		if($msg = fopen($file, 'w+')) {
			while($this->buffer = $this->socket->read()) {
				$size += SMTP_CHUNK_SIZE;
				
				if($size > SMTP_MAX_SIZE) {
					$size_exceeded = true;
					break;
				}
				
				fwrite($msg, $this->buffer);
				
				if(substr($this->buffer, -5) == "\r\n.\r\n") {
					break;
				}
			}
			
			fclose($msg);
		}
		
		if(!$size_exceeded) {
			$this->socket->write(SMTP_250);
		} else {
			$this->socket->write(SMTP_552);
		}
		
		$this->complete = true;
	}
	
	function RSET($arg) {
		$this->socket->write(SMTP_250);
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