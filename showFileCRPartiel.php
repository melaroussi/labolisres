<?php
/**
 * Affichage d'un CR Pdf
 */
 
include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

$id = $_GET['id'];
$numDemande = $_GET['numDemande'];

if($id > 0 && $numDemande != '' && $patientLogged->niveau != '' && $patientLogged->id() > 0) {

	$scd = new SoapClientDemande();
	$dataPartiel = $scd->getDataPartiel(Array("typeDestinataire"=>$patientLogged->niveau,"idDemande"=>$id,"numDemande"=>$numDemande,"idDestinataire"=>$patientLogged->id()));
	if($dataPartiel != '') {
	
		$nomPdfDemande = $numDemande.".pdf";

		header('Content-Description: File Transfer');
		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="'.$nomPdfDemande.'"');
		header('Content-Transfer-Encoding: binary');
		header('Pragma: public');
		echo $dataPartiel;
		
	}
}

die();