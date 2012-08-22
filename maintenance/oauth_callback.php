<?php

	session_start();
	require "mpreheader.php";
	$pageTitle = "Authenticated with Twitter!";
	require "mheader.php";

	// If the oauth_token is old redirect to the connect page.
	if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
		session_destroy();
		header('Location: ./index.php?oauth_error');
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
		echo l("Authenticated fine with Twitter. Connecting to retrieve your user information...\n");
		$connection = new TwitterOAuth($config['consumer_key'], $config['consumer_secret'], $access_token['oauth_token'], $access_token['oauth_token_secret']);
		$data = $connection->get("users/show", array("screen_name" => $config['twitter_screenname']));
		if($data){
			$extra = array(
				"created_at" => (string) $data->created_at,
				"utc_offset" => (string) $data->utc_offset,
				"time_zone"  => (string) $data->time_zone,
				"lang"       => (string) $data->lang,
				"profile_background_color"     => (string) $data->profile_background_color,
				"profile_text_color"           => (string) $data->profile_text_color,
				"profile_link_color"           => (string) $data->profile_link_color,
				"profile_sidebar_fill_color"   => (string) $data->profile_sidebar_fill_color,
				"profile_sidebar_border_color" => (string) $data->profile_sidebar_border_color,
				"profile_background_image_url" => (string) $data->profile_background_image_url,
				"profile_background_tile"      => (string) $data->profile_background_tile
			);
			echo l("Checking...\n");
			$db->query("DELETE FROM `".DTP."tweetusers` WHERE `userid` = '0'"); // Getting rid of empty users created in error
			$q = $db->query("SELECT * FROM `".DTP."tweetusers` WHERE `userid` = '" . $db->s($data->id_str) . "' LIMIT 1");
			if($db->numRows($q) <= 0){
				$iq = "INSERT INTO `".DTP."tweetusers` (`userid`, `screenname`, `realname`, `location`, `description`, `profileimage`, `url`, `extra`, `enabled`, `oauth_token`, `oauth_token_secret`) VALUES ('" . $db->s($data->id_str) . "', '" . $db->s($data->screen_name) . "', '" . $db->s($data->name) . "', '" . $db->s($data->location) . "', '" . $db->s($data->description) . "', '" . $db->s($data->profile_image_url) . "', '" . $db->s($data->url) . "', '" . $db->s(serialize($extra)) . "', '1', '" . $access_token['oauth_token'] . "', '" . $access_token['oauth_token_secret'] . "');";
			} else {
				$iq = "UPDATE `".DTP."tweetusers` SET `screenname` = '" . $db->s($data->screen_name) . "', `realname` = '" . $db->s($data->name) . "', `location` = '" . $db->s($data->location) . "', `description` = '" . $db->s($data->description) . "', `profileimage` = '" . $db->s($data->profile_image_url) . "', `url` = '" . $db->s($data->url) . "', `extra` = '" . $db->s(serialize($extra)) . "', `oauth_token` = '" . $access_token['oauth_token'] . "', `oauth_token_secret` = '" . $access_token['oauth_token_secret'] . "' WHERE `userid` = '" . $db->s($data->id_str) . "' LIMIT 1";
			}
			echo l("Updating...\n");
			$q = $db->query($iq);
			echo $q ? l(good("Done! Now load your tweets.")) : l(bad("DATABASE ERROR: " . $db->error()));
		} else { echo l(bad("No data! Try again later.")); }
	} else {
		// Save HTTP status for error dialog on connnect page.
		header('Location: ./index.php?oauth_error');
	}

	require "mfooter.php";
