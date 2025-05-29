<?php
class apps_kalidom_login extends RestController {
	public function get() {
		global $sc;

		if(!$this->paramsRequired(Array('customerCode','userCode','userPassword','token','apiVersion','softVersion'))) return false;

		$login = $sc->loginDemandeur($this->param('userCode'), $this->param('userPassword'), '', $this->param('token'), true);

		if( $login === false ) {
			kdDebug('Login error 34 Wrong user/password : '.$this->param('userPassword'),$this->param('userCode'),$this->param('token'));		
			$this->response = array('resultCode' => '34', 'resultMessage'=>'Wrong password');
			$this->responseStatus = 200;
			return false;
		}

		if( $login->accountLocked ) {
			kdDebug('Login error 34 Account is locked : '.$this->param('userCode'),$this->param('userCode'),$this->param('token'));		
			$this->response = array('resultCode' => '34', 'resultMessage'=>'Account is locked');
			$this->responseStatus = 200;
			return false;
		}

		if( $login->badToken ) {
			kdDebug('Login error 33 Invalid token : '.$this->param('token'),$this->param('userCode'),$this->param('token'));		
			$this->response = array('resultCode' => '33', 'resultMessage'=>'Invalid token');
			$this->responseStatus = 200;
			return false;
		}

		$session = $this->createSession();
		kdDebug('Login OK, Session is : '.$session,$this->param('userCode'),$this->param('token'));

		$_SESSION['token'] = $this->param('token');
		$_SESSION['userCode'] = $this->param('userCode');
		
		$userName = $login->prenom.' '.$login->nom;
		$messageOfTheDay = "Bienvenue sur KaliDom !";
		
		$return = Array(
			'session' 			=> $session,
			'userName' 			=> $userName,
			'messageOfTheDay' 	=> $messageOfTheDay,
		);

		$this->response = $return;
		$this->responseStatus = 200;
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
