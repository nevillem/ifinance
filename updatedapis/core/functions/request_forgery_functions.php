<?php

  // Use with request_is_post() to block posting from off-site forms
  function request_is_same_domain() {
  	if(!isset($_SERVER['HTTP_REFERER'])) {
  		// No refererer sent, so can't be same domain
  		return false;
  	} else {
  		$referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
  		$server_host = $_SERVER['HTTP_HOST'];

  		// Uncomment for debugging
  		// echo 'Request from: ' . $referer_host . "<br />";
  		// echo 'Request to: ' . $server_host . "<br />";

  		return ($referer_host == $server_host) ? true : false;
  	}
  }

?>
