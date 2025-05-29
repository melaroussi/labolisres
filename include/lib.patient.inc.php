<?

Class PatientLogged {

	var $id = 0;
	var $numPermanent = 0;
	var $numIdentification = "";
	var $login = 0;
	var $nom = "";
	var $temps;
	var $idLog = 0;
	var $accordSigne = 0;
	var $cgu = 0;
	var $passwordExpired = false;
	var $userOption = Array();
	var $messageAffiche = "";
	var $datePasswordExpired = "";
	var $numDemande = "";
	var $passwordToken = "";
	var $refAnalyse = 0;
	var $optionsUtilisateur = "";
	var $kaliresPrescription = 0;
	var $accountLocked = false;
	var $traceUser = "";
	var $tabChapitre = Array();
	var $paiement = false;

	function __construct($numeroSecu="",$passwd="",$numDossier="", $typePersonne="patient", $sha=false, $traceUser="") {
		global $conf,$securite,$mabase,$PHPSESSID,$_COOKIE,$cBdUniq;
		$passwd = trim($passwd);
		$this->traceUser = $traceUser;
		$this->accordSigne = false;
		$this->passwordExpired = false;
		$sc = new SoapClientKalires();
		
		if ($typePersonne=="patient") {
			$loginPatient = getSrOption("kaliResPatient");
			
			/* Login PATIENT */
			$infosPatient = $sc->loginPatient($numeroSecu, $passwd, $numDossier);	
			
			if ($infosPatient == false || !$loginPatient) {
				$this->logout();
			} else if ($infosPatient->accountLocked) {
				$this->accountLocked = true;
				$this->logout();
			} else {
				if($infosPatient->refAnalyse > 0) {
					$this->refAnalyse = 1;
				}

				$this->niveau = 'patient';
				$this->id = $infosPatient->idPatient;
				$this->numPermanent = $infosPatient->numPermanent;
				$this->email = $infosPatient->email;
				$this->paiement = $infosPatient->paiement;
				$this->numIdentification = "";
				if(getSrOption('affichAnt') == 0) {
					$this->numDemande = $infosPatient->numDemande;
				}
				$this->login = $infosPatient->idPatient;
				$this->passwordExpired = $infosPatient->changePassword;
				$this->passwordToken = $infosPatient->tokenPassword;
				$this->datePasswordExpired = $infosPatient->datePasswordExpired;
				$this->messageAffiche = $infosPatient->messageAffiche;
				$this->userOption = $infosPatient->userOption;
				$this->cgu = $infosPatient->cgu;
				if($this->cgu > 0) $this->accordSigne = true;
				$this->nom = _secho($infosPatient->nom);
				$this->nom .=($infosPatient->prenom!="")?" "._secho($infosPatient->prenom):"";
				$this->temps['create'] = time();
				$cs = new SoapClientKalires();
				$cs->trace('patient', $this->id, 'login', '', $this->traceUser);
			}
				
		} else {
			/* Login DEMANDEUR */
			unset($_SESSION["filter"]);
			
			$loginMedecin = getSrOption("kaliResMedecin");
			$loginCorrespondant = getSrOption("kaliResCorrespondant");
			$loginPreleveur = getSrOption("kaliResPreleveur");
			
			$infosLogin = $sc->loginDemandeur($numeroSecu, $passwd, $numDossier, $sha);
			
			if ($infosLogin == false) {
				$this->logout();
			}elseif (
				($infosLogin->niveau == 'medecin' && !$loginMedecin) ||
				($infosLogin->niveau == 'correspondant' && !$loginCorrespondant) ||
				($infosLogin->niveau == 'preleveur' && !$loginPreleveur)
			) {
				$this->logout();		
			} else if ($infosLogin->accountLocked) {
				$this->accountLocked = true;
				$this->logout();
			} else {
				if($infosLogin->refAnalyse > 0) {
					$this->refAnalyse = 1;
				}			
			
				$this->niveau = $infosLogin->niveau;
				$this->id = $infosLogin->id;
				$this->numPermanent = "";
				$this->numIdentification = $infosLogin->numIdentification;
				$this->login = $infosLogin->id;
				$this->passwordExpired = $infosLogin->changePassword;
				$this->passwordToken = $infosLogin->tokenPassword;
				$this->datePasswordExpired = $infosLogin->datePasswordExpired;
				$this->messageAffiche = $infosLogin->messageAffiche;
				$this->userOption = $infosLogin->userOption;
				$this->cgu = $infosLogin->cgu;
				if($this->cgu > 0) $this->accordSigne = true;
				$this->nom = _secho($infosLogin->nom);
				$this->nom .=($infosLogin->prenom!="")?" "._secho($infosLogin->prenom):"";
				$this->temps['create'] = time();			
				$this->kaliresPrescription = $infosLogin->kaliresPrescription;
				$this->permalinkLevel = $infosLogin->permalinkLevel;
				$cs = new SoapClientKalires();
				$cs->trace($infosLogin->niveau, $this->id, 'login', '', $this->traceUser);
				$this->optionsUtilisateur = $cs->getSrOptionsUtilisateur($this->id,$this->niveau);
				$this->tabChapitre = $infosLogin->tabChapitre;

				if($this->userOption["clearFilter"] == 0 || (isset($_SESSION['accesPermalinkLevel']) && $_SESSION['accesPermalinkLevel'] == 2)) {
					if (is_array($infosLogin->filtre)) {
						$_SESSION['filter'] = $infosLogin->filtre;
					}
				} else {
					unset($_SESSION['filter']);
				}

			}	
		}
		return true;
	}
	
	function getOptionUtilisateur($option) {
		if (is_array($this->optionsUtilisateur)) {
			return $this->optionsUtilisateur[$option];
		}
		return false;
	}
	
	function setOptionUtilisateur($option,$idUser,$typeUtilisateur) {
		$cs = new SoapClientKalires();		
		$cs->setSrOptionsUtilisateur($option,$idUser,$typeUtilisateur);
		foreach($option as $key => $value) {
			$this->optionsUtilisateur[$key] = $value;
		}
	}
	
	function logout(){
		global $PHPSESSID,$cBdUniq,$conf;
		$this->id=0;
		$this->login=0;
		$this->nom = "";
		$this->temps = Array();
		$this->niveau = '';
		$this->traceUser = '';
		$this->accordSigne = false;
		unset($_SESSION["filter"]);
		unset($_SESSION["accesPermalink"]);
		unset($_SESSION["accesPermalinkLevel"]);
		unset($_SESSION["accesPermalinkNum"]);
		unset($_SESSION["accesPermalinkNumIPP"]);
	}

	function update(){
		global $cBdUniq,$PHPSESSID,$conf;
		$sessDuree = 0;
		if($this->niveau != "patient") {
			$sessDuree = getSrOption("sessionTimePS");
		} else {
			$sessDuree = getSrOption("sessionTimePatient");
		}
		if($this->temps['update'] > 0 && $sessDuree > 0) {
			if( ( (time()-$this->temps['update'])/60) >= $sessDuree ) {
				afficheHead(_s("Serveur de résultats")." - ".getSrOption("laboNom"),"",false);
				entete();
				$this->logout();
				klRedir($conf["baseURL"]."index.php",5,"Votre session a expirée suite à un délai d'inactivité trop long. Veuillez vous reconnecter.");
				die();
			}
		}
		$this->temps['update'] = time();
	}

	function id(){
		return $this->id;
	}

	function nom(){
		return $this->nom;
	}

	function isMe($id){
		return ($this->id() == $id);
	}

	function isLogin(){
		return (($this->id!=0) && ($this->getNiveau()!='') );
	}

	function isAuth(){
		return (($this->id!=0) && ($this->getNiveau()!='') && ($this->accordSigne) );
	}
	
	function setAccordSigne(){
		global $cBdUniq;
		$this->accordSigne = true;
		$this->cgu = 1;
		$sc = new SoapClientKalires();
		$sc->valideCGU($this->id, $this->niveau);
	}	

	function getNiveau(){
		return $this->niveau;
	}
	
	function filtrageNiveau($niveauLimite,$urlNonAuth,$urlNonAcces){
		global $conf,$idSiteSelectedTransfert,$_modules;
		if ($this->isAuth() != 1) {
			$sMsg.=_s("Vous devez vous identifier pour accéder à cette page.")."<br /><br /><a href=\"$urlNonAuth\">"._s("Cliquez ici pour vous identifier")."</a>";
			echo "<br><br><p>";
			afficheMessage($sMsg,"width:450px;");
			echo "<TABLE HEIGHT=75% WIDTH=100%><TR valign=middle><TD align=center><a href=\"$urlNonAuth\"><IMG BORDER=0 SRC='".$conf["baseURL"]."images/seringue2.gif'></a></TD></TR></TABLE></a>";
			die();
		} elseif( ($niveauLimite == "administrateur") || ($niveauLimite == "admin") ) {
			$sMsg.=_s("Vous devez vous identifier pour accéder à cette page.")."<br /><br /><a href=\"$urlNonAuth\">"._s("Cliquez ici pour vous identifier")."</a>";
			echo "<br><br><p>";
			afficheMessage($sMsg,"width:450px;");
			echo "<TABLE HEIGHT=75% WIDTH=100%><TR valign=middle><TD align=center><a href=\"$urlNonAuth\"><IMG BORDER=0 SRC='".$conf["baseURL"]."images/seringue2.gif'></a></TD></TR></TABLE></a>";
			die();
		}
	}
	
	function loadAccesSession() {
		if($this->refAnalyse > 0) {
			$_SESSION["refAnalyse"] = 1;
		} else {
			$_SESSION["refAnalyse"] = 0;
		}
		
		if ($this->kaliresPrescription == 1 && getSrOption("kaliResPCAccesGlobal") == 1) {
			$_SESSION["accesPC"] = 1;
		} else {
			$_SESSION["accesPC"] = 0;
		}

		if($_SESSION["accesPermalink"]){
			$_SESSION["accesPermalinkLevel"] = $this->permalinkLevel;
		}	
	}

}
?>
