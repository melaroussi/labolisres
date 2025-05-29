<?php
 /**
  * Classe de gestion des fichiers utilisateurs et fichiers temporaires
  * @author Adrien
  *  
  * @package KaliLab
  * @module 
  * @tests 
  **/
class Kfile {

	public static function path() {
		global $conf;
		if (!isset($conf["dataDir"]))
			return $conf["baseDir"];
		return $conf["dataDir"];
	}

	public static function get($file,$exists=true) {
		if($file == "") return false;
		if(strrpos($file, "/tmp/", -strlen($file)) === FALSE) $file = Kfile::path().str_replace(Kfile::path(),"",$file);
		if($exists === false) {
			if(substr($file,-1,1) == "/") {
				$dir = $file;
			} else {
				$dir = dirname($file);
			}
			if(file_exists($dir)) {
				return $file;
			}
		} else {
			if(file_exists($file)) {
				return $file;
			}
		}
		return false;
	}

	public static function getUrl($file, $inline = false, $logo = false, $fileExist = false) {
		global $conf;
		if($fileExist === true && Kfile::get($file)===false) {
			return false;
		}
		if($logo !== false) {
			$_SESSION['kfile_logo']=Array('file'=>str_replace(Kfile::path(),"",$file));
			return $conf['baseURL'] . 'logo.php'
						. ($inline?'?inline=1':'');	
		} else {
			$token = sha1(uniqid(''));
			$_SESSION['kfile_token'][$token]=Array('file'=>str_replace(Kfile::path(),"",$file));
			
			return $conf['baseURL'] . 'kfile.php?'
						. ($inline?'&inline=1':'')
						. '&token='.$token;		
		}
	}

	public static function addContent($file,$content) {
		return Kfile::putContent($file,$content,true);
	}
	
	public static function putContent($file,$content,$append=false) {
		$file = Kfile::get($file,false);
		if($file !== false && !is_dir($file)) {
			if($append) {
				return file_put_contents($file,$content,FILE_APPEND);
			} else {
				file_put_contents($file,$content);
				return file_exists($file);
			}
		}
		return false;
	}

	public static function move($source,$destination) {
		$sourceFile = Kfile::get($source);
		if($sourceFile !== false && is_file($sourceFile)) {
			$destinationFile = Kfile::get($destination,false);
			if($destination != "" && !is_dir($destinationFile)) {
				return rename($sourceFile,$destinationFile);
			}
		}
		return false;
	}

	public static function remove($file) {
		$file = Kfile::get($file);
		if($file !== false && is_file($file)) {
			return unlink($file);
		}
		return false;
	}
	
	public static function getContent($file) {
		$file = Kfile::get($file);
		if($file !== false) {
			return file_get_contents($file);
		}
		return false;
	}
	
	public static function pjGetContent($file,$name="",$mimeType="", $inline = false) {
		$file = Kfile::get($file);
		if($file !== false) {
			if($name == "") {
				$name = basename($file);
			}

			$mimeType = _mime_content_type($file);
			ini_set('zlib.output_compression','0');
			header('Content-Description: File Transfer');
			header('Content-Type: '.$mimeType);
			header('Content-Disposition: '.($inline?'inline':'attachment').'; filename="'.$name.'"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: '.filesize($file));
			readfile($file);
			return true;
		}
		return false;
	}
}

?>
