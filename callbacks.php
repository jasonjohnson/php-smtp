<?php
/**
 * SMTP_Server
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

function simple_auth($auth_id, $user_id, $password) {
	$users = array(
		array('jason','123'),
		array('brian','456'),
		array('matty','789')
	);
	
	for($i = 0; $i < count($users); $i++) {
		if($user_id == $users[$i][0] && $password == $users[$i][1]) {
			return true;
		}
	}
	
	return false;
}

$api->register_hook(SMTP_API_AUTH, 'simple_auth');
?>