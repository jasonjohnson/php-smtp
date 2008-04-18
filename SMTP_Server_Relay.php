<?php
/**
 * SMTP_Server_Relay
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */
	
class SMTP_Server_Relay {
	var $log;
	var $index;
	
	function SMTP_Server_Relay() {
		$this->log = new SMTP_Server_Log();
		$this->index = new SMTP_Server_Index();
	}
		
	function relay($pos) {
		$session =& new SMTP_Server_Relay_Session($pos);
		$session->run();
		
		$session = null;
	}
	
	function run() {
		while(true) {
			$this->index->read();
			
			if($this->index->length() > 0) {
				for($pos = 0; $pos < $this->index->length(); $pos++) {
					$this->relay($pos);
				}
			}
			
			$this->index->truncate();
			
			sleep(10);
		}
	}
}
?>