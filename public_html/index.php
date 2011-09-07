<?php
/**
 * RedditSigs.com - Front End
 * 
 * Nothing fancy. Dispatches request to the Hagfish controller
 * (see /app/controllers/reddit_sigs.class.php for setup etc)
 * 
 * @author     Phil Newton <phil@sodaware.net>
 * @copyright  2011 Phil Newton <phil@sodaware.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since      File available since Release 1.0.0
 */


// Core code
require_once dirname(__FILE__) . '/../app/controllers/reddit_sigs.class.php';

// Create + dispatch
try {
	$app = new RedditSigs();
	$app->dispatch();
} catch (Exception $e) {
	error_log($e->getMessage());
	echo "Something went wrong. Sorry!";
	die;
}