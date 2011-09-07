<?php
/**
 * app/actions/image_action.class.php
 * 
 * Generates signature images.
 * 
 * @author     Phil Newton <phil@sodaware.net>
 * @copyright  2011 Phil Newton <phil@sodaware.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since      File available since Release 1.0.0
 */


class ImageAction extends HagfishAction
{
	
	// ----------------------------------------------------------------------
	// -- Main execution 
	// ----------------------------------------------------------------------

	/**
	 * Generates and displays a signature image
	 */	
	public function execute(HagfishRequest $request)
	{
		// Get inputs
		$username 	= $request->getParameter('username');
		$theme		= strtolower($request->getParameter('style', 'dark'));
		
		// Check a username was submitted 
		if (!$username) {
			echo "no username submitted";
			return;
		}
		
		// Check theme is valid. If not, default to "dark"
		$validThemes = array('dark', 'light');
		if (!in_array($theme, $validThemes)) {
			$theme = 'dark';
		}
		
		// Check for cached image
		$outputName	= $this->_getCachedImageName($username, $theme);
		if ($this->_hasCachedImage($outputName)) {
			
			// If not modified, don't send any data
			if ($this->_imageNotModifiedSince($outputName)) {
				header ('HTTP/1.1 304 Not Modified');
				return;
			}
			
			// Display the cached image
			if (!$this->_cachedImageExpired($outputName)) {
				$this->display($outputName);
				return;
			}
		}
		
		// Generate the image
		$imageFile = $this->_generateImage($username, $theme, $outputName);
		$this->display($imageFile);
		
	}
	
	
	// ----------------------------------------------------------------------
	// -- Cache control
	// ----------------------------------------------------------------------

	private function _getCachedImageName($username, $theme)
	{
		return CACHE_PATH . $username . '-' . $theme . '.png';
	}
	
	protected function _hasCachedImage($fileName)
	{
		return file_exists($fileName);
	}
	
	protected function _imageNotModifiedSince($fileName)
	{
		if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			return false;
		}

		if (!file_exists($fileName)) {
			return false;
		}
		
		$fileTime = @filemtime($fileName);
		if ($fileTime) {
			$modifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			
			if ($modifiedSince < $fileTime) {
				return true;
			}
		}
		
		return false;
        
	}
	
	protected function _cachedImageExpired($fileName)
	{
		$fileTime = @filemtime($fileName);
		return ((time() - $fileTime) > CACHE_EXPIRY);
	}

	
	// ----------------------------------------------------------------------
	// -- Image display 
	// ----------------------------------------------------------------------
	
	public function display($fileName)
	{
		// Check image can be served
		$imageInfo = getimagesize($fileName);
		if (!$imageInfo || !$imageInfo['mime']) {
			return false;
		}
		
		// Serve the image
		header('Content-Type: ' . $imageInfo['mime']);
		header('Content-Length: ' . filesize($fileName));
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
        
        // Read image & display
		return readfile($fileName);
	}
	
	
	// ----------------------------------------------------------------------
	// -- Image generation
	// ----------------------------------------------------------------------
	
	private function _generateImage($username, $theme, $outputImage)
	{
		
		
		// Load base image
		$signatureImage	= imagecreatefrompng(ASSETS_PATH . 'base-' . $theme . '.png');
		$redditLogo		= imagecreatefrompng(ASSETS_PATH . 'logo-' . $theme . '.png');
		
		$colours = array(
			'dark' => array(
				'font'		=> '#ffffff',
				'outline'	=> '#000000'
			),
			'light' => array(
				'font'		=> '#000000',
				'outline'	=> '#ffffff'
			)
		);
		
		// Setup our colours
		$fontColourData		= $this->_convertHtmlColour($colours[$theme]['font']);
		$outlineColourData	= $this->_convertHtmlColour($colours[$theme]['outline']);
		
		$fontColour		= imagecolorallocate($signatureImage, $fontColourData['r'], $fontColourData['g'], $fontColourData['b']);
		$outlineColour	= imagecolorallocate($signatureImage, $outlineColourData['r'], $outlineColourData['g'], $outlineColourData['b']);
		
		// Grab redditor information
		$rater = new RedditRater($username);
		$rater->setCachePath(CACHE_PATH);
		$rater->fetchUserData();
		$rater->fetchTrophyData();
		
		$data		= $rater->getUserData();
		$trophies	= $rater->getTrophyData();
		
		$level 			= $rater->calculateLevel();
		$commentKarma 	= $data['comment_karma'];
		$linkKarma 		= $data['link_karma'];
		
		// Generate info
		$levelText = "level $level redditor";
		
		// Username
		imagestring($signatureImage, 5, 10, 5, $username, $fontColour);
		imagestring($signatureImage, 3, 10, 20, $levelText, $fontColour);
		imagestring($signatureImage, 3, 10, 34, "$commentKarma comment karma", $fontColour);
		imagestring($signatureImage, 3, 10, 48, "$linkKarma link karma", $fontColour);

		// Doodad
		imagestring($signatureImage, 2, imagesx($signatureImage) - 92, 72, "redditsigs.com", $fontColour);
		
		imagecopy ($signatureImage, $redditLogo, 240, 10, 0, 0, 38, 38);
		
		// Trophies
		if ($trophies && count($trophies)) {
			$trophyCount = 0;
			$xPos		= 0;
			
			while ($trophyCount < 5 && $trophyCount < count($trophies)) {
				$badge = $trophies[$trophyCount];
				$badgeImage = @imagecreatefrompng(ASSETS_PATH . $badge['image']);
				
				if ($badgeImage) {
					imagecopyresampled ($signatureImage, $badgeImage, 10 + ($xPos * 24), 68, 0, 0, 20, 20, 40, 40);
					$xPos++;
				}
				
				$trophyCount++;
			}
		}
				
		imagepng($signatureImage, $outputImage);
		return $outputImage;
		
	}
	
	private function _convertHtmlColour($p_Colour)
	{
		// Strip the trailing # symbol
		$colourCode = str_replace('#', '', $p_Colour);
		
		// Get the seperate parts of the colour
		$colourRed		= substr($colourCode, 0, 2);
		$colourGreen	= substr($colourCode, 2, 2);
		$colourBlue		= substr($colourCode, 4, 2);
		$colourBlue		= substr($colourCode, 4, 2);
		
		// Convert the HEX to Decimal
		$newColour	= array(
			'r'	=> base_convert($colourRed, 16, 10),
			'g'	=> base_convert($colourGreen, 16, 10),
			'b'	=> base_convert($colourBlue, 16, 10)
		);
		
		return $newColour;
	}
}
