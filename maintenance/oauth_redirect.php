<?php

	session_start();
	require "mpreheader.php";
	$pageTitle = "Redirecting you to Twitter to authenticate...";
	require "mheader.php";

	// Build TwitterOAuth object with client credentials.
	$connection = new TwitterOAuth($config['consumer_key'], $config['consumer_secret']);

	// Get temporary credentials.
	$request_token = $connection->getRequestToken($config['twitter_callback']);

	// Save temporary credentials to session.
	$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

	switch ($connection->http_code) {
		case 200:
			// Build authorize URL and redirect user to Twitter.
			$url = $connection->getAuthorizeURL($token);
			header('Location: ' . $url); 
			break;
		default:
			// Show notification if something went wrong.
			echo 'Could not connect to Twitter. Refresh the page or try again later.';
	}

	require "mfooter.php";
