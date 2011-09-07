<?php
/**
 * app/config/local/config.php
 * 
 * Local configuration for testing purposes.
 * 
 * @author     Phil Newton <phil@sodaware.net>
 * @copyright  2011 Phil Newton <phil@sodaware.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since      File available since Release 1.0.0
 */

// Assets directory (backgrounds, trophy images)
define ('ASSETS_PATH', dirname(__FILE__) . '/../../assets/');

// Cache settings
define ('CACHE_EXPIRY', 1);
define ('CACHE_PATH',  dirname(__FILE__) . '/../../cache/');

// Base image path
define ('IMAGE_BASE', 'http://redditsigs-local.com/image/%user%/');
