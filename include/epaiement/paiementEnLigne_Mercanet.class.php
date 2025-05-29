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
        if ($this->get('responseURL') == '') $ret[]=_s("Aucun adresse web pour les r�ponses de la banque");
        if ($this->get('transactionReference') == '') $ret[]=_s("Mauvais num�ro de transaction");
        if ($this->get('customerId') =='') $ret[]=_s("Mauvais num�ro de client final");
        if ($this->get('orderId') == '') $ret[]=_s("Mauvais num�ro de commande");
        if ($this->get('secretKey') == '') $ret[]=_s("Mauvaise cl�");

        if (ctype_alnum($this->get('transactionReference')) === false) $ret[]=_s("La r�f�rence de la transaction doit �tre un alphanumerique");
        if (strlen($this->get('transactionReference'))>35) $ret[]=_s("La r�f�rence de la transaction doit �tre un alphanumerique de maximum 35 caract�res");

        if (ctype_digit($this->getAmount()) === false) $ret[]=sprintf(_s("Le montant doit �tre un num�rique (%s)"),$this->getAmount());
        if (strlen($this->getAmount())>12) $ret[]=_s("Le montant doit �tre un num�rique de maximum 12 caract�res");

        if (ctype_digit($this->get('merchantId')) === false) $ret[]=sprintf(_s("La r�f�rence du commercant doit �tre un num�rique (%s)"),$this->get('merchantId'));
        if (strlen($this->get('merchantId'))>15) $ret[]=_s("La r�f�rence du commercant doit �tre un num�rique de maximum 15 caract�res");

        if (ctype_alnum($this->get('customerId')) === false) $ret[]=_s("La r�f�rence du client doit �tre un alphanum�rique");
        if (strlen($this->get('customerId'))>19) $ret[]=_s("La r�f�rence du client doit �tre un alphanumerique de maximum 19 caract�res");

        if ($this->stringANS($this->get('orderId')) === false) $ret[]=sprintf(_s("La r�f�rence de commande doit �tre un alphanum�rique (%s)"),$this->get('orderId'));
        if (strlen($this->get('orderId'))>32) $ret[]=_s("La r�f�rence de commande doit �tre un alphanumerique de maximum 32 caract�res");


        if (count($ret) == 0) return true;
        else return implode(", ",$ret);
    }

    function getForm($formName, $formId)
    {
        if (($resCheck = $this->checkData()) !== true) return array("erreur"=>true,"data"=>$resCheck);
        $data = "amount=" . $this->getAmount() .
            "|currencyCode=" . paiementEnLigne_Mercanet::MERCANETPAIEMENT_DEVISEEURO . //On d�fini l'EURO comme monnaie
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
            //Le hash avec la cl� n'est pas bonne
            return sprintf(_s("Le hash avec la cl� n'est pas bonne %s - %s"),$seal,$sealCalcule);
        } else {
            preg_match_all("/(([^=]+)=([^|]*)\|?)/",$return,$dataExploded);
            foreach($dataExploded[2] as $key => $val) {
                $dataTmp[$val] = $dataExploded[3][$key];
            }
            /*********************************** Faire toutes les variables de retour int�ressante ******************************/

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
                return _s("Autorisation accept�e");
                break;
            case "02" :
                return _s("Demande d�autorisation par t�l�phone � la banque � cause d�un d�passement du plafond d�autorisation sur la carte, si vous �tes autoris� � forcer les transactions.");
                break;
            case "03" :
                return _s("Contrat commer�ant invalide");
                break;
            case "05" :
                return _s("Autorisation refus�e");
                break;
            case "11" :
                return _s("Utilis� dans le cas d'un contr�le diff�r�. Le PAN est en opposition");
                break;
            case "12" :
                return _s("Transaction invalide, v�rifier les param�tres transf�r�s dans la requ�te");
                break;
            case "14" :
                return _s("Coordonn�es du moyen de paiement invalides (ex: n� de carte ou cryptogramme visuel de la carte)");
                break;
            case "17" :
                return _s("Annulation de l�internaute");
                break;
            case "24" :
                return _s("Op�ration impossible. L�op�ration que vous souhaitez r�aliser n�est pas compatible avec l��tat de la transaction.");
                break;
            case "25" :
                return _s("Transaction non trouv�e dans la base de donn�es Sips");
                break;
            case "30" :
                return _s("Erreur de format");
                break;
            case "34" :
                return _s("Suspicion de fraude");
                break;
            case "40" :
                return _s("Fonction non support�e : l�op�ration que vous souhaitez r�aliser ne fait pas partie de la liste des op�rations auxquelles vous �tes autoris�s");
                break;
            case "51" :
                return _s("Montant trop �lev�");
                break;
            case "54" :
                return _s("Date de validit� du moyen de paiement est d�pass�e");
                break;
            case "60" :
                return _s("Transaction en attente");
                break;
            case "63" :
                return _s("R�gles de s�curit� non respect�es, transaction arr�t�e");
                break;
            case "75" :
                return _s("Nombre de tentatives de saisie des coordonn�es du moyen de paiement d�pass�");
                break;
            case "90" :
                return _s("Service temporairement indisponible");
                break;
            case "94" :
                return _s("Transaction dupliqu�e : le transactionReference de la transaction a d�j� �t� utilis�");
                break;
            case "97" :
                return _s("D�lais expir�, transation refus�e");
                break;
            case "99" :
                return _s("Probl�me temporaire au niveau du serveur Sips");
                break;
            default :
                return "";
                break;
        }
    }
}

?>
