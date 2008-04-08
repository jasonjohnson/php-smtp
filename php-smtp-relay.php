<?php
/**
 * php-smtp-relay
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

require_once 'SMTP.php';

/**
 * Start an instance of the relay
 */
$relay = new SMTP_Server_Relay();
$relay->run();
?>