<?php
class apps_kalidom_init extends RestController {
	public function get() {
		global $sc,$conf;

		if(!$this->paramsRequired(Array('userCode','customerCode','token','apiVersion','softVersion'))) return false;
		
		$dataInit = $sc->initKaliDom( $this->param('userCode'), $this->param('token') );
		
		if(is_array($dataInit) && count($dataInit) > 0) {
			
			$customerLogo = $customerLogoExt = '';
			$logo = $conf['dataDir'].'logo/logo-kalires-TN.jpg';
			if(file_exists($logo)) {
				$customerLogo = base64_encode(file_get_contents($logo));
				$customerLogoExt = 'JPG';
			}
	
			$return = Array(
				'customerName' 			=> $dataInit['name'],
				'customerAddress' 		=> $dataInit['address'],
				'customerPhoneNumber' 	=> $dataInit['phone'],
				'customerLogo' 			=> $customerLogo,
				'customerLogoExt' 		=> $customerLogoExt,
				'customerData' 			=> Array('titles' => $dataInit["titles"], 'sites' => $dataInit["sites"]),
				'passwordChange' 		=> $dataInit['passwordChange']
			);
	
			kdDebug('Init OK, Password change is : '.$dataInit['passwordChange'].'',$this->param('userCode'),$this->param('token'));
			
			$this->response = $return;
			$this->responseStatus = 200;
			
		} else {
			kdDebug('Login error 23 User/Token unknown : '.$this->param('userCode').'/'.$this->param('token'));		
			$this->response = array('resultCode' => '23', 'resultMessage'=>'User/Token unknown');
			$this->responseStatus = 200;
			return true;
		}
		
	}
	public function post() {
		return NULL;
	}
	public function put() {
		return NULL;
	}
	public function delete() {
		return NULL;
	}
}
