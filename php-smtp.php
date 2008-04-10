<?php
/**
 * php-smtp
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

require_once 'SMTP.php';

/**
 * Create an instance of the API
 */
$api = new SMTP_Server_API();


/**
 * Include user-defined callbacks
 */
require_once 'callbacks.php';


/**
 * Start an instance of the server
 */
$server = new SMTP_Server();
$server->run();
?>