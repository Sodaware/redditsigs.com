<?php
/**
 * app/config/prod.config.php
 * 
 * Production configuration.
 * 
 * @author     Phil Newton <phil@sodaware.net>
 * @copyright  2011 Phil Newton <phil@sodaware.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since      File available since Release 1.0.0
 */


// Assets directory (backgrounds, trophy images)
define ('ASSETS_PATH', dirname(__FILE__) . '/../../assets/');

// Cache settings
define ('CACHE_EXPIRY', 60 * 60);
define ('CACHE_PATH',  dirname(__FILE__) . '/../../cache/');

// Base image path
define ('IMAGE_BASE', 'http://redditsigs.com/image/%user%/');