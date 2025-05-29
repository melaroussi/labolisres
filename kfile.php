<?php
/**
 * Récupération d'un fichier (kfile)
 * User: Raf
 * Date: 26/09/14
 * Time: 12:10
 */

ini_set('zlib.output_compression', '0');
if(isset($_GET['PHPSESSID']) && $_GET['PHPSESSID']!="") session_id($_GET['PHPSESSID']);
require_once 'include/conf.inc.php';
require_once 'include/lib.inc.php';

// Vérification du jeton
$token = $_REQUEST['token'];

if (!$patientLogged->isAuth() || !isset($_SESSION['kfile_token'][$token])) {
	// Non authentifié
	header("HTTP/1.0 403 Forbidden");
	die();
}

$file = $_SESSION['kfile_token'][$token]['file'];
if (($file = Kfile::get($file)) === false) {
	// Le fichier n'existe pas
	header("HTTP/1.0 404 Not Found");
	die();
}

$inline = false;
if (isset($_REQUEST['inline']) && $_REQUEST['inline'] == '1') {
	$inline = true;
}

ob_end_clean();
Kfile::pjGetContent(urldecode($file), '', '', $inline);

?>
