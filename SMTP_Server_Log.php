<?php
/**
 * SMTP_Server_Log
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

define(SMTP_LOG, './log/smtp.log');

define(SMTP_CRITICAL, 1);
define(SMTP_ERROR, 2);
define(SMTP_WARNING, 3);
define(SMTP_NOTICE, 4);
define(SMTP_DEBUG, 5);

class SMTP_Server_Log {
	var $handle;
	
	function open() {
		$this->handle = fopen(SMTP_LOG, 'a');
	}
	
	function write($str) {
		fwrite($this->handle, ($str."\n"));
	}
	
	function close() {
		fclose($this->handle);
	}
	
	/**
	 * Log a message to the logfile specified in the constant SMTP_LOG
	 * 
	 * @param int $level Takes SMTP_CRITICAL, SMTP_ERROR, SMTP_WARNING, SMTP_NOTICE, or SMTP_DEBUG
	 * @param string $msg The message to write to the logfile
	 */
	function msg($level, $msg) {
		$this->open();
		
		$time = date('j/M/Y:G:i:s O', time());
				
		switch($level) {
			case SMTP_CRITICAL: $level = 'CRITICAL'; break;
			case SMTP_ERROR: $level = 'ERROR'; break;
			case SMTP_WARNING: $level = 'WARNING'; break;
			case SMTP_NOTICE: $level = 'NOTICE'; break;
			case SMTP_DEBUG: $level = 'DEBUG'; break;
		}
		
		$this->write("[$level] - [$time] - $msg");
		$this->close();
	}
}
?>