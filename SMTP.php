<?php
/**
 * SMTP
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

/**
 * Report only critical errors. Set this to E_ALL to report all PHP errors.
 */
error_reporting(E_ERROR);

/**
 * Unlimited execution time to accomodate the infinite run() loop.
 */
set_time_limit(0);

require_once 'SMTP_Server_Log.php';
require_once 'SMTP_Server_Socket.php';
require_once 'SMTP_Server_Session.php';
require_once 'SMTP_Server.php';

$server = new SMTP_Server();
$server->run();	
?>