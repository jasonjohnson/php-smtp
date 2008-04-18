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
	var $index;
	var $socket;
	var $buffer;
	var $date;
	var $to;
	var $from;
	var $domains;
	var $complete;
	var $api;
	
	var $is_authenticated;
	var $is_local_account;
	
	function SMTP_Server_Session($socket) {
		global $api;
		
		$this->log = new SMTP_Server_Log();
		$this->index = new SMTP_Server_Index();
		
		$this->socket = $socket;
		$this->date = time();
		$this->to = array();
		$this->from = array();
		$this->complete = false;
		$this->api = &$api;
		
		$this->domains = explode(',', SMTP_VALID_DOMAINS);
		
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
			case 'HELO': $this->HELO($arg); break;
			case 'EHLO': $this->EHLO($arg); break;
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
	
	function EHLO($arg) {
		$this->socket->write('250-This host supports a few commands');
		$this->socket->write('250-AUTH PLAIN CRAM-MD5');
		$this->socket->write('250 HELP');
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
		
		if($this->api->hook(SMTP_API_AUTH, $auth_id, $user_id, $password)) {
			$this->is_authenticated = true;
			$this->socket->write(SMTP_235);
		} else {
			$this->is_authenticated = false;
			$this->socket->write(SMTP_550);
		}
	}
	
	function MAIL($arg) {
		$arg = trim($arg, 'FfRrOoMm: <>');
		$arr = explode('@', $arg);
		
		$this->from['user'] = $arr[0];
		$this->from['domain'] = $arr[1];
		
		$this->socket->write(SMTP_250);
	}
	
	function RCPT($arg) {
		$arg = trim($arg, 'TtOo: <>');
		$arr = explode('@', $arg);
		
		$to = array('user' => $arr[0], 'domain' => $arr[1]);
		
		if(!in_array($to['domain'], $this->domains) && !$this->is_authenticated) {
			$this->socket->write(SMTP_550);
			return;
		}
		
		$this->to[] = $to;
		$this->socket->write(SMTP_250);
	}
	
	function DATA($arg) {
		if(!$this->to) {
			$this->socket->write(SMTP_503);
			return;
		}
		
		$this->socket->write(SMTP_354);
		
		if($this->is_authenticated) {
			$file = SMTP_OUTBOUND;
		} else {
			$file = SMTP_INBOUND;
			$file .= $this->to['domain'].DIRECTORY_SEPARATOR;
			$file .= $this->to['user'].DIRECTORY_SEPARATOR;
		}
		
		// If the path does not exist, create it recursively
		if(!file_exists($file)) {
			mkdir($file, 0700, true);
		}
		
		$file .= $this->date."@".$this->to[0]['user']."@".$this->to[0]['domain'];
		
		$this->index->write($file, $this->to, $this->from, $this->date);
				
		$size = 0;
		$size_exceeded = false;
		
		if($msg = fopen($file, 'w')) {
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