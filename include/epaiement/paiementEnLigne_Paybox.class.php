<?php

CLASS paiementEnLigne_Paybox extends paiementEnLigne {

	private static  $PAYBOX_URLPAYMENT	= 'tpeweb.paybox.com';
	private static  $PAYBOX_DEVISE		= Array('EURO'=>978,'DOLLAR'=>840,'CFA'=>952);
	private static  $PAYBOX_LANGUE		= Array('francais'=>'FRA',
												'anglais'=>'GBR',
												'espagnol'=>'ESP',
												'italien'=>'ITA',
												'allemand'=>'DEU',
												'neerlandais'=>'NLD',
												'suedois'=>'SWE',
												'portugais'=>'PRT'
											);
	private static  $PAYBOX_RETOUR 		= Array("mt"=>"M",
												"ref"=>"R",
												"auto"=>"A",
												"err"=>"E",
												"sign"=>"K"
											); // K doit toujours �tre � la fin

	private static $formfields = Array(
									"PBX_SITE"         	=> ""
									,"PBX_RANG"        	=> ""
									,"PBX_IDENTIFIANT" 	=> ""
									,"PBX_TOTAL"       	=> ""
									,"PBX_DEVISE"      	=> ""
									,"PBX_CMD"         	=> ""
									,"PBX_PORTEUR"     	=> ""
									,"PBX_RETOUR"      	=> ""
									,"PBX_HASH"        	=> ""
									,"PBX_TIME"        	=> ""
									// optionnel
									,"PBX_LANGUE"		=> ""
									,"PBX_TYPEPAIEMENT"=> "CARTE"
									,"PBX_EFFECTUE"		=> ""
									,"PBX_REFUSE"		=> ""
									,"PBX_ANNULE"		=> ""
									,"PBX_ATTENTE"		=> ""
									,"PBX_REPONDRE_A"	=> ""
									);

	function getProvider(){
		return 'Verifone';
	}

	// Hash les donn�es du formulaire avec la clef secrete marchand
	public function computeHmac() {
		$message = Array();
		foreach(paiementEnLigne_Paybox::$formfields as $key=>$val){
			$message[] = $key."=".$val;
		}
		$message = join('&', $message);
		$res = pack("H*", $this->get('secretKey'));
		return strtoupper(hash_hmac(paiementEnLigne_Paybox::$formfields["PBX_HASH"],$message, $res));
	}

	// Retourne le montant en centime
	function getAmount(){
		return "".($this->get('amount') * 100);
	}

	// Set le montant
	function setAmount($amount){
		$this->set('amount',$amount/100);
	}

	// V�rifie si alphanum�rique
	function stringANS($txt) {
	  return !preg_match("/[^a-zA-Z0-9_\-]/", $txt);
	}

	// V�rifie les donn�es
	function checkData(){
		$err=Array();
		if ($this->getAmount() == '' || $this->getAmount() == 0) $err[]=_s("Mauvais montant");
		if ($this->get('merchantId') == '')                      $err[]=_s("Aucun code commercant");
		if ($this->get('secretKey') == '')                       $err[]=_s("Mauvaise cl�");
		if ($this->get('returnURL') == '')                       $err[]=_s("Aucune adresse web du site pour le retour");
		if ($this->get('responseURL') == '')                     $err[]=_s("Aucun adresse web pour les r�ponses de la banque");
		if ($this->get('transactionReference') == '')            $err[]=_s("Mauvais num�ro de transaction");
		if ($this->get('customerId') =='')                       $err[]=_s("Mauvais num�ro de client final");
		if ($this->get('orderId') == '')                         $err[]=_s("Mauvais num�ro de commande");
		if ($this->get('customerEmail') == '')                   $err[]=_s("Aucun e-mail patient");

		if (ctype_alnum($this->get('transactionReference')) === false)  $err[]=_s("La r�f�rence de la transaction doit �tre un alphanumerique");

		if (ctype_digit($this->getAmount()) === false)  $err[]=sprintf(_s("Le montant doit �tre un num�rique (%s)"),$this->getAmount());

		$identifiants = explode(';',$this->get('merchantId'));
		if (count($identifiants)<>3) $err[]=_s("La r�f�rence du commercant doit �tre sous forme 'SITE;RANG;IDENTIFIANT'");
		else{
			if($this->stringANS($identifiants[0])=== false) $err[]=_s("PBX_SITE doit �tre un alphanum�rique ".$identifiants[0]);
			if($this->stringANS($identifiants[1])=== false) $err[]=_s("PBX_RANG doit �tre un alphanum�rique ".$identifiants[1]);
			if($this->stringANS($identifiants[2])=== false) $err[]=_s("PBX_IDENTIFIANT doit �tre un alphanum�rique ".$identifiants[2]);
		}
		if (ctype_alnum($this->get('customerId')) === false) $err[]=_s("La r�f�rence du client doit �tre un alphanum�rique");

		if ($this->stringANS($this->get('secretKey')) === false)  $err[]=sprintf(_s("La clef secr�te doit �tre un alphanum�rique (%s)"),$this->get('secretKey'));

		if ($this->stringANS($this->get('orderId')) === false)  $err[]=sprintf(_s("La r�f�rence de commande doit �tre un alphanum�rique (%s)"),$this->get('orderId'));

		if (count($err) == 0) return true;
		else return implode(", ",$err);
	}

	// Formate les donn�es du tableau PBX_RETOUR
	function formatRetour(){
		$retour = Array();
		foreach(paiementEnLigne_Paybox::$PAYBOX_RETOUR as $key=>$val){
			$retour[] = $key.':'.$val; 
		}
		return join(';',$retour);
	}

	// Cr�e le formulaire envoy� � PAYBOX
	function getForm($formName,$formId) {

		if (($resCheck = $this->checkData()) !== true) return array("erreur"=>true,"data"=>$resCheck);

		$identifiants = explode(';',$this->get('merchantId'));
		paiementEnLigne_Paybox::$formfields["PBX_SITE"]		   = $identifiants[0];
		paiementEnLigne_Paybox::$formfields["PBX_RANG"]		   = $identifiants[1];
		paiementEnLigne_Paybox::$formfields["PBX_IDENTIFIANT"] = $identifiants[2];
		paiementEnLigne_Paybox::$formfields["PBX_TOTAL"]       = $this->getAmount();
		paiementEnLigne_Paybox::$formfields["PBX_DEVISE"]      = paiementEnLigne_Paybox::$PAYBOX_DEVISE["EURO"];
		paiementEnLigne_Paybox::$formfields["PBX_CMD"]         = $this->get('transactionReference')."|".$this->get('customerId')."|". $this->get('orderId');
		paiementEnLigne_Paybox::$formfields["PBX_PORTEUR"]     = $this->get('customerEmail');
		paiementEnLigne_Paybox::$formfields["PBX_RETOUR"]      = $this->formatRetour();
		paiementEnLigne_Paybox::$formfields["PBX_HASH"]        = "SHA512";
		paiementEnLigne_Paybox::$formfields["PBX_TIME"]        = date("c");
		paiementEnLigne_Paybox::$formfields["PBX_LANGUE"]      = paiementEnLigne_Paybox::$PAYBOX_LANGUE["francais"];
		paiementEnLigne_Paybox::$formfields["PBX_REPONDRE_A"]  = $this->get('responseURL');
		paiementEnLigne_Paybox::$formfields["PBX_EFFECTUE"]	   = $this->get('returnURL')."&etat=0";
		paiementEnLigne_Paybox::$formfields["PBX_REFUSE"]	   = $this->get('returnURL')."&etat=1";
		paiementEnLigne_Paybox::$formfields["PBX_ANNULE"]	   = $this->get('returnURL')."&etat=2";
		paiementEnLigne_Paybox::$formfields["PBX_ATTENTE"]	   = $this->get('returnURL')."&etat=3";

		// Doit toujours �tre � la fin et pas dans $formfields lors du hmac
		paiementEnLigne_Paybox::$formfields["PBX_HMAC"] = $this->computeHmac();

		$type = 'hidden';

		if (($serveurCheck = $this->getBankURL()) === false) return array("erreur"=>true,"data"=>_s("Aucun serveur de paiement en ligne n'est disponible."));

		$url = "https://".$serveurCheck."/cgi/MYchoix_pagepaiement.cgi";
		$data = '<form method="post" name="' . $formName . '" id="' . $formId . '" action="' . $url . '">';

		foreach(paiementEnLigne_Paybox::$formfields as $key=>$val){
			$data.='<input type="'.$type.'" name="'.$key.'" value="'.$val.'">';
		}

		$data .= '</form>';

		return array("erreur"=>false,"data"=>$data);
	}

	// Retourne l'url � laquelle envoyer le formulaire
	function getBankURL(){
		return ($this->get('modeTest') == 'TEST') ? 'preprod-'.paiementEnLigne_Paybox::$PAYBOX_URLPAYMENT : paiementEnLigne_Paybox::$PAYBOX_URLPAYMENT;
	}

	// V�rifie les donn�es que PAYBOX renvoi
	function verifRetour()
	{

		$dataRetour = $this->getDataRetour();

		$etat = $dataRetour["etat"]; // 0 = EFFECTUE, 1 = REFUSE, 2 = ANNULE, 3 = ATTENTE

		if($etat == "0" || $etat == ""){ // EFFECTUE. Etat == "" dans le cas d'un appel serveur � serveur
			// Verification de la signature
			// Donn�es sign�es :
			// a) lors de la r�ponse Verifone de serveur � serveur (URL IPN : PBX_REPONDRE_A), seules les informations
			// demand�es dans la variable PBX_RETOUR sont sign�es, (donc se baser sur $dataRetour)
			// b) dans les 4 autres cas (redirection via le navigateur du client, PBX_EFFECTUE,
			// PBX_REFUSE et PBX_ANNULE, PBX_ATTENTE), ce sont toutes les donn�es suivant le
			// ' ? ' (donc se baser sur PAYBOX_RETOUR)
			$arraySigned = $etat == "" ? paiementEnLigne_Paybox::$PAYBOX_RETOUR : $dataRetour;
			$message = $join = "";
			foreach($arraySigned as $key=>$val){
				$message .= $join.$key.'='.$dataRetour[$key];
				$join = '&';
			}
			$checkSign = $this->pbxVerSign($message);

			if( $checkSign == 1 ){ // "Signature valide");
				$erreur 		= $dataRetour["err"];  // doit etre 00000
				$autorisation 	= $dataRetour["auto"]; // doit pas etre vide
				$montant 		= $dataRetour["mt"];

				// Champs obligatoires
				list($numDemande,$customerId,$orderId) = explode("|",$dataRetour["ref"]);
				$this->set('responseCode',0);
				$this->set('customerId',$customerId);
				$this->set('orderId',$orderId);
				$this->set('transactionReference',$numDemande);
				$this->setAmount($montant);

				if ($erreur == "00000" && $autorisation != "") { // Paiement effectu�, sans erreur, autorisation OK
					// Champs sup
					return true;
				}
				else { // KO
					// Champs sup
					return false;
				}
			}
			else if( $checkSign == 0 )  return sprintf(_s("Signature invalide : donn�es alt�r�es ou signature falsifi�e"));
			else                        return sprintf(_s("Erreur lors de la v�rification de la signature"));
		}
		else if ($etat == "2"){ // ANNULE
			$erreur 		= $dataRetour["err"];  // doit etre 00000
			$autorisation 	= $dataRetour["auto"]; // doit pas etre vide
			$montant 		= $dataRetour["mt"];

			// Champs obligatoires
			list($numDemande,$customerId,$orderId) = explode("|",$dataRetour["ref"]);
			$this->set('responseCode',2);
			$this->set('customerId',$customerId);
			$this->set('orderId',$orderId);
			$this->set('transactionReference',$numDemande);
			$this->setAmount($montant);
			return true;
		}
	}


	// Chargement de la clef publique de PAYBOX
	function loadKey() {
		$sc = new SoapClientKalires();
		$keyData = $sc->getKey();
		$key = openssl_pkey_get_public( $keyData );
		return $key;
	}

	// renvoi les donnes signees et la signature
	function getSignedData( $qrystr, &$data, &$sig) {    
		$pos = strrpos( $qrystr, '&' );                         // cherche dernier separateur
		$data = substr( $qrystr, 0, $pos );                     // et voila les donnees signees
		$pos = strpos( $qrystr, '=', $pos ) + 1;                // cherche debut valeur signature
		$sig = substr( $qrystr, $pos );                         // et voila la signature
		$sig = base64_decode( $sig );                           // decodage signature base 64
	}

	// verification signature Paybox
	function pbxVerSign( $qrystr) {
		$key = $this->loadKey();                                   // chargement de la cle
		if( !$key ) return -1;                                     // si erreur chargement cle
		//  penser � openssl_error_string() pour diagnostic openssl si erreur
		$data = $sig = "";
		$this->getSignedData( $qrystr, $data, $sig);               // separation et recuperation signature et donnees
		return openssl_verify( $data, $sig, $key );                // verification : 1 si valide, 0 si invalide, -1 si erreur
	}

}