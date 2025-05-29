<?php

ini_set("soap.wsdl_cache_enabled", "0");
ini_set("display_errors", "1");
ini_set("html_errors", "1");

/**
 * Soap Base
 */
abstract class SoapClientBase {
	
	/**
	 * Initialisation de la connexion SOAP
	 */
	protected function init() {
	    if (!empty($this->location)) {
	            $context = stream_context_create(
	                    array(
	                            'ssl' => array(
									'SNI_enabled' => false,
									'verify_peer' => false,
									'verify_peer_name' => false
	                            )
	                    )
	            );
	            $this->client = new SoapClient(NULL, array(
	                    'location'      => $this->location,
	                    'uri'           => "urn:xmethods-delayed-quotes",
	                    'encoding'      => 'UTF-8',
	                    'trace'         => 1,
	                    'allow_self_signed' => 1,
	                    'stream_context' => $context
	            ));
	
	            if (!$this->client) {
	                    die("Impossible de se connecter au serveur SOAP");
	            }
	    }
    }

	
	/**
	 * Décodage utf8 des structures 
	 */
	protected function decodeObject($o) {
		if (is_array($o)) {
			$o2 = array();
			foreach( $o as $field=>$value) {
				$o2[utf8_decode($field)] = $this->decodeObject($value);
			}
			$o = $o2;
		} elseif (is_object($o)) {
			foreach( get_object_vars($o) as $field=>$value) {
				$o->$field = $this->decodeObject($value);
			}
		} elseif (is_string($o)) {
			$o = utf8_decode($o);
		}
		return $o;
	}
	
	/**
	 * Encodage utf8 des objets 
	 */
	protected function SoapEncodeObject( $_object, $_func = 'utf8_encode') {
		if (is_string($_object)) {
			// Une chaine non utf-8
			return $_func($_object);
		} elseif (is_object($_object)) {
			foreach( get_object_vars($_object) as $attribute => $value) {
				$_object->$attribute = self::SoapEncodeObject($value);
			}
		} elseif (is_array($_object)) {
			$_object2 = array();
			foreach( $_object as $field => $value) {
				$_object2[$_func($field)] = self::SoapEncodeObject($value);
			}
			$_object = $_object2;
		}	
		return $_object;
	}
	
	/**
	 * Affichage message d'erreurs
	 */
	protected function handleError( SoapFault $e ) {
		global $conf,$patientLogged;
		@file_put_contents("/var/log/kalires/soap.log", "\n[".date("Y-m-d H:i:s")."] SOAP ERROR (CODE : ".$e->faultcode.") : ".$e->getMessage(), FILE_APPEND);
		@file_put_contents("/var/log/kalires/soap.log", "\n[".date("Y-m-d H:i:s")."] TRACE DIST :\n".$this->client->__getLastResponse()."\n", FILE_APPEND);
		if ($e->faultcode == "DemandeCheckError") {
			$patientLogged->logout();
			klRedir($conf["baseURL"]."index.php",5,_s("Une erreur est survenue. Veuillez vous reconnecter."));
			die();
		}
	}
	
}


/**
 * Requetes SOAP KaliRes
 * */
class SoapClientKalires extends SoapClientBase {

	function __construct() {
		global $conf;
		$this->location =  $conf["serveurSoap"] . 'kalires.php';
		$this->init();
	}

	/**
	 * Récupération des options du serveur de résultats
	 */
	public function getSrOptions() {
		try {
			$data = $this->client->getInfoLab();
			
		} catch (SoapFault  $ex) {
			self::handleError($ex);
			return false;
		}
		return $this->decodeObject($data);
	}

	/**
	 * Récupération des site option merchantId et secretKey
	 */
	public function getSiteOptions() {
		try {
			$data = $this->client->getSiteOptions();
		} catch (SoapFault  $ex) {
			self::handleError($ex);
			return false;
		}
		return $data;
	}
	
	/**
	 * Récupération de la clef publique paybox
	 */
	function getKey() {
		try {
			$key = $this->client->getKey();
		} catch (SoapFault  $ex) {
			self::handleError($ex);
			return false;
		}
		return $key;
	}

	/**
	 * KALIDOM : Auth
	 */
	public function authKaliDom($codePreleveur,$token) {
		try {
			$data = $this->client->authKaliDom($codePreleveur,$token);
		} catch (SoapFault  $ex) {
			self::handleError($ex);
			return false;
		}
		return $this->decodeObject($data);
	}

	/**
	 * KALIDOM : Init
	 */
	public function initKaliDom($codePreleveur,$token) {
		try {
			$data = $this->client->initKaliDom($codePreleveur,$token);
		} catch (SoapFault  $ex) {
			self::handleError($ex);
			return false;
		}
		return $this->decodeObject($data);
	}

	/**
	 * Récupération des options du serveur de résultats
	 */
	public function getSrOptionsUtilisateur($idUser,$type) {
		try {
			$data = $this->client->getSrOptionsUtilisateurs($idUser,$type);
			
		} catch (SoapFault  $ex) {
			self::handleError($ex);
			return false;
		}
		return $this->decodeObject($data);
	}
	/**
	 * Set des options du serveur de résultats
	 */
	public function setSrOptionsUtilisateur($options,$idUser,$typeUtilisateur) {
		try {
			$data = $this->client->setSrOptionsUtilisateur($options,$idUser,$typeUtilisateur);
			
		} catch (SoapFault  $ex) {
			self::handleError($ex);
			return false;
		}
		return $this->decodeObject($data);
	}	
	/**
	 *  Login du patient
	 */
	public function loginPatient( $pLogin, $pPassword, $numDossier = '') {
		if (empty($pLogin) || empty($pPassword)) return false;
		try {
			$pPassword = encodePassword("patient",$pPassword);
			$oPatient = $this->client->loginPatient($pLogin, $pPassword, $numDossier);
			return $this->decodeObject($oPatient);
		} catch (Exception $ex) {
			self::handleError($ex);
			return false;
		}
	}
	/**
	 * Login Médecin ou correspondant ou préleveur
	 */
	public function loginDemandeur( $pLogin, $pPassword, $numDossier = '', $sha=false, $kalidom=false) {
		try {
			if(!$sha){
				$pPassword = encodePassword("demandeur",$pPassword);
			}	
			$oDemandeur = $this->client->loginDemandeur( $pLogin, $pPassword, $numDossier, $sha, $kalidom);
			return  $this->decodeObject($oDemandeur);
		} catch (Exception $ex) {
			self::handleError($ex);
			return false;
		}
	}
	/**
	 * Changement password
	 */
	public function changePassword( $pNiveau, $pLogin, $pPasswordOld, $pPassword, $token="") {
		try {
			$pPasswordOld = encodePassword($pNiveau,$pPasswordOld);
			$pPassword = encodePassword($pNiveau,$pPassword);
			$token = encodeUserToken($token,$pNiveau,$pLogin,$pPassword);
			$oDemandeur = $this->client->changePassword( $pNiveau, $pLogin, $pPasswordOld, $pPassword, $token);
			return  $this->decodeObject($oDemandeur);
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}
	/**
	 * Demande new password
	 */
	public function regenPassword( $pNiveau, $pLogin, $pMail) {
		try {
			$oDemandeur = $this->client->regenPassword( $pNiveau, $pLogin, $pMail);
			return  $this->decodeObject($oDemandeur);
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}
	/**
	 * Validation CGU
	 */
	public function valideCGU( $pId, $pNiveau ) {
		try {
			$oDemandeur = $this->client->valideCGU( $pId, $pNiveau );
			return  $this->decodeObject($oDemandeur);
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}
	/**
	 * Récupération du logo du laboratoire
	 */
	public function getKaliresLogo() {
		global $conf;
		try {
			$dataJpg = base64_decode($this->client->getLogo());
			file_put_contents($conf['dataDir'] . '/logo/logo-kalires-TN.jpg', $dataJpg);
			return true;
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}
	/**
	 * Traces
	 */
	public function trace($type, $reference, $idType, $idReference, $traceUser='') {
		$this->client->srTrace($type, $reference, $idType, $idReference, $traceUser);
	}
	
	/**
	 * TODO : Demande d'un nouveau mot de passe
	 */
	public function getPassword( $type, $login, $email ) {
		
	}
	/**
	 * Récupération des référentiels d'analyse
	 */
	public function getKaliresReferentiel($type) {
		global $conf;
		try {
			$listeReferentiel = $this->decodeObject($this->client->getReferentiel($type));
			return $listeReferentiel;
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}
	/**
	 * Récupération des référentiels d'analyse
	 */
	public function majKaliresReferentiel($type,$arrayRef=array()) {
		global $conf, $patientLogged;
		try {
			list($listeFichierRef,$arrayFichierClean) = $this->client->majReferentiel($type,$arrayRef);
			
			//Mise en place des nouveaux fichiers
			if(is_array($listeFichierRef) && count($listeFichierRef)>0) {				
				foreach ($listeFichierRef as $key => $value) {
					$dataRef = base64_decode($value["file"]);	
					file_put_contents($conf['dataDir'].'referentiel/'.$patientLogged->niveau.'/'.$value["nomFile"].'.pdf', $dataRef);
				}
			}
			
			//Clean anciens referentiels
			foreach($arrayRef as $key => $value) {
				$fileNameTest = $value."_".$key.".pdf";
				if(!in_array($fileNameTest,$arrayFichierClean)) {
					if (file_exists($conf['dataDir'].'referentiel/'.$patientLogged->niveau.'/'.$fileNameTest)) unlink($conf['dataDir'].'referentiel/'.$patientLogged->niveau.'/'.$fileNameTest);
				}
			}

		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}

	/**
	 * Encaisse une demande
	 * $params = Array("idDemande","numDemande","token","montant");
	 */
	function encaisseDemande($params = Array()) {
		try {
			$params = self::SoapEncodeObject($params);
			$data = $this->client->encaisseDemande( $params['token'] );
			return $this->decodeObject($data);
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}
	
	/**
	 * Encaisse une demande
	 * $params = Array("idDemande","numDemande","token","montant");
	 */
	function reglementDemandeOffline($params = Array()) {
		try {
			$params = self::SoapEncodeObject($params);
			$data = $this->client->reglementDemandeOffline( $params['numDemande'], $params['dateNaissance'] );
			return $this->decodeObject($data);
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}
}


/**
 * Requetes SOAP Demandes
 * */
class SoapClientDemande extends SoapClientBase {
	
	function __construct() {
		global $conf;
		$this->location =  $conf["serveurSoap"] . 'demande.php';
		$this->init();
	}
	
	/**
	 * Récupération du CR d'une $idDemande et l'enregistre dans $fichierDest
	 */
	public function getFichierCR( $idDemande, $numDemande,  $typeDest, $idDest, $crSelected=0, $fichierDest = "" ) {
		try {
			$dataCR = base64_decode($this->client->getCR($idDemande, $numDemande, $typeDest, $idDest, $crSelected));
			return $dataCR;
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}

	/**
	 * Récupération la quittance d'une $idDemande et l'enregistre dans $fichierDest
	 */
	public function getFichierQuittance( $idDemande, $fichierDest = "" ) {
		try {
			$dataQuittance = base64_decode($this->client->getQuittance($idDemande));
			if (empty($fichierDest)) $fichierDest = tempnam('/tmp','qui').".pdf";
			file_put_contents($fichierDest, $dataQuittance);
			return $fichierDest;
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}
	
	/**
	 * Recherche de la liste des dossiers
	 * $params = Array("patientId", "numDemande")
	 */
	function changeMessageStatus($params = Array()) {
		$params = self::SoapEncodeObject($params);
		$data = $this->client->changeMessageStatus($params['idDemandeur'], $params['typeDemandeur'], $params['afficher']);
		return $this->decodeObject($data);
	} 	
	/**
	 * Recherche de la liste des dossiers
	 * $params = Array("patientId", "numDemande")
	 */
	function getListeDemandePatient($params = Array()) {
		$params = self::SoapEncodeObject($params);
		$data = $this->client->getListeDemandePatient($params['patientId'], $params['numDemande']);
		return $this->decodeObject($data);
	} 
	/**
	 * Recherche de la liste des dossiers
	 * $params = Array("preleveurId", "numDemande")
	 */
	function getListeDemandePreleveur($params = Array()) {
		$params = self::SoapEncodeObject($params);
		$data = $this->client->getListeDemandePreleveur($params['preleveurId'], $params['filtre']);
		return $this->decodeObject($data);
	} 
	/**
	 * Recherche de la liste des dossiers d'un demandeur
	 * $params = Array("idDemandeur", "typeDemandeur", "filtre")
	 */
	function getListeDemandeDemandeur($params = Array()) {
		$params["filtre"]["isFromPermalink"] = $_SESSION["accesPermalink"];
		$params = self::SoapEncodeObject($params);
		$retourSoap = $this->decodeObject($this->client->getListeDemandeDemandeur($params['idDemandeur'], $params['typeDemandeur'], $params['filtre']));
		
		 
		/* Traitement du retour SOAP **/
		/* Demandes */
		$listeDemandes = $retourSoap["data"];
		
		/* Chapitres */
		/* Recherche des chapitres dans les demandes renvoyées par SOAP */
		$listeChapitres = Array();
		foreach($listeDemandes as $lesDemandes) {
			foreach($lesDemandes['analyses'] as $lesAnalyses) {
				$codeChapitre = $lesAnalyses['codeChapitre'];
				$idChapitre = $lesAnalyses['idChapitre'];
				if(!in_array($codeChapitre, $listeChapitres)) {
					$listeChapitres[$codeChapitre] = Array(
						'idChapitre'=>$lesAnalyses["idChapitre"],
						'nomChapitre'=>$lesAnalyses["nomChapitre"],
						'codeChapitre'=>$lesAnalyses["codeChapitre"]
					);
				}
				
			}
		}
		ksort($listeChapitres);
		
		/* Medecins 
	 	 * Recherche des medecins dans toutes les demandes renvoyées
	 	 * par SOAP */
		$listeMedecins = Array();
		foreach($listeDemandes as $lesDemandes) {
			foreach($lesDemandes['medecins'] as $medecin) {
				$idMedecin = $medecin['idMedecin'];
				if(!in_array($idMedecin, $listeMedecins)) $listeMedecins[$idMedecin] = $medecin;
			}
		}
		/* Correspondants 
	 	 * Recherche des correspondants dans toutes les demandes renvoyées
	 	 * par SOAP */
		$listeCorrespondants = Array();
		foreach($listeDemandes as $lesDemandes) {
			foreach($lesDemandes['correspondants'] as $correspondant) {
				$idCorrespondant = $correspondant['idCorrespondant'];
				if(!in_array($idCorrespondant, $listeCorrespondants)) $listeCorrespondants[$idCorrespondant] = $correspondant;
			}
		}
		
		return Array($listeDemandes, $listeMedecins, $listeCorrespondants, $listeChapitres, $retourSoap);
	} 
		
	/**
	 * Recherche des informations d'un dossier
	 * $params = Array("typeDestinataire","patientId","numDemande","patientNiveau");
	 */
	function getDataPatient($params = Array()) {
		try {
			$params = self::SoapEncodeObject($params);
			$data = $this->client->getInfoDemande( $params['numDemande'], $params['idDemande'], $params['typeDestinataire'],  $params['patientId'], $params['referer']);
			return $this->decodeObject($data);
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}
	
	/**
	 * Recherche des données des analyses d'un dossier
	 * 	$params = Array("idDemande", "patientId", "patientNiveau","chapitreId");
	 */
	function getDataAnalyse($params = Array()) {
		try {
			$params = self::SoapEncodeObject($params);
			$data = $this->client->getDataDemande( $params['idDemande'], $params['numDemande'], $params['patientId'], $params['patientNiveau'], $params['chapitreId']);
			return $this->decodeObject($data);
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}
	
	/**
	 * Recherche des données des anteriorités
	 * 	$params = Array("typeDestinataire","patientId","numPermanent","patientNiveau");
	 */
	function getDataAnteriorite($params = Array()) {
		$params = self::SoapEncodeObject($params);
		$data = $this->client->getDataAnteriorite( $params['typeDestinataire'], $params['numPermanent'], $params['patientId'], $params['patientNiveau']);
		return $this->decodeObject($data);
	}

	/**
	 * Recherche des informations d'un dossier
	 * $params = Array("typeDestinataire","patientId","numDemande","patientNiveau");
	 */
	function getDataPartiel($params = Array()) {
		try {
			$params = self::SoapEncodeObject($params);
			$dataCR = base64_decode($this->client->getDataPartiel($params['numDemande'],$params['idDemande'],$params['typeDestinataire'],$params['idDestinataire']));
			return $dataCR;

		} catch (Exception $ex) {
			echo _s("Erreur")." : ".$ex->getMessage();
			return false;
		}
	}


	/**
	 * Acces a une demande avec un token
	 * $params = Array("token","patientId","patientNiveau");
	 */
	function accesDemande($params = Array()) {
		try {
			$params = self::SoapEncodeObject($params);
			$data = $this->client->accesDemande( $params['token'], $params['patientId'], $params['patientNiveau']);
			return $this->decodeObject($data);
		} catch (Exception $ex) {
			echo self::handleError($ex);
			return false;
		}
	}

}

class SoapClientPrescription extends SoapClientBase {

	function __construct() {
		global $conf;
		$this->location =  $conf["serveurSoap"] . 'prescription.php';
		$this->init();
	}
	
	function envoiDemandePresc($params = Array()) {
		$params = self::SoapEncodeObject($params);
		return $this->client->receptionDemandePresc($params);
	}
	
	function getDataPrescription($params = Array()) {
		$params = self::SoapEncodeObject($params);
		$data = $this->client->getDataPrescription($params["idReference"],$params["idType"]);
		return $this->decodeObject($data);
	}

	function getPrescription($arg = Array()) {
		$params = self::SoapEncodeObject($arg);
		$data = $this->client->getPrescription($params);
		return $this->decodeObject($data);
	}
	
	
	function getListePrescriptionConnectee($params) {
		$params = self::SoapEncodeObject($params);
		$retourSoap = $this->decodeObject($this->client->getListePrescriptionConnectee($params['idDemandeur'], $params['typeDemandeur'], $params['filtre']));
		
		return $retourSoap;
	}
	
	function getInfoPatientIpp($params) {
		$param = self::SoapEncodeObject($params);
		$data = $this->decodeObject($this->client->getInfoPatientIpp($params["idIntervenant"], $params["typeIntervenant"], $params["numIPP"]));
		
		return $data;
	}
	
}
