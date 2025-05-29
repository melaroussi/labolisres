<?php                                  

include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

$type = getSrOption('interfacePaiement');

if($type == "CBI" && isset($_GET['token'])) {
	$scd = new SoapClientKalires();
	$params = Array(
		"token"			=> $_GET['token']
	);
	$ret = $scd->encaisseDemande($params);
	if($ret !== false) {
		echo "OK";
		die();
	}
}else if($type !== "CBI") {
	include_once("include/epaiement/paiementEnLigne.ctrl.php");
	
	if(paiementEnLigneCtrl::isInterfaceMonoSite($type)){
		$idSite      = $_REQUEST["idSite"];
		$merchantId  = getSiteOption("MERCHANT_ID_PAIEMENT", $idSite);
		$secretKey   = getSiteOption("SECRET_KEY_PAIEMENT" , $idSite);
	} else {
		$merchantId  = getSrOption('merchantIdPaiement');
		$secretKey   = getSrOption('secretKeyPaiement');
	}

	$paiement = paiementEnLigneCtrl::get($type,$merchantId,$secretKey,getSrOption('tpeNumber'),getSrOption('testPaiement'));

	if ( ($ret=$paiement->verifRetour())===true) {

		$token = $paiement->calculateToken();
		$scd = new SoapClientKalires();
		$params = Array(
			"token"			=> $token
		);
		$ret = $scd->encaisseDemande($params);
		if($ret !== false && $type != "PAYBOX") { // Cette page ne doit rien renvoyer pour PAYBOX
			echo "OK";
			die();
		}

	}
	// pas de sleep pour le bon focntionnement de l'interface
	die();
}
sleep(10); // pour éviter le brutforce
header("HTTP/1.0 403 Forbidden");

?>