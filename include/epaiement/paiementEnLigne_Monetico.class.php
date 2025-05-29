<?php

CLASS paiementEnLigne_Monetico extends paiementEnLigne {
	
	const MONETICOPAIEMENT_EPTNUMBER			= '0000001';
	const MONETICOPAIEMENT_VERSION				= '3.0';
	const MONETICOPAIEMENT_CTLHMAC				= 'V4.0.sha1.php--[CtlHmac%s%s]-%s';
	const MONETICOPAIEMENT_CTLHMACSTR			= 'CtlHmac%s%s';
	const MONETICOPAIEMENT_PHASE2BACK_RECEIPT	= "version=2\ncdr=%s";
	const MONETICOPAIEMENT_PHASE2BACK_MACOK		= "0\n";
	const MONETICOPAIEMENT_PHASE2BACK_MACNOTOK	= "1\n";
	const MONETICOPAIEMENT_PHASE2BACK_FIELDS	= '%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*';
	const MONETICOPAIEMENT_PHASE1GO_FIELDS		= '%s*%s*%s%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s';
	const MONETICOPAIEMENT_URLPAYMENT			= 'https://p.monetico-services.com/paiement.cgi';
	const MONETICOPAIEMENT_URLPAYMENTTEST		= 'https://p.monetico-services.com/test/paiement.cgi';
	const MONETICOPAIEMENT_DEVISE				= 'EUR';
	const MONETICOPAIEMENT_LANGUE				= 'FR';
    
    function getProvider(){
        return 'Monetico';
    }

    function checkData()
    {
        $ret=Array();
        if ($this->get('amount') == '' || $this->get('amount') == 0) $ret[]=_s("Mauvais montant");
        if ($this->get('merchantId') == '') $ret[]=_s("Aucun code commercant");
        if ($this->get('returnURL') == '') $ret[]=_s("Aucune adresse web du site pour le retour");
		if ($this->get('transactionReference') == '') $ret[]=_s("Mauvais numéro de transaction");
        if ($this->get('customerId') =='') $ret[]=_s("Mauvais numéro de client final");
        if ($this->get('orderId') == '') $ret[]=_s("Mauvais numéro de commande");
        if ($this->get('secretKey') == '') $ret[]=_s("Mauvaise clé");

        if (ctype_alnum($this->get('transactionReference')) === false) $ret[]=_s("La référence de la transaction doit être un alphanumerique");
        if (strlen($this->get('transactionReference'))>12) $ret[]=sprintf(_s("La référence de la transaction doit être un alphanumerique de maximum 12 caractères (%s)"),$this->get('transactionReference'));


        if (count($ret) == 0) return true;
        else return implode(", ",$ret);
    }


    function getForm($formName,$formId) {

        if (($resCheck = $this->checkData()) !== true) return array("erreur"=>true,"data"=>$resCheck);
        
        $merchantId  = $this->get('merchantId');
        $merchantKey = $this->get('secretKey');//'12345678901234567890123456789012345678P0';
        $sReference = $this->get('transactionReference');
        $sMontant = $this->get('amount');
        $sDevise  = paiementEnLigne_Monetico::MONETICOPAIEMENT_DEVISE;
        $sTexteLibre = $this->get('customerId'). "|". $this->get('orderId');
        $sDate = date("d/m/Y:H:i:s");
        $sLangue = paiementEnLigne_Monetico::MONETICOPAIEMENT_LANGUE;
        $sEmail = $this->get('customerEmail');
        $sOptions = "";
        $sNbrEch = "";
        $sDateEcheance1 = "";
        $sMontantEcheance1 = "";
        $sDateEcheance2 = "";
        $sMontantEcheance2 = "";
        $sDateEcheance3 = "";
        $sMontantEcheance3 = "";
        $sDateEcheance4 = "";
        $sMontantEcheance4 = "";

        $oHmac = new MoneticoPaiement_Hmac($merchantKey);

        // Control String for support
        $CtlHmac = sprintf(paiementEnLigne_Monetico::MONETICOPAIEMENT_CTLHMAC, paiementEnLigne_Monetico::MONETICOPAIEMENT_VERSION, $this->get('tpeNumber'), $oHmac->computeHmac(sprintf(paiementEnLigne_Monetico::MONETICOPAIEMENT_CTLHMACSTR, paiementEnLigne_Monetico::MONETICOPAIEMENT_VERSION, $this->get('tpeNumber'))));

        // Data to certify
        $phase1go_fields = sprintf(paiementEnLigne_Monetico::MONETICOPAIEMENT_PHASE1GO_FIELDS, $this->get('tpeNumber'),
            $sDate,
            $sMontant,
            $sDevise,
            $sReference,
            $sTexteLibre,
            paiementEnLigne_Monetico::MONETICOPAIEMENT_VERSION,
            $sLangue,
            $merchantId,
            $sEmail,
            $sNbrEch,
            $sDateEcheance1,
            $sMontantEcheance1,
            $sDateEcheance2,
            $sMontantEcheance2,
            $sDateEcheance3,
            $sMontantEcheance3,
            $sDateEcheance4,
            $sMontantEcheance4,
            $sOptions);

        // MAC computation
        $sMAC = $oHmac->computeHmac($phase1go_fields);

        $hidden = 'hidden';
//        $hidden = 'text';
        $data = '<form method="post" name="'.$formName.'" id="'.$formId.'" action="'.$this->getBankURL().'">';
        $data .= '<input type="'.$hidden.'" name="version"             id="version"        value="'.paiementEnLigne_Monetico::MONETICOPAIEMENT_VERSION.'" />';
        $data .= '<input type="'.$hidden.'" name="TPE"                 id="TPE"            value="'.$this->get('tpeNumber').'" />';
        $data .= '<input type="'.$hidden.'" name="date"                id="date"           value="'.$sDate.'" />';
        $data .= '<input type="'.$hidden.'" name="montant"             id="montant"        value="'.$sMontant . $sDevise.'" />';
        $data .= '<input type="'.$hidden.'" name="reference"           id="reference"      value="'.$sReference.'" />';
        $data .= '<input type="'.$hidden.'" name="MAC"                 id="MAC"            value="'.$sMAC.'" />';
        $data .= '<input type="'.$hidden.'" name="url_retour"          id="url_retour"     value="'.$this->get('returnURL').'" />';
        $data .= '<input type="'.$hidden.'" name="url_retour_ok"       id="url_retour_ok"  value="'.$this->get('returnURL').'&paiement=OK" />';
        $data .= '<input type="'.$hidden.'" name="url_retour_err"      id="url_retour_err" value="'.$this->get('returnURL').'&paiement=NOK" />';
        $data .= '<input type="'.$hidden.'" name="lgue"                id="lgue"           value="'.$sLangue.'" />';
        $data .= '<input type="'.$hidden.'" name="societe"             id="societe"        value="'.$merchantId.'" />';
        $data .= '<input type="'.$hidden.'" name="texte-libre"         id="texte-libre"    value="'.$sTexteLibre.'" />';
        $data .= '<input type="'.$hidden.'" name="mail"                id="mail"           value="'.$sEmail.'" />';
        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------
          SECTION PAIEMENT FRACTIONNE - Section spécifique au paiement fractionné
          
          INSTALLMENT PAYMENT SECTION - Section specific to the installment payment
         --------------------------------------------------------------------------------------------------------------------------------------------------------------*/
        $data .= '<input type="'.$hidden.'" name="nbrech"              id="nbrech"         value="'.$sNbrEch.'" />';
        $data .= '<input type="'.$hidden.'" name="dateech1"            id="dateech1"       value="'.$sDateEcheance1.'" />';
        $data .= '<input type="'.$hidden.'" name="montantech1"         id="montantech1"    value="'.$sMontantEcheance1.'" />';
        $data .= '<input type="'.$hidden.'" name="dateech2"            id="dateech2"       value="'.$sDateEcheance2.'" />';
        $data .= '<input type="'.$hidden.'" name="montantech2"         id="montantech2"    value="'.$sMontantEcheance2.'" />';
        $data .= '<input type="'.$hidden.'" name="dateech3"            id="dateech3"       value="'.$sDateEcheance3.'" />';
        $data .= '<input type="'.$hidden.'" name="montantech3"         id="montantech3"    value="'.$sMontantEcheance3.'" />';
        $data .= '<input type="'.$hidden.'" name="dateech4"            id="dateech4"       value="'.$sDateEcheance4.'" />';
        $data .= '<input type="'.$hidden.'" name="montantech4"         id="montantech4"    value="'.$sMontantEcheance4.'" />';


//        $data .= '<input type="submit" value="Payer">';
        $data .= '</form>';

        return array("erreur"=>false,"data"=>$data);
    }

    function getBankURL()
    {
        if ($this->get('modeTest') == 'TEST') return paiementEnLigne_Monetico::MONETICOPAIEMENT_URLPAYMENTTEST;
        else 						return paiementEnLigne_Monetico::MONETICOPAIEMENT_URLPAYMENT;
    }

    function verifRetour()
    {

        $dataRetour = $this->getDataRetour();
        $oHmac = new MoneticoPaiement_Hmac($this->get('secretKey'));

        if ($this->get('modeRetour') == "redirect") return $dataRetour;
        $phase2back_fields = sprintf(paiementEnLigne_Monetico::MONETICOPAIEMENT_PHASE2BACK_FIELDS, $this->get('tpeNumber'),
            $dataRetour["date"],
            $dataRetour['montant'],
            $dataRetour['reference'],
            $dataRetour['texte-libre'],
            paiementEnLigne_Monetico::MONETICOPAIEMENT_VERSION,
            $dataRetour['code-retour'],
            $dataRetour['cvx'],
            $dataRetour['vld'],
            $dataRetour['brand'],
            $dataRetour['status3ds'],
            $dataRetour['numauto'],
            $dataRetour['motifrefus'],
            $dataRetour['originecb'],
            $dataRetour['bincb'],
            $dataRetour['hpancb'],
            $dataRetour['ipclient'],
            $dataRetour['originetr'],
            $dataRetour['veres'],
            $dataRetour['pares']
        );

        $amount = (float)substr($dataRetour["montant"],0,-3);


        //$date = "01/02/2016 a 12:13:14";
        if(preg_match("|([0-9]{1,2})/([0-9]{1,2})/([0-9]{4}) a ([0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})|",$dataRetour["date"],$matches)){
            $date = $matches[3].'-'.$matches[2].'-'.$matches[1].' '.$matches[4];
            $this->set('transactionDateTime',$date);
        }else{
            //echo "erreur pregmatch";
        }

        $this->set('transactionReference',$dataRetour["reference"]);
        $this->set('amount',$amount);
        $this->set('authorisationId',$dataRetour["numauto"]);
        $this->set('customerIpAddress',$dataRetour["ipclient"]);

        $brand = Array('AM'=>'AMEX','CB'=>'CB','MC'=>'MASTERCARD','VI'=>'VISA','na'=>'NA');
        $this->set('paymentMeanBrand',$brand[$dataRetour["brand"]]);
        $this->set('paymentMeanType','CARD');
        $this->set('maskedPan',$dataRetour["hpancb"]);

        list($customerId,$orderId) = explode("|",$dataRetour["texte-libre"]);
        $this->set('customerId',$customerId);
        $this->set('orderId',$orderId);


        if ($oHmac->computeHmac($phase2back_fields) == strtolower($dataRetour['MAC']))
        {
            switch($dataRetour['code-retour']) {

                case "Annulation" :
                    // Paiement refusé
                    $this->set('responseCode',$dataRetour["motifrefus"]);
                    $ret = $dataRetour["motifrefus"];
                    break;

                case "payetest":
                    // Paiement accepté sur le serveur de test
                    $this->set('responseCode',0);
                    $ret = true;
                    break;

                case "paiement":
                    // Paiement accepté sur le serveur de production
                    $this->set('responseCode',0);
                    $ret = true;
                    break;
            }

            $receipt = paiementEnLigne_Monetico::MONETICOPAIEMENT_PHASE2BACK_MACOK;
        }
        else
        {
            // traitement en cas de HMAC incorrect
            // your code if the HMAC doesn't match
            $this->set('responseCode',"HMAC doesn't match");
            $receipt = paiementEnLigne_Monetico::MONETICOPAIEMENT_PHASE2BACK_MACNOTOK;
            $ret = "HMAC doesn't match";
        }
		printf (paiementEnLigne_Monetico::MONETICOPAIEMENT_PHASE2BACK_RECEIPT, $receipt);
        return $ret;
    }


}




/*****************************************************************************
 *
 * Classe / Class : MoneticoPaiement_Hmac
 *
 *****************************************************************************/

class MoneticoPaiement_Hmac {

    private $_sUsableKey;	// La clé du TPE en format opérationnel / The usable TPE key

    // ----------------------------------------------------------------------------
    //
    // Constructeur / Constructor
    //
    // ----------------------------------------------------------------------------

    function __construct($merchantKey) {

        $this->_sUsableKey = $this->_getUsableKey($merchantKey);
    }

    // ----------------------------------------------------------------------------
    //
    // Fonction / Function : _getUsableKey
    //
    // Renvoie la clé dans un format utilisable par la certification hmac
    // Return the key to be used in the hmac function
    //
    // ----------------------------------------------------------------------------

    private function _getUsableKey($merchantKey){

        $hexStrKey  = substr($merchantKey, 0, 38);
        $hexFinal   = "" . substr($merchantKey, 38, 2) . "00";

        $cca0=ord($hexFinal);

        if ($cca0>70 && $cca0<97)
            $hexStrKey .= chr($cca0-23) . substr($hexFinal, 1, 1);
        else {
            if (substr($hexFinal, 1, 1)=="M")
                $hexStrKey .= substr($hexFinal, 0, 1) . "0";
            else
                $hexStrKey .= substr($hexFinal, 0, 2);
        }


        return pack("H*", $hexStrKey);
    }

    // ----------------------------------------------------------------------------
    //
    // Fonction / Function : computeHmac
    //
    // Renvoie le sceau HMAC d'une chaine de données
    // Return the HMAC for a data string
    //
    // ----------------------------------------------------------------------------

    public function computeHmac($sData) {

        return strtolower(hash_hmac("sha1", $sData, $this->_sUsableKey));

        // If you don't have PHP 5 >= 5.1.2 and PECL hash >= 1.1 
        // you may use the hmac_sha1 function defined below
        //return strtolower($this->hmac_sha1($this->_sUsableKey, $sData));
    }

    // ----------------------------------------------------------------------------
    //
    // Fonction / Function : hmac_sha1
    //
    // RFC 2104 HMAC implementation for PHP >= 4.3.0 - Creates a SHA1 HMAC.
    // Eliminates the need to install mhash to compute a HMAC
    // Adjusted from the md5 version by Lance Rushing .
    //
    // Implémentation RFC 2104 HMAC pour PHP >= 4.3.0 - Création d'un SHA1 HMAC.
    // Elimine l'installation de mhash pour le calcul d'un HMAC
    // Adaptée de la version MD5 de Lance Rushing.
    //
    // ----------------------------------------------------------------------------

    public function hmac_sha1 ($key, $data) {

        $length = 64; // block length for SHA1
        if (strlen($key) > $length) { $key = pack("H*",sha1($key)); }
        $key  = str_pad($key, $length, chr(0x00));
        $ipad = str_pad('', $length, chr(0x36));
        $opad = str_pad('', $length, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;

        return sha1($k_opad  . pack("H*",sha1($k_ipad . $data)));
    }

}
