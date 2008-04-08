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
 * Report only critical errors. Set this to E_ALL to report all PHP errors
 */
error_reporting(E_ERROR);


/**
 * Unlimited execution time to accomodate the infinite run() loop
 */
set_time_limit(0);


/**
 * Messages and storage
 */
define(SMTP_INBOUND, './inbound/');
define(SMTP_OUTBOUND, './outbound/');


/**
 * SMTP response codes
 */
// 200's
define(SMTP_211, '211 System status, or system help reply');
define(SMTP_214, '214 Help message');
define(SMTP_220, '220 <domain> Service ready');
define(SMTP_221, '221 <domain> Service closing transmission channel');
define(SMTP_250, '250 Requested mail action okay, completed');
define(SMTP_251, '251 User not local; will forward to <forward-path>');

// 300's
define(SMTP_354, '354 Start mail input; end with <CRLF>.<CRLF>');

// 400's
define(SMTP_421, '421 <domain> Service not available,');
define(SMTP_450, '450 Requested mail action not taken: mailbox unavailable');
define(SMTP_451, '451 Requested action aborted: error in processing');
define(SMTP_452, '452 Requested action not taken: insufficient system storage');

// 500's
define(SMTP_500, '500 Syntax error, command unrecognized');
define(SMTP_501, '501 Syntax error in parameters or arguments');
define(SMTP_502, '502 Command not implemented');
define(SMTP_503, '503 Bad sequence of commands');
define(SMTP_504, '504 Command parameter not implemented');
define(SMTP_550, '550 Requested action not taken: mailbox unavailable');
define(SMTP_551, '551 User not local; please try <forward-path>');
define(SMTP_552, '552 Requested mail action aborted: exceeded storage allocation');
define(SMTP_553, '553 Requested action not taken: mailbox name not allowed');
define(SMTP_554, '554 Transaction failed');


/**
 * Logging
 */
define(SMTP_LOG, './log/smtp.log');
define(SMTP_CRITICAL, 1);
define(SMTP_ERROR, 2);
define(SMTP_WARNING, 3);
define(SMTP_NOTICE, 4);
define(SMTP_DEBUG, 5);

// Log level, set to SMTP_DEBUG to report all server-server and client-server communication
define(SMTP_LOG_LEVEL, SMTP_DEBUG);


require_once 'SMTP_Server_Log.php';
require_once 'SMTP_Server_Socket.php';
require_once 'SMTP_Server_Session.php';
require_once 'SMTP_Server.php';


/**
 * Start an instance of the server
 */
$server = new SMTP_Server();
$server->run();	
?>