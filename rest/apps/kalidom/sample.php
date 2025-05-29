<?php
class apps_kalidom_sample extends RestController {
	public function get() {
		return NULL;
	}
	public function post() {
		if(!$this->paramsRequired(Array('token','session','data','checksum'))) return false;
		
		//file_put_contents('/var/log/kalires/sample.params.log',"\n[".date("Y-m-d H:i:s")."] PARAMS ".print_r($this->request['params'],true));

		if(!$this->setSession($this->param('session'))){
			kdDebug('Sample error 41 Invalid session : '.$_SESSION['lastError'],$_SESSION['userCode'],$_SESSION['token'],$this->param('session'));		
			$this->response = array('resultCode' => '41', 'resultMessage'=>'Invalid session');
			$this->responseStatus = 200;
			return false;
		}
		
		if($_SESSION['token'] != $this->param('token')){
			kdDebug('Sample error 41 Invalid session token : '.$this->param('token').' != '.$_SESSION['token'],$_SESSION['userCode'],$_SESSION['token'],$this->param('session'));		
			$this->response = array('resultCode' => '41', 'resultMessage'=>'Invalid session : bad token');
			$this->responseStatus = 200;
			return false;
		}

		$checksum = sha1('hu-A'.$this->param('token',false).'+2Tf'.$this->param('data',false).'KB@6'.$this->param('session',false).'Zv(1');
		if($this->param('checksum') != $checksum){
			kdDebug('Sample error 42 Invalid checksum : '.$this->param('checksum').' != '.$checksum,$_SESSION['userCode'],$_SESSION['token'],$this->param('session'));
			$this->response = array('resultCode' => '42', 'resultMessage'=>'Invalid checksum : '.$checksum);
			$this->responseStatus = 200;
			return false;
		}

		$data = $this->param('data',false);
		if(false) {
			$key = hash("sha256",'$Hf:'.substr($this->param('session',false),0,5).'aC<='.substr($this->param('token',false),-5,5).'*F#t'.substr($this->param('checksum',false),5,5).'LRtç');
			$data = mcrypt_decrypt(MCRYPT_3DES,$key,$data,MCRYPT_MODE_ECB);
		}
		$data = utf8_decode($data);

		$dataDom = json_decode($data,true);
		if(!is_array($dataDom)) {
			kdDebug('Sample error 45 Error decoding data JSON',$_SESSION['userCode'],$_SESSION['token'],$this->param('session'));		
			$this->response = array('resultCode' => '45', 'resultMessage'=>'Error decoding data JSON');
			$this->responseStatus = 200;
			return false;
		}
		
		//file_put_contents('/var/log/kalires/sample.datadom.log',"\n[".date("Y-m-d H:i:s")."] DATADOM ".print_r($dataDom,true));
		
		if(!is_array($dataDom["fields"]) || $dataDom["fields"]["firstName"] == '' || $dataDom["fields"]["birthName"] == '' || $dataDom["fields"]["birthDate"] == '' || $dataDom["fields"]["site"] == '') {
			kdDebug('Sample error 46 Error decoding fields array',$_SESSION['userCode'],$_SESSION['token'],$this->param('session'));		
			$this->response = array('resultCode' => '46', 'resultMessage'=>'Error decoding fields array');
			$this->responseStatus = 200;
			return false;
		}
		
		if( !function_exists('decode_items')){
			function decode_items(&$item) {
			    $item = utf8_decode($item);
			}
		}

		if(is_array($dataDom)) {
			array_walk_recursive($dataDom,'decode_items');
		}

		kdDebug('Sample decoded, Checksum is : '.$this->param('checksum'),$_SESSION['userCode'],$_SESSION['token'],$this->param('session'));

		$numPermanent = "";
		if($dataDom["qr"] != "") {
			$dataQr = json_decode($dataDom["qr"]);
			if(is_array($dataQr) && count($dataQr) == 8 && $dataQr[0] == "KALISIL" && $dataQr[1] != "") {
				$numPermanent = $dataQr[1];
			} else {
				kdDebug('Sample error 44 Error decoding QR-Code informations',$_SESSION['userCode'],$_SESSION['token'],$this->param('session'));		
				$this->response = array('resultCode' => '44', 'resultMessage'=>'Invalid qr code');
				$this->responseStatus = 200;
				return false;
			}
		}

		$rangNaissance = "";
		$vitaleXmlError = 0;
		$vitaleXmlTxt = "";
		if(isset($dataDom["vitale"]) && $dataDom["vitale"] != "") {
			if(!isset($dataDom["fields"]["beneficiaryId"]) || $dataDom["fields"]["beneficiaryId"] == ""){
				$vitaleXmlError = 1;
				$vitaleXmlTxt = "beneficiaryId not found in fields";
			} else {
				$xml = simplexml_load_string(utf8_encode($dataDom["vitale"]));
				if(!$xml){
					$vitaleXmlError = 2;
					$vitaleXmlTxt = "error decoding XML";
				} else {
					$dataBeneficiaire = $xml->xpath("benef_display[@chainage='".$dataDom["fields"]["beneficiaryId"]."']");
					if(count($dataBeneficiaire) > 0){
						$rangNaissance = (string) $dataBeneficiaire[0]->{'BEN-RNG'};
					} else {
						$vitaleXmlError = 3;
						$vitaleXmlTxt = "chainage beneficiaryId '".$dataDom["fields"]["beneficiaryId"]."' not found in benef_display";
					}
				}
			}			
		}
		
		if($vitaleXmlError > 0) {
			kdDebug('Sample error 43 Failed loading vitale XML ('.$vitaleXmlError.': '.$vitaleXmlTxt.')',$_SESSION['userCode'],$_SESSION['token'],$this->param('session'));	
			$this->response = array('resultCode' => '43', 'resultMessage'=>'Failed loading vitale XML ('.$vitaleXmlError.': '.$vitaleXmlTxt.')');
			$this->responseStatus = 200;
			return false;
		}

		$dataPC = Array(
			"origine"				=> "kalidom",
			"kaliresType"			=> "preleveur",
			"kaliresReference"		=> $_SESSION['userCode'],
			"kalidomToken"			=> $_SESSION['token'],
			"statusPrescription"	=> "valide",
			"idSiteDest"			=> $dataDom["fields"]["site"],
			"numPermanent"			=> $numPermanent,
			"numDemandeExterne"		=> $this->param('checksum'),
			"civilite"				=> $dataDom["fields"]["title"],
			"nomJeuneFille"			=> $dataDom["fields"]["birthName"],
			"nom"					=> Array($dataDom["fields"]["lastName"],$dataDom["fields"]["firstName"]),
			"caisse"				=> Array("numeroSecu"=>$dataDom["fields"]["insuranceNumber"],"rangNaissance"=>$rangNaissance),
			"dateNaissance"			=> $dataDom["fields"]["birthDate"],
			"email"					=> $dataDom["fields"]["email"],
			"commentaires"			=> Array("autre"=>$dataDom["fields"]["comment"]),
			"dateOrdonnance"		=> $dataDom["fields"]["ordonnanceDate"],
			"datePrelevement"		=> substr($dataDom["fields"]["date"],6,4)."-".substr($dataDom["fields"]["date"],3,2)."-".substr($dataDom["fields"]["date"],0,2),
			"heurePrelevement"		=> substr($dataDom["fields"]["date"],11,5),
			"scan"					=> $dataDom["pictures"],
			"vitale"				=> $dataDom["vitale"],
			"qr"					=> $dataDom["qr"]
		);

		//file_put_contents('/var/log/kalires/sample.sample.log',"\n[".date("Y-m-d H:i:s")."] SAMPLE ".print_r($dataPC,true));
		
		$scdp = new SoapClientPrescription();
		$sampleId = $scdp->envoiDemandePresc($dataPC);
		if($sampleId > 0) {
			kdDebug('Sample accepted, Id is : '.$sampleId,$_SESSION['userCode'],$_SESSION['token'],$this->param('session'));
			$this->response = $sampleId;
			$this->responseStatus = 200;
		} else {
			kdDebug('Sample error 41 Invalid token',$_SESSION['userCode'],$_SESSION['token'],$this->param('session'));		
			$this->response = array('resultCode' => '41', 'resultMessage'=>'Invalid token');
			$this->responseStatus = 200;
			return false;
		}
	}

	public function put() {
		return NULL;
	}
	public function delete() {
		return NULL;
	}
}
