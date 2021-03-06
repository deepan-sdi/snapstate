<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
error_reporting(E_ALL);
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

define('SITE_IMAGE_PATH', '/Front/img/');
define('SITE_SCRIPT_PATH', '/Front/js/');
define('SITE_STYLE_PATH', '/Front/css/');

// Config vars
if($_SERVER['HTTP_HOST'] == 'snapstatelocal.com') {
	define('HOST', 'mongodb://localhost:27017');
	define('USERNAME', 'snapstate');
	define('PASSWORD', 'snapstate');
	define('DATABASE', 'snapstate');
	define('ADMIN_USER_ID', '5267a1b88ead0eab15000000');
	define('USER_GROUP_ID', '5270988f8ead0e2648000000');
	define('FB_RETURN_URL', 'http://snapstatelocal.com');
	define('FB_APPID', '353997868079462');
} else {
	define('HOST', 'mongodb://localhost:27017');
	define('USERNAME', 'snapstate');
	define('PASSWORD', '1et2s!tp3831!o');
	define('DATABASE', 'snapstate');
	define('ADMIN_USER_ID', '526f3f745a41226975cd9676');
	define('USER_GROUP_ID', '5270b3cf5a41220c46808733');
	define('FB_RETURN_URL', 'http://snapstate.sdiphp.com');
	define('FB_APPID', '');
}

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
	define('PROTOCOL', 'https');
} else {
	define('PROTOCOL', 'http');
}

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
