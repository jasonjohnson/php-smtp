<?php

	class SMTP_Server_Session {
		var $socket;
		var $buffer;
		var $date;
		var $to;
		var $from;
		var $domains;
		var $complete;
		
		function SMTP_Server_Session($socket) {
			$this->socket = new SMTP_Server_Socket($socket);
			$this->date = time();
			$this->to = array();
			$this->from = array();
			$this->complete = false;
			
			$this->domains = array(
				'localhost',
				'127.0.0.1',
			);
		}
		
		function run() {
			$this->socket->write("220 Ready");
			
			while($this->buffer = $this->socket->read()) {
				$cmd = substr($this->buffer, 0, 4);
				$arg = trim(substr($this->buffer, 5));
				
				$this->dispatch($cmd, $arg);
				
				if($this->complete) {
					break;
				}
			}
			
			$this->socket->write("221 Goodbye");
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
			$this->socket->write("250 Hello");
		} 
		
		function MAIL($arg) {
			$arg = trim($arg, 'FROM:<>');
			$arr = explode('@', $arg);
			
			$this->from['user'] = $arg[0];
			$this->from['domain'] = $arg[1];
			
			$this->socket->write("250 Sender OK");
		}
		
		function RCPT($arg) {
			$arg = trim($arg, 'TO:<>');
			$arr = explode('@', $arg);
			
			$this->to['user'] = $arr[0];
			$this->to['domain'] = $arr[1];
			
			if(!in_array($this->to['domain'], $this->domains)) {
				$this->socket->write("550 Mailbox unavailable");
				$this->socket->close();
				
				return;
			}
			
			$this->socket->write("250 Recipient OK");
		}
		
		function DATA($arg) {
			if(!$this->to) {
				$this->socket->write("503 Bad sequence of commands");
				$this->socket->close();
				
				return;
			}
			
			$this->socket->write("354 Begin data");
			
			$file = "./inbound/".$this->date."-".$this->to['user']."-".$this->to['domain'];
			
			if($msg = fopen($file, 'w+')) {
				while($this->buffer = $this->socket->read()) {
					fwrite($msg, $this->buffer);
					
					if(substr($this->buffer, -5) == "\r\n.\r\n") {
						break;
					}
				}
				
				fclose($msg);
			}
			
			$this->socket->write("250 Message accepted for delivery");
			
			$this->complete = true;
		}
		
		function HELP($arg) {
			$this->socket->write("250 Available commands: HELO, MAIL, RCPT, DATA, HELP, QUIT");
		}
		
		function QUIT($arg) {
			$this->socket->write("221 Goodbye");
			$this->socket->close();
		}
		
		function NOT_IMPLEMENTED() {
			$this->socket->write("502 Command not implemented");
		}
	}

?>