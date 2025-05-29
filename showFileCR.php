<?php
/**
 * Affichage d'un CR Pdf
 */
 
include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

$id = $_GET['id'];
$numDemande = $_GET['numDemande'];
$crSelected = $_GET['crSelected'];

if($id > 0 && $numDemande != '' && $patientLogged->niveau != '' && $patientLogged->id() > 0 && $crSelected > 0) {

	$cs = new SoapClientDemande();
	$dataCr = $cs->getFichierCR($id, $numDemande, $patientLogged->niveau, $patientLogged->id(), $crSelected);
	if($dataCr != '') {
	
		$nomPdfDemande = $numDemande.".pdf";

		header('Content-Description: File Transfer');
		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="'.$nomPdfDemande.'"');
		header('Content-Transfer-Encoding: binary');
		header('Pragma: public');
		echo $dataCr;
		
	}
	
}

die();