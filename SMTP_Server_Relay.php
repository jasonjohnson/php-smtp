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
	function scan_outgoing() {
		if($dir = opendir(SMTP_OUTBOUND)) {
			while($file_name = readdir($dir)) {
				if(substr($file_name, 0, 1) != '.') {
					$this->relay(SMTP_OUTBOUND.$file_name);
				}
			}		
			
			closedir($dir);
		}
	}
	
	function relay($file_name) {
		$session =& new SMTP_Server_Relay_Session($file_name);
		$session->run();
		
		$session = null;
	}
	
	function run() {
		while(true) {
			$this->scan_outgoing();
			
			sleep(10);
		}
	}
}
?>