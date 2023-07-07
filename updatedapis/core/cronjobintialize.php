<?php

ob_start('ob_gzhandler');
// error_reporting(0);
define("APP_ROOT", dirname(dirname(__FILE__)));
define("CORE_PATH", APP_ROOT . "/core");
define("PRIVATE_PATH", APP_ROOT . "/API");


// require_once(CORE_PATH . "/Headers.php");
require_once(CORE_PATH . "/functions/request_forgery_functions.php");
require_once(CORE_PATH . "/functions/api.functions.php");
require_once(CORE_PATH . '/functions/file_upload.php');
require_once(CORE_PATH . "/functions/general-functions.php");
