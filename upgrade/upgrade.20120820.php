<?php
	// PONGSOCKET TWEET ARCHIVE
	// Upgrade tables for OAuth integration

	require "inc/preheader.php";

	$db->query("ALTER TABLE `".DTP."tweetusers` ADD COLUMN oauth_token varchar(50), ADD COLUMN oauth_token_secret varchar(50)") or die($db->error());
	echo "Added oauth_token and oauth_token_secret columns to the users table.\n";
	echo "First, make sure your Twitter application credentials are stored in /inc/config.php\n";
	echo "Then visit /maintenance/oauth_redirect.php to load your credentials into the database.\n";

	echo "Done! You can delete me now.\n";