<!DOCTYPE html>
<html>

<head>
	<title>RedditSigs.com</title>
	<link rel="stylesheet" href="/rsc/css/style.css" />
</head>

<body id="top">

<div id="site">
	
	<header>
		<h1>RedditSigs.com</h1>
	</header>

	<article>

		<p>Enter your reddit username to generate your signature:</p>

		<form method="POST">
			<input type="text" name="username" id="username" />
			<input type="submit" value="Create Signature" id="submit" />
		</form>

		<br />

		<?php if (isset($sig) && $sig) { ?>
			<textarea id="signature" rows="8" cols="80"><?php echo $sig; ?></textarea>
			
			<section id="signature-images">
			
				<p>
					Want an image? Here you go!
				</p>

				<?php if ($imageUri) { ?>
				
					<div style="overflow: hidden;">

						<span style="float: left; width: 50%;">
							<img src="<?php echo $imageUri; ?>dark" width="300" height="92" alt="Signature Image" /><br />
							<pre><?php echo $imageUri; ?>dark</pre>
						</span>

						<span style="float: left; width: 50%;">
							<img src="<?php echo $imageUri; ?>light" width="300" height="92" alt="Signature Image" /><br />
							<pre><?php echo $imageUri; ?>light</pre>
						</span>

					</div>

				<?php } ?>

			</section>
			
		<?php } ?>
				

		<br />

		<p>Thanks to <a href="http://www.reddit.com/user/asdofikjasdlfkjqwpea">asdofikjasdlfkjqwpea</a> for the idea (<a href="http://www.reddit.com/r/gaming/comments/k6g5a/why_i_dont_like_gaming_forums/c2hvob3">comment thread</a>)</p>


	</article>

	<footer>
		<p>Reddit is a trademark of reddit Inc &middot; redditsigs.com is not affiliated with reddit.com &middot; <a href="http://store.xkcd.com/reddit/#8bitredditalientshirt">8-bit alien</a></p>
		<p>Code by <a href="http://www.reddit.com/user/sodaware">sodaware</a> (<a href="http://twitter.com/sodaware">@sodaware</a>) &middot; <a href="http://sodaware.github.com/misc/redditsigs/">Info &amp; Source</a></p>
	</footer>
	
</div>

</body>
</html>