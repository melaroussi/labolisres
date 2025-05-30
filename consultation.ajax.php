<?php                                  

include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

filtrageAcces("patient","index.php","index.php");

// Date du passé
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// toujours modifié
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

// HTTP/1.0
header("Pragma: no-cache");

header ("Content-type: text/xml; charset=ISO-8859-1"); 

echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?".">\n";

	$scd = new SoapClientDemande();
	$params = Array("idDemandeur"=>$patientLogged->id(), "typeDemandeur"=>$patientLogged->niveau, "afficher"=>$afficher);
	$patientLogged->messageAffiche = $scd->changeMessageStatus($params);

echo "<response>"."</response>";

?>
