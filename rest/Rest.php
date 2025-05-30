<?php
/*
 * Copyright 2011 <http://voidweb.com>.
 * Author: Deepesh Malviya <https://github.com/deepeshmalviya>.
 * 
 * Simple-REST - Lightweight PHP REST Library
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License. 
 */

/**
 * Class implements RESTfulness
 */
class Rest {
	
	private $request = array(); // Array storing request
	private $response; // Array storing response
	
	const DEFAULT_RESPONSE_FORMAT = 'json'; // Default response format
	
	/**
	 * Constructor
	 * calls processRequest internally
	 */
	public function __construct() {
		$this->processRequest();		
	}
	
	/**
	 * Function processing raw HTTP request headers & body
	 * and populates them to class variables. 
	 */
	private function processRequest() {
		$this->request['resource'] = (isset($_GET['RESTurl']) && !empty($_GET['RESTurl'])) ? $_GET['RESTurl'] : 'index';
		unset($_GET['RESTurl']);
		$this->request['method'] = strtolower($_SERVER['REQUEST_METHOD']);
		$this->request['headers'] = $this->getHeaders();
		$this->request['format'] = isset($_GET['format']) ? trim($_GET['format']) : null;
		switch($this->request['method']) {
			case 'get':
				$this->request['params'] = $_GET;
				break;
			case 'post':
				$this->request['params'] = array_merge($_POST, $_GET);
				break;
			case 'put':
				parse_str(file_get_contents('php://input'), $this->request['params']);
            	break;
			case 'delete':
				$this->request['params'] = $_GET;
				break;
			default:
				break;
		}
		$this->request['content-type'] = $this->getResponseFormat($this->request['format']);
		if(!function_exists('trim_value')) {
			function trim_value(&$value) {
				$value = trim($value);
			}
		}
		array_walk_recursive($this->request, 'trim_value');
	}
	
	/**
	 * Function to resolve controller based on the resource name and http
	 * method (GET/POST/PUT/DELETE) using reflection and get the response.
	 * Passes the response to the response helpers class.
	 */
	public function process() {
		try	{			
			$controllerName = $this->getController();		
			if(null == $controllerName) {
				throw new Exception('Method not allowed', 405);
			}		
			$controller = new ReflectionClass($controllerName);
			if(!$controller->isInstantiable()) {
				throw new Exception('Bad Request', 400);
			}
			try {
				$method = $controller->getMethod($this->request['method']);
			} catch(ReflectionException $re) {
				throw new Exception('Unsupported HTTP method ' . $this->request['method'], 405);
			}
			if(!$method->isStatic()) {
				$controller = $controller->newInstance($this->request);
				if(!$controller->checkAuth()) {
					throw new Exception('Unauthorized', 401);
				}
				$method->invoke($controller);
				$this->response = $controller->getResponse();
				$this->responseStatus = $controller->getResponseStatus();
			} else {
				throw new Exception('Static methods not supported in Controllers', 500);
			}
			if(is_null($this->response)) {
				throw new Exception('Method not allowed', 405);
			}
		} catch (Exception $re)	{
			$this->responseStatus = $re->getCode();
			$this->response = array('ErrorCode' => $re->getCode(), 'ErrorMessage' => $re->getMessage());
		}
		$this->response()->send();
	}

	/**
	 * Function to resolve constroller from the Controllers
	 * directory based on resource name request.
	 */	
	private function getController() {
		$expected = $this->request['resource'];
// 		echo "expected=$expected\n";
// 		preg_match_all('|^([^/]*)/([^/]*)$|',$expected,$matches);
// 		var_dump($matches);
		$appsDir = "apps/";
		$dir = false;
		$file = false;
		$expected = preg_replace("/[^a-zA-Z0-9_\/]/", "", $expected);


		$dirs = explode("/",$expected);
		if(count($dirs)>1){
			$dir = implode("/",array_slice($dirs,0,count($dirs)-1));
			$file = $dirs[count($dirs)-1];
			if( !file_exists($appsDir.$dir."/".$file.".php") ){
				$dir = $dir . "/" . $file;
			}
		}else{
			$dir = $expected;
			$file = $dirs[0];
		}

// 		echo "dir=$dir file=$file : ";
		
		if( $file !== false && $dir !== false){
			if( file_exists($appsDir.$dir."/".$file.".php") ) {
// 				echo " OK!\n";
// 				echo "CLASS=apps_".str_replace("/","_",$dir."/".$file)."\n";
				return "apps_".str_replace("/","_",$dir."/".$file);
			}else{
//				echo " NOK!\n";
			}
		}else{
//			echo "NULL\n";
		}
		return null;
	}

	private function xmlHelper($data, $version = '1.0', $encoding = 'UTF-8') {
		$xml = new XMLWriter;
		$xml->openMemory();
		$xml->startDocument($version, $encoding);

		if(!function_exists('write')) {
			function write(XMLWriter $xml, $data, $old_key = null) {
				foreach($data as $key => $value){
					if(is_array($value)){
						if(!is_int($key)) {
							$xml->startElement($key);
						}
						write($xml, $value, $key);
						if(!is_int($key)) {
							$xml->endElement();
						}
						continue;
					}
					// Special handling for integer keys in array
					$key = (is_int($key)) ? $old_key.$key : $key;
					$xml->writeElement($key, $value);
				}
			}
		}
		write($xml, $data);
		return $xml->outputMemory(true);
	}
	
	/**
	 * Function implementing xml response helper.
	 * Converts response array to xml response.
	 */
	private function xmlResponse() {
		return $this->xmlHelper($this->response);
	}

	/**
	 * Function implementating json response helper.
	 * Converts response array to json.
	 */
	private function jsonResponse() {
		function encode_items(&$item, $key)
		{
		    $item = utf8_encode($item);
		}
		$response = $this->response;
		if(is_array($response)) array_walk_recursive($response, 'encode_items');
		return json_encode($response);
	}

	/**
	 * Function implementing querystring response helper
	 * Converts response array to querystring.
	 */
	private function qsResponse() {
		return http_build_query($this->response);
	}

	private function response() {
		if(!empty($this->response)) {
			$method = $this->request['content-type'] . 'Response';
			$this->response = array('status' => $this->responseStatus, 'body' => $this->$method());
		} else {
			$this->request['content-type'] = 'querystring';
			$this->response = array('status' => $this->responseStatus, 'body' => $this->response);
		}
		
		return $this;
	}

	/**
	 * Function to get HTTP headers
	 */	
	private function getHeaders() {
		if(function_exists('apache_request_headers')) {
			return apache_request_headers();
		}
		$headers = array();
		$keys = preg_grep('{^HTTP_}i', array_keys($_SERVER));
		foreach($keys as $val) {
				$key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($val, 5)))));
				$headers[$key] = $_SERVER[$val];
			}
		return $headers;
	}
	
	private static $codes = array(  
            100 => 'Continue',  
            101 => 'Switching Protocols',  
            200 => 'OK',  
            201 => 'Created',  
            202 => 'Accepted',  
            203 => 'Non-Authoritative Information',  
            204 => 'No Content',  
            205 => 'Reset Content',  
            206 => 'Partial Content',  
            300 => 'Multiple Choices',  
            301 => 'Moved Permanently',  
            302 => 'Found',  
            303 => 'See Other',  
            304 => 'Not Modified',  
            305 => 'Use Proxy',  
            306 => '(Unused)',  
            307 => 'Temporary Redirect',  
            400 => 'Bad Request',  
            401 => 'Unauthorized',  
            402 => 'Payment Required',  
            403 => 'Forbidden',  
            404 => 'Not Found',  
            405 => 'Method Not Allowed',  
            406 => 'Not Acceptable',  
            407 => 'Proxy Authentication Required',  
            408 => 'Request Timeout',  
            409 => 'Conflict',  
            410 => 'Gone',  
            411 => 'Length Required',  
            412 => 'Precondition Failed',  
            413 => 'Request Entity Too Large',  
            414 => 'Request-URI Too Long',  
            415 => 'Unsupported Media Type',  
            416 => 'Requested Range Not Satisfiable',  
            417 => 'Expectation Failed',  
            500 => 'Internal Server Error',  
            501 => 'Not Implemented',  
            502 => 'Bad Gateway',  
            503 => 'Service Unavailable',  
            504 => 'Gateway Timeout',  
            505 => 'HTTP Version Not Supported'  
        );  
  
	/**
	 * Function returns HTTP response message based on HTTP response status code
	 */
	private function getStatusMessage($status) {
        return (isset(self::$codes[$status])) ? self::$codes[$status] : self::$codes[500];
    }

	private static $formats = array('xml', 'json', 'qs');
	
	/**
	 * Function returns response format from allowed list
	 * else the default response format
	 */
	private function getResponseFormat($format) {
		return (in_array($format, self::$formats)) ? $format : self::DEFAULT_RESPONSE_FORMAT;
	}

	private static $contentTypes = array(
				'xml' => 'application/xml',
				'json' => 'application/json',
				'qs' => 'text/plain'
			);

	/**
	 * Function returns response content type.
	 */
	private function getResponseContentType($type = null) {
		return self::$contentTypes[$type];
	}
		
	private function send() {
		$status = (isset($this->response['status'])) ? $this->response['status'] : 200;
		$contentType = $this->getResponseContentType($this->request['content-type']);
		$body = (empty($this->response['body'])) ? '' : $this->response['body'];

		$headers = 'HTTP/1.1 ' . $status . ' ' . $this->getStatusMessage($status);
		header($headers);
		header('Content-Type: ' . $contentType);
		echo $body;
	}
}

/**
 * Abstract Controller
 * To be extended by every controller in application
 */
abstract class RestController {
	protected $request;
	protected $response;
	protected $responseStatus;

	public function __construct($request) {
		$this->request = $request;		
	}


	final public function getResponseStatus() {
		return $this->responseStatus;
	}

	final public function getResponse() {
		return $this->response;
	}

	public function checkAuth() {
		return true;
	}

	function param($id,$decode=true){
		if( !function_exists('stripslashes_items') ) {
			function stripslashes_items(&$item, $key){
			    $item = stripslashes($item);
			}
		}
				
		if( !function_exists('decode_items') ) {
			function decode_items(&$item, $key){
				if( is_array($item) ){
					array_walk_recursive($item,'decode_items');
				}else{
					$item = utf8_decode($item);
				}
			}
		}
		
		$params = $this->request['params'][$id];
		if( is_array($params)){
			array_walk_recursive($params, 'stripslashes_items');
			if( $decode ) array_walk_recursive($params,'decode_items');
			return $params;
		}else{
			$params = stripslashes($params);

			if( $decode ) return utf8_decode($params);
			else return $params ;		
		} 
	}
	
	public function paramsRequired($paramsRequired=Array()){
		$params  = array_keys($this->request['params']);
		$missingParams = array_diff($paramsRequired,$params);
		
		if( is_array($missingParams) && count($missingParams)>0 ){
			$this->response = array('resultCode' => '4', 'resultMessage' => "Some parameters are missing : ".implode(",",$missingParams) );
			$this->responseStatus = 200;
			return false;
		}

		return true;
	}

	function createSession(){
		@session_destroy();

		session_id();
		session_start();
		$_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
		$_SESSION['time'] = time();
		return session_id();
	}

	function setSession($session){
		session_write_close();
		session_id($session);
		session_start();
	    try{
	        if(!isset($_SESSION['time'])) {
	        	$_SESSION['lastError'] = 'No session started';
	            throw new Exception('No session started.');
			}
			/*
	        if($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR']) {
	        	$_SESSION['lastError'] = 'IP Address mismatch ('.$_SESSION['IPaddress'].' != '.$_SERVER['REMOTE_ADDR'].')';
	            throw new Exception('IP Address mismatch (possible session hijacking attempt).');
			}
			*/
	        if($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
	        	$_SESSION['lastError'] = 'Useragent mixmatch ('.$_SESSION['userAgent'].' != '.$_SERVER['HTTP_USER_AGENT'].')';
	            throw new Exception('Useragent mixmatch (possible session hijacking attempt).');
			}
	        return true;

	    }catch(Exception $e){
	        return false;
	    }
	}

	// @codeCoverageIgnoreStart
	abstract public function get();
	abstract public function post();
	abstract public function put();
	abstract public function delete();
	// @codeCoverageIgnoreEnd
	
}
