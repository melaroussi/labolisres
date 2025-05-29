<?php
/**
 * Created by PhpStorm.
 * User: adrien.cara
 * Date: 29/08/2017
 * Time: 11:15
 */

CLASS paiementEnLigne_Ogone extends paiementEnLigne
{

	const OGONEPAIEMENT_URLPAIEMENT         = "https://secure.ogone.com/ncol/prod//orderstandard_utf8.asp";
	const OGONEPAIEMENT_URLPAIEMENTTEST     = "https://ogone.test.v-psp.com/ncol/test//orderstandard_utf8.asp";
	const OGONEPAIEMENT_DEVISE			    = 'EUR';
	const OGONEPAIEMENT_LANGUE			    = 'fr_FR';

	function checkData() {
		$ret = Array();
		
        if ($this->get('amount') == '' || $this->get('amount') == 0) $ret[]=_s("Mauvais montant");
        if ($this->get('merchantId') == '') $ret[]=_s("Aucun code commercant");
        if ($this->get('returnURL') == '') $ret[]=_s("Aucune adresse web du site pour le retour");
		if ($this->get('transactionReference') == '') $ret[]=_s("Mauvais numéro de transaction");
        if ($this->get('customerId') =='') $ret[]=_s("Mauvais numéro de client final");
        if ($this->get('orderId') == '') $ret[]=_s("Mauvais numéro de commande");

		
		if (count($ret) == 0) return true;
        else return implode(", ",$ret);
	}
	
	function getForm($formName,$formId) {

		if (($resCheck = $this->checkData()) !== true) return array("erreur"=>true,"data"=>$resCheck);
		
		$dataToHash =Array(
			'ACCEPTURL'		=> $this->get('returnURL'),
			'DECLINEURL'	=> $this->get('returnURL'),
			'PSPID'			=> $this->get('merchantId'),
			'ORDERID'		=> $this->get('orderId'),
			'AMOUNT'		=> (float) $this->get('amount') * 100,
			'CURRENCY'		=> paiementEnLigne_Ogone::OGONEPAIEMENT_DEVISE,
			'LANGUAGE'		=> paiementEnLigne_Ogone::OGONEPAIEMENT_LANGUE,
			'PARAMPLUS'		=> 'customerId='.$this->get('customerId').'&transactionReference='.$this->get('transactionReference').'&montantComplet='.$this->get('amount')
		);


		$hash = $this->computeSHA1($dataToHash);

		$data = '<form method="post" action="'.$this->getBankURL().'" id="'.$formName.'" name="'.$formId.'">
					<!-- general parameters: see Form parameters -->
					<input type="hidden" name="PSPID" value="'.$dataToHash['PSPID'].'">
					<input type="hidden" name="ORDERID" value="'.$dataToHash['ORDERID'].'">
					<input type="hidden" name="AMOUNT" value="'.$dataToHash['AMOUNT'].'">
					<input type="hidden" name="CURRENCY" value="'.$dataToHash['CURRENCY'].'">
					<input type="hidden" name="LANGUAGE" value="'.$dataToHash['LANGUAGE'].'">
					<!-- check before the payment: see Security: Check before the payment -->
					<input type="hidden" name="SHASIGN" value="'.$hash.'">
					<!-- post payment redirection: see Transaction feedback to the customer -->
					<input type="hidden" name="ACCEPTURL" value="'.$dataToHash['ACCEPTURL'].'">
					<input type="hidden" name="DECLINEURL" value="'.$dataToHash['DECLINEURL'].'">
					<input type="hidden" name="PARAMPLUS" value="'.$dataToHash['PARAMPLUS'].'">
				</form>';

        return array("erreur"=>false,"data"=>$data);
	}

	function getBankURL()
    {
        if ($this->get('modeTest') == 'TEST')   return paiementEnLigne_Ogone::OGONEPAIEMENT_URLPAIEMENTTEST;
        else 						            return paiementEnLigne_Ogone::OGONEPAIEMENT_URLPAIEMENT;
    }

	function verifRetour() {
		global $licence;

        $dataRetour = $this->getDataRetour();
		file_put_contents("/var/log/kalilab/paiement.log", print_r($dataRetour, true), FILE_APPEND);

		if (substr(strval($dataRetour["STATUS"]), 0, 1) == '9') {
			$dataRetour["paiement"] = "OK";
		} else if ($dataRetour["status"] == 2 || $dataRetour["STATUS"] == 1) {
			$dataRetour["paiement"] = "NOK";
		}

        if ($this->get('modeRetour') == "redirect") return $dataRetour;
		$dataToHash = Array(
			'ORDERID'=> $dataRetour['orderID'],
			'CURRENCY' => $dataRetour['currency'],
			'AMOUNT' => $dataRetour["amount"],
			'PM' => $dataRetour["PM"],
			'ACCEPTANCE' => $dataRetour["ACCEPTANCE"],
			'STATUS' => $dataRetour["STATUS"],
			'CARDNO' => $dataRetour["CARDNO"],
			'ED' => $dataRetour["ED"],
			'CN' => $dataRetour["CN"],
			'TRXDATE' => $dataRetour["TRXDATE"],
			'PAYID' => $dataRetour["PAYID"],
			'PAYIDSUB' => $dataRetour["PAYIDSUB"],
			'NCERROR' => $dataRetour["NCERROR"],
			'BRAND' => $dataRetour["BRAND"],
			'ECI' => $dataRetour["ECI"],
			'IP' => $dataRetour["IP"]
		);

		$hash = $SHA1 = $this->computeSHA1($dataToHash);

		if (strtoupper($hash) == strtoupper($dataRetour["SHASIGN"])) {
			if ($dataToHash["STATUS"] == "9") {
				$this->set('orderId', $dataRetour['orderID']);
				$this->set('transactionReference', $dataRetour['transactionReference']);
				$this->set('customerId', $dataRetour['customerId']);
				$this->set('amount', $dataRetour['montantComplet']);
				return true;
			} else  {
				$this->set('orderId', $dataRetour['orderID']);
				$this->set('transactionReference', $dataRetour['transactionReference']);
				$this->set('customerId', $dataRetour['customerId']);
				$this->set('amount', "0");
				return false;
			}
		}
		return false;

	}


	private function computeSHA1($dataToHash){
		global $licence;

		ksort($dataToHash);
		$cleSHA = sha1("STR|".substr($licence["numero"], 0, 4)."|STR");


		$stringToHash = "";
		foreach ($dataToHash as $key => $value) {
			if ($value != "") $stringToHash .= $key.'='.$value.$cleSHA;
		}

		return sha1($stringToHash);
	}
}