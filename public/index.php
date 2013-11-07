<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
error_reporting(E_ALL);
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

define('SITE_LANGUAGE_IMAGE_PATH', getcwd().'/public/images/flags');
define('SITE_BANNER_IMAGE_PATH', getcwd().'/public/images/banners');
define('SITE_LOGO_IMAGE_PATH', getcwd().'/public/images/logos');
define('SITE_HP_IMAGE_PATH', getcwd().'/public/images/HPIcons');
define('APP_FOLDER_PATH', getcwd().'/public/app-content');
define('APP_FOLDER_CREATION_PATH', getcwd().'/public/');

// Config vars
if($_SERVER['HTTP_HOST'] == 'snapstatelocal.com') {
	define('HOST', 'mongodb://localhost:27017');
	define('USERNAME', 'snapstate');
	define('PASSWORD', 'snapstate');
	define('DATABASE', 'snapstate');
	define('ADMIN_USER_ID', '5267a1b88ead0eab15000000');
} else {
	define('HOST', 'mongodb://localhost:27017');
	define('USERNAME', 'snapstate');
	define('PASSWORD', '1et2s!tp3831!o');
	define('DATABASE', 'snapstate');
	define('ADMIN_USER_ID', '526f3f745a41226975cd9676');
}

// Run the application!

Zend\Mvc\Application::init(require 'config/application.config.php')->run();
