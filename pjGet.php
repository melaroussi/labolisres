<?php                                  
 /**                                
  * Ouverture d'un fichier en mode streaming                     
  *                                 
  *        		                 
  * @package KaliLab                
  * @module KaliLab                
  * @author Netika <info@netika.net>
  * @tests T00543
  **/         
include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

ob_start();

afficheHead("","",true);
filtrageAcces("patient", "index.php", "index.php");

$doUnlink = false;

if($_GET['src'] == "referentiel") {
	if ($_SESSION["refAnalyse"] == 0) {
		klredir("consultation.php", 3, _s("Vous n'avez pas accès à cette page."));
		die;
	}

	$file = $conf['dataDir'].'referentiel/'.$patientLogged->niveau.'/'. $_GET['file'];
}

if($_GET['src'] == "quittance") {
	$file = '/tmp/'.$_GET['file'];
	$doUnlink = true;
}

ob_end_clean();

ini_set('zlib.output_compression','0');


if(isset($_GET['nom'])) $name = $_GET['nom'];
else $name = basename($file);

if (file_exists($file)) {

	ob_start();

	if (function_exists('mime_content_type')) {
		// PHP > 4.3.0
		$mimeType = mime_content_type($file);
	} else {
		$mimeType = "application/octet-stream";
	}

	ob_end_clean();
	
	header('Content-Description: File Transfer');
	header('Content-Type: ' . $mimeType);
	header('Content-Disposition: attachment; filename="' . $name . '"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file));
	readfile($file);
	if($doUnlink) unlink($file);
}

?>