<?php
/**
 * SMTP_Server_Index
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

class SMTP_Server_Index {
	var $handle;
	var $records = array();
	
	function SMTP_Server_Index() {
		$this->read();	
	}
	
	function open($mode = 'a') {
		$this->handle = fopen(SMTP_INDEX, $mode);
	}
	
	function read() {
		$this->open('r');
		
		while(!feof($this->handle)) {
			$this->records[] = fgetcsv($this->handle);
		}
		
		$this->close();
	}
	
	function write($file = "", $to = array(), $from = array(), $timestamp = 0) {
		$this->open();
		
		for($i = 0; $i < count($to); $i++)
			$to[$i] = ($to[$i]['user'].'@'.$to[$i]['domain']);
		
		$from = ($from['user'].'@'.$from['domain']);
		
		fputcsv($this->handle, array($file, join("\t", $to), $from, $timestamp));
		
		$this->close();
	}
	
	function get($index) {
		if(count($this->records[$index]) != 4)
			return false;
		
		$rec = $this->records[$index];
		$rec[1] = explode("\t", $rec[1]);
		
		return $rec;
	}
	
	function length() {
		return count($this->records);
	}
	
	function lock() {
		flock($this->handle, LOCK_EX);
	}
	
	function unlock() {
		flock($this->handle, LOCK_UN);
	}
	
	function truncate() {
		$this->open('r+');
		$this->lock();
		
		ftruncate($this->handle, 0);
		
		$this->unlock();
		$this->close();
	}
	
	function close() {
		fclose($this->handle);
	}
}
?>