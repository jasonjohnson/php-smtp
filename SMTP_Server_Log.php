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
	function SMTP_Server_Log($path) {
		
	}
	
	function msg($level, $msg) {
		switch($level) {
			case SMTP_CRITICAL: break;
			case SMTP_ERROR: break;
			case SMTP_WARNING: break;
			case SMTP_NOTICE: break;
			case SMTP_DEBUG: break;
		}
	}
}
?>