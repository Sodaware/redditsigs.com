<?php
/**
 * app/actions/default_action.class.php
 * 
 * The default action for RedditSigs.com. Nothing fancy, but handles the main
 * form and generates the ASCII signature.
 * 
 * @author     Phil Newton <phil@sodaware.net>
 * @copyright  2011 Phil Newton <phil@sodaware.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since      File available since Release 1.0.0
 */


class DefaultAction extends HagfishAction
{
	
	// ----------------------------------------------------------------------
	// -- Main execution 
	// ----------------------------------------------------------------------

	/**
	 * Displays the main index page
	 */	
	public function execute()
	{
		// Set up the template
		$this->setTemplateName('homepage');
		
		if (array_key_exists('username', $_POST) && $_POST['username']) {
			
	
			// Retrieve user details
			$rater = new RedditRater(trim($_POST['username']));
			$rater->setCachePath(CACHE_PATH);
			$rater->fetchUserData();
			$data = $rater->getUserData();

			if ($data) {

				$rater->fetchUserData();
				$rater->fetchTrophyData();
				
				$data		= $rater->getUserData();
				$trophies	= $rater->getTrophyData();
				
				$level 			= $rater->calculateLevel();
				$commentKarma 	= $data['comment_karma'];
				$linkKarma 		= $data['link_karma'];
				
				// Create signature
				$sig  = "    ******************************************************************\n";
				$sig .= "    * " . str_pad($rater->getUsername(), 56) . "O      *\n";
				$sig .= "    * " . str_pad("Level $level Human Redditor", 55) . "\|/     *\n";
				$sig .= "    * " . str_pad("$commentKarma Comment Karma", 56) . "|      *\n";
				$sig .= "    * " . str_pad("$linkKarma Link Karma", 55) . "/ \     *\n";
				$sig .= "    *                                                                *\n";
				$sig .= "    * Get your own Reddit signature at Redditsigs.com                *\n";
				$sig .= "    ******************************************************************\n";
				
				$imageUri = str_replace('%user%', $rater->getUsername(), IMAGE_BASE);
				
			} else {
				$sig = "User not found";
			}
			

			$this->registerVariables(array(
				'sig'		=> $sig,
				'imageUri'	=> @$imageUri
			));
			
		}
		
	}
	
	/**
	 * Displays the 404 page.
	 */
	public function execute404()
	{
		header('HTTP/1.0 404 Not Found');
		echo "Not found!";
	}
	
/*
 * 
if ($_POST['username']) {
	
	// Retrieve user details
	$rater = new RedditRater($_POST['username']);
	$rater->fetchUserData();
	$data = $rater->getUserData();

	if ($data) {
		
		$membershipLength	= time() - $data['date_created'];
		
		$level 			= floor($membershipLength / (60 * 60 * 24 * 7));
		$commentKarma 	= $data['comment_karma'];
		$linkKarma 		= $data['link_karma'];
		
		// Create signature
		$sig  = "    ******************************************************************\n";
		$sig .= "    * " . str_pad($rater->getUsername(), 56) . "O      *\n";
		$sig .= "    * " . str_pad("Level $level Human Redditor", 55) . "\|/     *\n";
		$sig .= "    * " . str_pad("$commentKarma Comment Karma", 56) . "|      *\n";
		$sig .= "    * " . str_pad("$linkKarma Link Karma", 55) . "/ \     *\n";
		$sig .= "    *                                                                *\n";
		$sig .= "    * Get your own Reddit signature at Redditsigs.com                *\n";
		$sig .= "    ******************************************************************\n";

	} else {
		$sig = "User not found";
	}

}

?>
 */	
}
