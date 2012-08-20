<?php

  session_start();
  require "mpreheader.php";
  $pageTitle = "Redirecting you to Twitter to authenticate...";
  require "mheader.php";
  require_once "../lib/twitteroauth.php";

  // Build TwitterOAuth object with client credentials.
  $connection = new TwitterOAuth($config['consumer_key'], $config['consumer_secret']);

  // Get temporary credentials.
  $callback_path = $config['twitter_callback'] + "maintenance/oauth_callback.php";
  $request_token = $connection->getRequestToken("http://localhost:80/maintenance/oauth_callback.php");

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
      echo '<pre>';
      echo $connection->http_code;
      echo print_r($connection->http_info);
      echo '</pre>';
  }

  require "mfooter.php";
