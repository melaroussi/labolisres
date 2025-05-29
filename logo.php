<?php
/**
 * Récupération d'un fichier (kfile)
 * User: Raf
 * Date: 26/09/14
 * Time: 12:10
 */

ini_set('zlib.output_compression', '0');
require_once 'include/conf.inc.php';
require_once 'include/lib.inc.php';

$inline = false;
if (isset($_REQUEST['inline']) && $_REQUEST['inline'] == '1') {
	$inline = true;
}

ob_end_clean();
Kfile::pjGetContent(urldecode($_SESSION['kfile_logo']['file']), '', '', $inline);

?>