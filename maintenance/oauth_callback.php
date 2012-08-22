<?php

	session_start();
	require "mpreheader.php";
	$pageTitle = "Authenticated with Twitter!";
	require "mheader.php";

	// If the oauth_token is old redirect to the connect page.
	if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
		session_destroy();
		header('Location: ./oauth_setup.php');
	}

	// Create TwitteroAuth object with app key/secret and token key/secret from default phase
	$connection = new TwitterOAuth($config['consumer_key'], $config['consumer_secret'], $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

	// Request access tokens from Twitter
	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

	// Remove no longer needed request tokens
	unset($_SESSION['oauth_token']);
	unset($_SESSION['oauth_token_secret']);

	// If HTTP response is 200 continue otherwise send to connect page to retry
	if (200 == $connection->http_code) {
		// The user has been verified and the access tokens can be saved for future use
		echo l("Authenticated fine with Twitter. Saving your tokens in the database now...\n");
		$iq = "UPDATE `".DTP."tweetusers` SET `oauth_token` = '" . $access_token['oauth_token'] . "', `oauth_token_secret` = '" . $access_token['oauth_token_secret'] . "' WHERE `userid` = '" . $access_token['user_id'] . "' LIMIT 1";
		$q = $db->query($iq);
		echo $q ? l(good("All done!")) : l(bad("DATABASE ERROR: " . $db->error()));
	} else {
		// Save HTTP status for error dialog on connnect page.
		header('Location: ./oauth_setup.php');
	}

	require "mfooter.php";
