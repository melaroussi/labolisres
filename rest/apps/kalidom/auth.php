<?php
class apps_kalidom_auth extends RestController {
	public function get() {
		global $sc;

		if(!$this->paramsRequired(Array('userCode','customerCode','apiVersion','softVersion'))) return false;

		// à l'auth, le token est donné par kalisil. on génère un token tmp pour la premiere transaction
		$tokenTmp = hash("sha256","po8*".$this->param('userCode')."la#4".date("Y-m-d")."+5Ak");

		$prelev = $sc->authKaliDom($this->param('userCode'),$tokenTmp);
		
		if(is_array($prelev) && count($prelev) > 0 && $prelev["token"] != "") {
			$this->response = array('resultCode' => 0, 'userCode' => $this->param('userCode'), 'token' => $prelev["token"]);
			$this->responseStatus = 200;
			kdDebug('Auth OK, Token is : '.$prelev["token"],$this->param('userCode'));
			return true;
		}

		kdDebug('Auth error 1 User unknown : '.$this->param('userCode'));		
		$this->response = array('resultCode' => '1', 'resultMessage' => 'User unknown');
		$this->responseStatus = 200;
		return false;

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
