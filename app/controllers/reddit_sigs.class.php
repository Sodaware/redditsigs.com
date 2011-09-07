<?php
/**
 * app/controllers/reddit_sigs.class.php
 * 
 * Main driver for RedditSigs.com. 
 * 
 * @author     Phil Newton <phil@sodaware.net>
 * @copyright  2011 Phil Newton <phil@sodaware.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since      File available since Release 1.0.0
 */


// LIVE version define. Used to hide advertising etc on local installations
define('LIVE', $_SERVER['SERVER_NAME'] == 'www.redditsigs.com' || $_SERVER['SERVER_NAME'] == 'redditsigs.com');

// Configuration (local / global)
require_once (LIVE) ? 
	dirname(__FILE__) . '/../config/prod.config.php' : 
	dirname(__FILE__) . '/../config/local.config.php';

// Hagfish & SimpleHTML
require_once dirname(__FILE__) . '/../../vendor/hagfish/hagfish.php';
require_once dirname(__FILE__) . '/../../vendor/simple_html_dom.php';

// Libraries
require_once dirname(__FILE__) . '/../lib/reddit_rater.class.php';

// Actions
foreach (glob(dirname(__FILE__) . '/../actions/*_action.class.php') as $fileName) {
	require_once $fileName;
}


/**
 * Main controller for RedditSigs.com 
 */
class RedditSigs extends HagfishController
{
	
	/**
	 *Set up controller options and register available actions.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// Set up template system
		$this->setTemplatePath(dirname(dirname(__FILE__)) . '/templates/');
		
		// Add all actions
		$this->addActions(
			
			// Regular Actions
			array('default'					=> array('DefaultAction', 'execute')),
			array('404'						=> array('DefaultAction', 'execute404')),
			array('image/%w:username%/%w:style%'	=> array('ImageAction', 'execute'))
			
		);
		
	}
	
}
