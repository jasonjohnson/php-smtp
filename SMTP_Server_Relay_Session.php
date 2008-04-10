<?php
/**
 * SMTP_Server_Relay_Session
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

class SMTP_Server_Relay_Session {
	var $log;
	var $file_name;
	var $to;
	var $from;
	var $host;
	var $socket;
	var $headers = array();
	
	function SMTP_Server_Relay_Session($file_name) {
		$this->log = new SMTP_Server_Log();
		$this->socket = new SMTP_Server_Socket();
		$this->file_name = $file_name;
	}
	
	function resolve() {
		list($user, $domain) = $this->split_address($this->to);
		
		$hosts = array();
		$weight = array();
		
		getmxrr($domain, $hosts, $weight);
		
		for($i = 0; $i < count($hosts); $i++) {
			$this->log->msg(SMTP_DEBUG, "HOST #".$i." for '".$domain."' is '".$hosts[$i]."' with the weight '".$weight[$i]."'");
		}
		
		if(count($hosts) <= 0) {
			return false;
		}
		
		$this->host = $hosts[0];
	}
	
	function extract_address($header) {
		$matches = array();
		
		if(preg_match("/([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}/ix", $header, $matches) <= 0) {
			return false;
		}
		
		return $matches[0];
	}
	
	function split_address($address) {
		return explode('@', $address);
	}
	
	function scan_headers() {
		if($file = fopen($this->file_name, 'r')) {
			while(trim($line = fgets($file)) != '') {
				if(substr($line, 0, 3) == 'To:') {
					$this->to = $this->extract_address($line);
				}
				
				if(substr($line, 0, 5) == 'From:') {
					$this->from = $this->extract_address($line);
				}
				
				$this->headers[] = $line;
			}
			
			fclose($file);
		}
	}
	
	function send() {
		$this->socket->connect($this->host);
		
		while(true) {
			if(!$this->HELO()) break;
			if(!$this->MAIL()) break;
			if(!$this->RCPT()) break;
			if(!$this->DATA()) break;
			if(!$this->QUIT()) break;
			
			break;
		}
		
		$this->socket->close();
	}
	
	function HELO() {
		$this->socket->write("HELO");
		
		if(substr($this->socket->read(), 0, 1) != '2') {
			return false;
		}
		
		return true;
	}
	
	function MAIL() {
		$this->socket->write("MAIL FROM: <".$this->from.">");
		
		if(substr($this->socket->read(), 0, 1) != '2') {
			return false;
		}
		
		return true;
	}
	
	function RCPT() {
		$this->socket->write("RCPT TO: <".$this->to.">");
		
		if(substr($this->socket->read(), 0, 1) != '2') {
			return false;
		}
		
		return true;
	}
	
	function DATA() {
		$this->socket->write("DATA");
		
		if(substr($this->socket->read(), 0, 1) != '3') {
			return false;
		}
		
		$this->socket->write_file($this->file_name);
		
		if(substr($this->socket->read(), 0, 1) != '2') {
			return false;
		}
		
		return true;
	}
	
	function QUIT() {
		$this->socket->write("QUIT");
		$this->socket->read();
		
		return true;
	}
	
	function remove() {
		unlink($this->file_name);
	}
	
	function run() {
		$this->scan_headers();
		$this->resolve();
		$this->send();
		$this->remove();
		
		$this->log->msg(SMTP_DEBUG, "RELAYED MESSAGE TO '".$this->to."' FROM '".$this->from."'");
	}
}
?>