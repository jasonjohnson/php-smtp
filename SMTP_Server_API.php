<?php
/**
 * SMTP_Server_API
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

class SMTP_Server_API {
	var $events = array();
	var $hooks = array();
	
	function register_event($event, $function) {
		$this->events[$event][] = $function;
	}
	
	function register_hook($hook, $function) {
		$this->hooks[$hook][] = $function;
	}
	
	function event() {
		$args = func_get_args();
		$event = array_shift($args);
		
		for($i = 0; $i < count($this->events[$event]); $i++) {
			call_user_func_array($this->events[$event][$i], $args);
		}
	}
	
	function hook() {
		$args = func_get_args();
		$hook = array_shift($args);
		
		for($i = 0; $i < count($this->hooks[$hook]); $i++) {
			if(call_user_func_array($this->hooks[$hook][$i], $args) == false) {
				return false;
			}
		}
		
		return true;
	}
}
?>