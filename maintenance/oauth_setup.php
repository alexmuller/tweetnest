<?php

  session_start();
  require "mpreheader.php";
  $pageTitle = "Sign in to Tweet Nest";
  require "mheader.php";

  echo l("<a href='oauth_redirect.php'>");
  echo l("<img src='../styles/images/sign-in-with-twitter.png' />");
  echo l("</a>");

  require "mfooter.php";
