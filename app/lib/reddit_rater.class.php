<?php
/**
 * app/lib/reddit_rater.class.php
 * 
 * Class to wrap some Reddit.com API functions, as well as a few extras (such
 * as retrieving badges, comments and links).
 * 
 * To use:
 * 	* Create a new RedditRater class
 * 	* Download data using a fetch method (such as fetchUserData)
 * 	* Access data using a get method (such as getUserData)
 * 
 * Example:
 * 
 *  // Download basic karma information
 * 	$rater = new RedditRater('Sodaware');
 * 	$rater->fetchUserData();
 *  $data = $rater->getUserData();
 *  echo "Comment Karma: " . $data['comment_karma'];
 * 
 * This class can optionally use a cache directory for storing downloaded API
 * responses and user pages. Recommended to keep things speedy.
 * 
 * Requires simple_html_dom.php (for retrieving trophies)
 * 
 * @author     Phil Newton <phil@sodaware.net>
 * @copyright  2011 Phil Newton <phil@sodaware.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since      File available since Release 1.0.0
 */

 
// Reddit API uri's for comments / user info
define ('REDDIT_USER_COMMENTS_URI', 'http://reddit.com/user/%user%/comments/.json');
define ('REDDIT_USER_ABOUT_URI', 'http://www.reddit.com/user/%user%/about.json');
define ('REDDIT_USER_PROFILE_URI', 'http://www.reddit.com/user/%user%/');

/**
 *
 */
class RedditRater
{
	private $_username;             /**< The redditor's username */
	
	// Setup
	private $_commentsToRetrieve;   /**< The number of comments to retrieve */
	private $_linksToRetrieve;      /**< The number of links to retrieve */
	private $_cachePath;			/**< Directory to store cached files in **/
	private $_cacheLifetime;		/**< Number of seconds a file stays cached **/
	
	// Downloaded user data
	private $_userData;             /**< Downloaded user data (karma etc) **/
	private $_commentData;          /**< Downloaded comment data **/
	private $_linkData;             /**< Downloaded link data **/
	private $_trophyData;           /**< Downloaded trophy **/
	
	
	// ----------------------------------------------------------------------
	// -- Option Setting
	// ----------------------------------------------------------------------

	/**
	 * Set the number of comments to retrieve when using fetchCommentData.
	 * @param int $comments The number of comments to retrieve.
	 */
	public function setCommentsToRetrieve($comments)
	{
		$this->_commentsToRetrieve	= $comments;
	}
	
	/**
	 * Set the number of links to retrieve when using fetchLinkData.
	 * @param int $links The number of links to retrieve.
	 */
	public function setLinksToRetrieve($links)
	{
		$this->_linksToRetrieve	= $links;
	}
	
	/**
	 * Set the path where cached data will be stored.
	 * @param string $path A valid directory to store files.
	 */
	public function setCachePath($path)
	{
		// Check directory exists
		if (!is_dir($path)) {
			throw new Exception("Cache path $path does not exist");
		}
		
		// Check for trailing slash
		if (substr($path, strlen($path) - 1) != '/') {
			$path .= '/';
		}
		
		$this->_cachePath = $path;
	}
	
	/**
	 * Set the maximum age of cache files.
	 * @param string $lifetime Max age of a cache file in seconds.
	 */
	public function setCacheLifetime($lifetime)
	{
		$this->_cacheLifetime = $lifetime;
	}


	// ----------------------------------------------------------------------
	// -- Getting data
	// ----------------------------------------------------------------------

	/**
	 * Gets basic user data and returns it as an array.
	 * @return array Array containing user data.
	 */
	public function getUserData()
	{
		return $this->_userData;
	}
	
	/**
	 * Gets the username.
	 * @return string Username.
	 */
	public function getUsername()
	{
		return $this->_username;
	}
	
	/**
	 * Gets comment data.
	 * @return array An array of comment data.
	 */
	public function getCommentData()
	{
		return $this->_commentData;
	}
	
	/**
	 * Gets link data.
	 * @return array An array of link data.
	 */	
	public function getLinkData()
	{
		return $this->_linkData;
	}
	
	/**
	 * Gets a list of trophies the user has earned.
	 * @return array Array of arrays containing trophy image & trophy name.
	 */
	public function getTrophyData()
	{
		return $this->_trophyData;
	}
	
		
	/**
	 * Calculate the user level, based on karma and trophy data. Weighed more 
	 * heavily towards link karma and number of trophies. Must fetch user data
	 * and trophy data to get an accurate level.
	 * 
	 * @return int user level
	 */
	public function calculateLevel()
	{
		$data				= $this->getUserData();
		$trophies			= $this->getTrophyData();
		
		$totalBadges		= count($trophies);
		$membershipLength	= floor((time() - $data['date_created']) / (60 * 60 * 24 * 7));
		$commentKarma		= $data['comment_karma'];
		$linkKarma			= $data['link_karma'];	
		
		// Calculate level
		$rawLevel			= 
			($totalBadges * 0.75) + 
			($membershipLength * 0.25) + 
			($commentKarma * 0.25) + 
			($linkKarma * 0.5);
		
		// Make it a more reasonable value
		return floor($rawLevel / 50);		
	}


	// ----------------------------------------------------------------------
	// -- Downloading data
	// ----------------------------------------------------------------------
	
	/**
	 * Fetches data for the current user. May fetch from the cache if caching is
	 * enabled and data is young enough. If not, will fetch from the reddit API.
	 * @return array Array of user data (date_created, link_karma, comment_karma)
	 */
	public function fetchUserData()
	{
		// Generate the URI to query
		$userUri = str_replace('%user%', strtolower($this->_username), REDDIT_USER_ABOUT_URI);
		
		// Fetch data
		$rawData = $this->_getPageData($userUri);
		
		// Check something was returned
		if (!$rawData) { 
			return null;
		}
		
		// Decode data and store in an array
		$data = json_decode($rawData);
		$this->_userData = array(
			'date_created'		=> $data->data->created,
			'link_karma'		=> $data->data->link_karma,
			'comment_karma'		=> $data->data->comment_karma,
			'is_gold'			=> $data->data->is_gold,
			'is_mod'			=> $data->data->is_mod,
			'id'				=> $data->data->id			
		);
		
		return $this->_userData;
	}
	
	/**
	 * Download user comments.
	 */
	public function fetchCommentData()
	{
		$this->_commentData = $this->_downloadComments();
	}

	public function fetchLinkData()
	{
		// TODO: Do this
	}
	
	/**
	 * Download trophy data.
	 * @return array Array containing trophies this user has earned.
	 */
	public function fetchTrophyData()
	{
		// Fetch the user's profile page
		$userUri = str_replace('%user%', strtolower($this->_username), REDDIT_USER_PROFILE_URI);
		$data = $this->_getPageData($userUri);
		
		// Check data was retrieved
		if (!$data) {
			return;
		}
		
		// Parse page to find trophy elements
		$dom = str_get_dom($data);
		$trophies = $dom->find('td.trophy-info div');
		
		// Store trophies (if any found)
		if ($trophies) {
			$this->_trophyData = array();
			foreach ($trophies as $trophy) {
				$this->_trophyData[] = array(
					'image'		=> basename($trophy->find('.trophy-icon', 0)->src),
					'name'		=> $trophy->find('.trophy-name', 0)->plaintext,
				);
			}
		}
		
		return $this->_trophyData;
	}

	
	
	// ----------------------------------------------------------------------
	// -- Internal data methods
	// ----------------------------------------------------------------------

	/**
	 * Gets the raw text data for a Reddit uri. Will attempt to retrieve the
	 * data from the cache first.
	 * @param string $uri The URI to retrieve.
	 * @return string Text data.
	 */
	private function _getPageData($uri)
	{
		// Generate cached file name
		if ($this->_cachePath) {
			
			$cacheFile	= $this->_cachePath . md5($uri);
			
			if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->_cacheLifetime) {
				return file_get_contents($cacheFile);
			}
			
		}
		
		// Fetch new data		
		// TODO: Handle 404/overloaded errors
		$content = @file_get_contents($uri);
		
		// Cache it
		if ($content && $this->_cachePath) {
				
			$cacheFile	= $this->_cachePath . md5($uri);
				
			@file_put_contents($cacheFile, $content);
			
		}

		return $content;
	}
	
	/**
	 * Download comment data. Not a nice function.
	 */
	private function _downloadcomments()
	{		
		$commentCount	= 0;
		$currentUri		= str_replace('%user%', strtolower($this->_userName), REDDIT_USER_COMMENTS_URI);
		$commentData	= array();

		while ($commentCount < $this->_commentsToRetrieve) {
			
			$commentPage = json_decode($this->_getPageData($currentUri));
			$commentData = array_merge($commentData, $commentPage->data->children);
			$commentCount += count($commentData->data->children);

			if (count($commentData->data->children) == 25) {
				$currentUri = str_replace('%user%', strtolower($this->_username), REDDIT_USER_COMMENTS_URI);
				$currentUri .= '?count=' . $commentCount . '&after=' . $commentData->data->children[24]->data->name;

			} else {
				break;
			}

		}
		
		return $commentData;
	}
	
	
	// ----------------------------------------------------------------------
	// -- Construction / Destruction
	// ----------------------------------------------------------------------

	/**
	 * Default constructor.
	 * @param string $username Reddit username.
	 */
	public function __construct($username)
	{
		$this->_username			= $username;
		$this->_commentsToRetrieve	= 50;
		$this->_linksToRetrieve		= 50;
		$this->_cacheLifetime		= 60 * 60; // 1 hour
	}

}
