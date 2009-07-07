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
 * Check user access and cquire IP address
 */
// Make sure we are root in order to obtain port 25
if(exec("whoami") != "root")
	die("Must be root in order to start server properly\n");

// Make sure we have enough arguments to continue
if(count($argv) < 2)
	die("Usage: php-smtp.php ip-address [port]\n");

// Finally, use the arguments or default to configuration values later
$ip = $argv[1]?$argv[1]:null;
$port = $argv[2]?$argv[2]:null;


/**
 * Start an instance of the server
 */
$server = new SMTP_Server($ip, $port);
$server->run();
?>