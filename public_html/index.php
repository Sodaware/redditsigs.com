<?php
/**
 * RedditSigs.com - Front End
 * 
 * Nothing fancy. Dispatches request to the Hagfish controller
 * (see /app/controllers/reddit_sigs.class.php for setup etc)
 */

// Core code
require_once dirname(__FILE__) . '/../app/controllers/reddit_sigs.class.php';

// Create + dispatch
$app = new RedditSigs();
$app->dispatch();