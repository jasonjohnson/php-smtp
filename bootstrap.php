<?php
/**
 * bootstrap
 *
 * @author Jason Johnson <jason@php-smtp.org>
 * @copyright Copyright (c) 2008, Jason Johnson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 * @package php-smtp
 */

passthru('php ./php-smtp.php&');
passthru('php ./php-smtp-relay.php&');
?>