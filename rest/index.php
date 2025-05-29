<?php
@ini_set('zlib.output_compression', 'Off');
@ini_set('output_buffering', 'Off');
@ini_set('output_handler', '');
@apache_setenv('no-gzip', 1);
header('Access-Control-Allow-Origin: *');
unset($_COOKIE);

ob_start();
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH),
    get_include_path(),
)));

// Define path to data directory
defined('APPLICATION_DATA')
    || define('APPLICATION_DATA', realpath(dirname(__FILE__) . '/../../data/logs'));

function __autoload($path) {
	return include str_replace('_', '/', $path) . '.php';
}

define("NO_SESSION_START","1");
define("KALILAB_SESSION_NO_UPDATE","1");

include_once ("../include/conf.inc.php");
include_once ("../include/lib.inc.php");

$sc = new SoapClientKalires();

$rest = new Rest();
$rest->process();
ob_end_flush();