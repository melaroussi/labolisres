<?php                                  

include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

afficheHead(_s("Redirection vers une demande")." - ".getSrOption("laboNom"),"",true);
entete();

$typePaiement = getSrOption('interfacePaiement');

if(is_a($patientLogged,'PatientLogged') && $patientLogged->id() > 0 && $patientLogged->niveau == 'patient' && ((isset($_GET['token']) && $typePaiement == "CBI") || (isTypePaiementOk($typePaiement)))) {

	$scd = new SoapClientDemande();
	$params = Array(
		"patientId" => $patientLogged->id(),
		"patientNiveau" => $patientLogged->niveau
	);
	if ($typePaiement == "CBI") {
		$params["token"] = $_GET['token'];
	} else {
	 	include_once($conf['baseDir']."include/epaiement/paiementEnLigne.ctrl.php");


		if(paiementEnLigneCtrl::isInterfaceMonoSite($typePaiement)){
			$idSite      = $_REQUEST["idSite"];
			$merchantId  = getSiteOption("MERCHANT_ID_PAIEMENT", $idSite);
			$secretKey   = getSiteOption("SECRET_KEY_PAIEMENT" , $idSite);
		} else {
			$merchantId  = getSrOption('merchantIdPaiement');
			$secretKey   = getSrOption('secretKeyPaiement');
		}

		$paiement = paiementEnLigneCtrl::get($typePaiement,$merchantId,$secretKey,getSrOption('tpeNumber'),getSrOption('testPaiement'),"redirect");
		$retPaiement=$paiement->verifRetour();
		if ($typePaiement == "MONETICO" || $typePaiement == "OGONE") {
			$paiement->set("orderId", $retPaiement['idDemande']);
			$paiement->set("transactionReference", $retPaiement['numDemande']);
			$paiement->set("amount", $retPaiement['montant']);
			$paiement->set("customerId", $params['patientId']);
			$retour = $retPaiement["paiement"];
			if ($retour == "OK") {
				echo klMessage("info", "Retour de paiement", "Le paiement a réussi et a été enregistré dans la demande.");
			} else if ($retour == "NOK") {
				echo klMessage("error", "Retour de paiement", "Le paiement a échoué, veuillez essayer à nouveau.");

			}
		}
		$token = $paiement->calculateToken();
		$params["token"] = $token;
	}
	
	if(!isset($_GET['w'])) {
		$_GET['w'] = 0;
	}

	$ret = $scd->accesDemande($params);
	if(is_array($ret) && $ret['status'] == 'wait' && $_GET['w'] <= 3) {
		$_GET['w']++;
		klRedir("redirect.php?token=".$_GET['token']."&w=".$_GET['w']."",5,_s("Merci de patienter, paiement en cours de validation ..."));
		afficheFoot();
		die();
	}
	
	if(is_array($ret) && $ret['status'] == 'open' && $ret['idDemande'] != '' && $ret['numDemande'] != '') {
		klRedir("afficheDossier.php?sNumDossier=".$ret['numDemande']."&sIdDossier=".$ret['idDemande']."",1,_s("Redirection en cours ..."));
		afficheFoot();
		die();
	}

	afficheMessage("<font color=red>"._s("Erreur : Vous n'avez pas accès à ce dossier, veuillez signaler ce problème au laboratoire")."</font>");
	afficheFoot();
	die();
		
} elseif($_SESSION["paiementOffline"] == 'ok' && ((isset($_GET['token']) && $typePaiement == "CBI") || (isTypePaiementOk($typePaiement)))) {

	klRedir("index.php",5,"<span style=\"color:black;\">"._s("Redirection en cours ...")."</span>");
	afficheFoot();
	die();
	
}

klRedir("denied.php?type=redirect",10,"<span style=\"color:red;\">"._s("L'authentification a échoué")."</span>"); // pour éviter le brutforce
afficheFoot();
die();

?>