<?php


/**
$url="https://payment-webinit.simu.mercanet.bnpparibas.net/paymentInit";
$urlResponse="automaticResponseUrl=http://www.netika.net/bnp/bnp.php";
$urlResponseManuel="normalReturnUrl=http://www.netika.net/bnp/bnpManuel.php";
$idCommercant="002001000000001";
$cleSecrete="002001000000001_KEY1";
$montant="55";
$monnaie="978";
$transactionReference=uniqid();

$data = "amount=".$montant."|currencyCode=".$monnaie."|merchantId=".$idCommercant."|".$urlResponseManuel."|".$urlResponse."|transactionReference=".$transactionReference."|keyVersion=1";
$deal = hash('sha256', $data.$cleSecrete);
*/

CLASS paiementEnLigne_Mercanet extends paiementEnLigne
{
	const MERCANETPAIEMENT_KEYVERSION			= '1';
	const MERCANETPAIEMENT_INTERFACEVERSION		= 'HP_2.9';
	const MERCANETPAIEMENT_DEVISEEURO			= '978';
	const MERCANETPAIEMENT_URLPAIEMENTTEST		= 'https://payment-webinit.simu.mercanet.bnpparibas.net/paymentInit';
	const MERCANETPAIEMENT_URLPAIEMENT			= 'https://payment-webinit.mercanet.bnpparibas.net/paymentInit';

    function getProvider(){
        return 'MERCANET';
    }
    function getAmount()
    {
        return "".(parent::get('amount') * 100);
    }
    
    function setAmount($amount)
    {
        $this->set('amount',$amount/100);
    }

    function stringANS($txt) {
    
      return !preg_match("/[^a-zA-Z0-9_\-]/", $txt);
    
    }
    
    function checkData()
    {
        $ret=Array();
        if ($this->getAmount() == '' || $this->getAmount() == 0) $ret[]=_s("Mauvais montant");
        if ($this->get('merchantId') == '') $ret[]=_s("Aucun code commercant");
        if ($this->get('returnURL') == '') $ret[]=_s("Aucune adresse web du site pour le retour");
        if ($this->get('responseURL') == '') $ret[]=_s("Aucun adresse web pour les réponses de la banque");
        if ($this->get('transactionReference') == '') $ret[]=_s("Mauvais numéro de transaction");
        if ($this->get('customerId') =='') $ret[]=_s("Mauvais numéro de client final");
        if ($this->get('orderId') == '') $ret[]=_s("Mauvais numéro de commande");
        if ($this->get('secretKey') == '') $ret[]=_s("Mauvaise clé");

        if (ctype_alnum($this->get('transactionReference')) === false) $ret[]=_s("La référence de la transaction doit être un alphanumerique");
        if (strlen($this->get('transactionReference'))>35) $ret[]=_s("La référence de la transaction doit être un alphanumerique de maximum 35 caractères");

        if (ctype_digit($this->getAmount()) === false) $ret[]=sprintf(_s("Le montant doit être un numérique (%s)"),$this->getAmount());
        if (strlen($this->getAmount())>12) $ret[]=_s("Le montant doit être un numérique de maximum 12 caractères");

        if (ctype_digit($this->get('merchantId')) === false) $ret[]=sprintf(_s("La référence du commercant doit être un numérique (%s)"),$this->get('merchantId'));
        if (strlen($this->get('merchantId'))>15) $ret[]=_s("La référence du commercant doit être un numérique de maximum 15 caractères");

        if (ctype_alnum($this->get('customerId')) === false) $ret[]=_s("La référence du client doit être un alphanumérique");
        if (strlen($this->get('customerId'))>19) $ret[]=_s("La référence du client doit être un alphanumerique de maximum 19 caractères");

        if ($this->stringANS($this->get('orderId')) === false) $ret[]=sprintf(_s("La référence de commande doit être un alphanumérique (%s)"),$this->get('orderId'));
        if (strlen($this->get('orderId'))>32) $ret[]=_s("La référence de commande doit être un alphanumerique de maximum 32 caractères");


        if (count($ret) == 0) return true;
        else return implode(", ",$ret);
    }

    function getForm($formName, $formId)
    {
        if (($resCheck = $this->checkData()) !== true) return array("erreur"=>true,"data"=>$resCheck);
        $data = "amount=" . $this->getAmount() .
            "|currencyCode=" . paiementEnLigne_Mercanet::MERCANETPAIEMENT_DEVISEEURO . //On défini l'EURO comme monnaie
            "|merchantId=" . $this->get('merchantId') .
            "|normalReturnUrl=" . $this->get('returnURL') .
            "|automaticResponseUrl=" . $this->get('responseURL') .
            "|transactionReference=" . $this->get('transactionReference') .
            "|customerId=" . $this->get('customerId') .
            "|orderId=" . $this->get('orderId') .
            "|keyVersion=".paiementEnLigne_Mercanet::MERCANETPAIEMENT_KEYVERSION;
        $deal = hash('sha256', $data . $this->get('secretKey') );

        $form = "
            <form method=\"post\" name=\"" . $formName . "\" id=\"" . $formId . "\" action=\"" . $this->getBankURL() . "\">
                <input type=\"hidden\" name=\"Data\" value=\"" . $data . "\">
                <input type=\"hidden\" name=\"InterfaceVersion\" value=\"".paiementEnLigne_Mercanet::MERCANETPAIEMENT_INTERFACEVERSION."\">
                <input type=\"hidden\" name=\"Seal\" value=\"" . $deal . "\">
            </form>
        ";
        return array("erreur"=>false,"data"=>$form);
    }

    function getBankURL()
    {
        if ($this->get('modeTest') == 'TEST') return paiementEnLigne_Mercanet::MERCANETPAIEMENT_URLPAIEMENTTEST;
        else 						return paiementEnLigne_Mercanet::MERCANETPAIEMENT_URLPAIEMENT;
    }


    function verifRetour()
    {
        $dataRetour = $this->getDataRetour();
        $return = stripslashes($dataRetour["Data"]);
        $seal = $dataRetour["Seal"];
        $sealCalcule = hash('sha256', $return . $this->get('secretKey'));

        if ($seal != $sealCalcule) {
            //Le hash avec la clé n'est pas bonne
            return sprintf(_s("Le hash avec la clé n'est pas bonne %s - %s"),$seal,$sealCalcule);
        } else {
            preg_match_all("/(([^=]+)=([^|]*)\|?)/",$return,$dataExploded);
            foreach($dataExploded[2] as $key => $val) {
                $dataTmp[$val] = $dataExploded[3][$key];
            }
            /*********************************** Faire toutes les variables de retour intéressante ******************************/

            $numDemande = substr($dataTmp["transactionReference"],0,-10);

            if ($dataTmp["responseCode"] == "00") {
                $this->set('responseCode',0);
                $this->set('responseCodeTxt',$this->responseCodeDescription($dataTmp["responseCode"]));
                $this->set('merchantId',$dataTmp["merchantId"]);
                $this->set('customerId',$dataTmp["customerId"]);
                $this->set('orderId',$dataTmp["orderId"]);
                $this->set('transactionDateTime',date("Y-m-d H:i:s",strtotime($dataTmp["transactionDateTime"]))); //date("U",strtotime('2012-01-18T11:45:00+01:00'));
                $this->set('transactionReference',$numDemande);
                $this->setAmount($dataTmp["amount"]);
                $this->set('authorisationId',$dataTmp["authorisationId"]);
                $this->set('customerIpAddress',$dataTmp["customerIpAddress"]);
                $this->set('paymentMeanBrand',$dataTmp["paymentMeanBrand"]); //VISA, MASTERCARD
                $this->set('paymentMeanType',$dataTmp["paymentMeanType"]); //CARD, PAYPAL, ...
                $this->set('maskedPan',$dataTmp["maskedPan"]);
                return true;
            }
            else {
                $this->set('responseCode',$dataTmp["responseCode"]);
                $this->set('responseCodeTxt',$this->responseCodeDescription($dataTmp["responseCode"]));
                $this->set('merchantId',$dataTmp["merchantId"]);
                $this->setAmount($dataTmp["amount"]);
                $this->set('customerId',$dataTmp["customerId"]);
                $this->set('orderId',$dataTmp["orderId"]);
                $this->set('transactionDateTime',date("Y-m-d H:i:s",strtotime($dataTmp["transactionDateTime"]))); //date("U",strtotime('2012-01-18T11:45:00+01:00'));
                $this->set('transactionReference', $numDemande);
                return $this->responseCodeDescription($dataTmp["responseCode"]);
            }
        }

    }

    function responseCodeDescription($error)
    {

        switch ($error) {
            case "00" :
                return _s("Autorisation acceptée");
                break;
            case "02" :
                return _s("Demande d’autorisation par téléphone à la banque à cause d’un dépassement du plafond d’autorisation sur la carte, si vous êtes autorisé à forcer les transactions.");
                break;
            case "03" :
                return _s("Contrat commerçant invalide");
                break;
            case "05" :
                return _s("Autorisation refusée");
                break;
            case "11" :
                return _s("Utilisé dans le cas d'un contrôle différé. Le PAN est en opposition");
                break;
            case "12" :
                return _s("Transaction invalide, vérifier les paramètres transférés dans la requête");
                break;
            case "14" :
                return _s("Coordonnées du moyen de paiement invalides (ex: n° de carte ou cryptogramme visuel de la carte)");
                break;
            case "17" :
                return _s("Annulation de l’internaute");
                break;
            case "24" :
                return _s("Opération impossible. L’opération que vous souhaitez réaliser n’est pas compatible avec l’état de la transaction.");
                break;
            case "25" :
                return _s("Transaction non trouvée dans la base de données Sips");
                break;
            case "30" :
                return _s("Erreur de format");
                break;
            case "34" :
                return _s("Suspicion de fraude");
                break;
            case "40" :
                return _s("Fonction non supportée : l’opération que vous souhaitez réaliser ne fait pas partie de la liste des opérations auxquelles vous êtes autorisés");
                break;
            case "51" :
                return _s("Montant trop élevé");
                break;
            case "54" :
                return _s("Date de validité du moyen de paiement est dépassée");
                break;
            case "60" :
                return _s("Transaction en attente");
                break;
            case "63" :
                return _s("Règles de sécurité non respectées, transaction arrêtée");
                break;
            case "75" :
                return _s("Nombre de tentatives de saisie des coordonnées du moyen de paiement dépassé");
                break;
            case "90" :
                return _s("Service temporairement indisponible");
                break;
            case "94" :
                return _s("Transaction dupliquée : le transactionReference de la transaction a déjà été utilisé");
                break;
            case "97" :
                return _s("Délais expiré, transation refusée");
                break;
            case "99" :
                return _s("Problème temporaire au niveau du serveur Sips");
                break;
            default :
                return "";
                break;
        }
    }
}

?>
