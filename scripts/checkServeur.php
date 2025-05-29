<?php
/*
 * @tests T02952
 *
Statut du serveur Kalilab/kalisil

FICHIERS OBLIGATOIRES : 

	include/licence.ver (kalirep, kalimodem, kalires) 
		OU conf/licence.ver (kalilab, kalisil)
	include/conf.inc.php (kalirep, kalimodem, kalires) 
		OU conf/conf.inc.php (kalilab, kalisil)
	include/class.phpmailer.php
	include/class.smtp.php
	include/lib.serverStatus.inc.php
	include/lib.sysInfo.inc.php
	scripts/checkServeur.php
	scripts/serverctl.sh

*/
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
define("CHECKSERVEUR_OK", 0);
define("CHECKSERVEUR_INPROGRESS", 1);
define("CHECKSERVEUR_ERROR", 2);
define("CHECKSERVEUR_RETABLISSEMENT", 3);

$baseDir = dirname(__FILE__) . "/..";
$confFile = $baseDir . "/conf/conf.inc.php";
if(file_exists($confFile)) {
	include ($confFile);
	if(!isset($argv[1])) { 
		$mode = "kalilab";
		if(KL_APP_NAME == "KaliSil") {
			$mode = "kalisil";
		}
	}
} else {
	$confFile = $baseDir . "/include/conf.inc.php";
	if(file_exists($confFile)) {
		include ($confFile);
	} else {
		die('ERROR : conf.inc.php not found');
	}
}

if(isset($argv[1]) && strtolower($argv[1]) == "kalirep") {
	$mode = "kalirep";
} elseif(isset($argv[1]) && strtolower($argv[1]) == "kalires") {
	$mode = "kalires";
} elseif(isset($argv[1]) && strtolower($argv[1]) == "kalimodem") {
	$mode = "kalimodem";
} elseif(isset($argv[1]) && substr($argv[1],0,5) == "test_") {
	$mode = $argv[1];
}

$testTodo = Array();
$restriction_service = false;
switch($mode) {
	case "kalilab":
		$testTodo[] = 'test_charge';
		$testTodo[] = 'test_services';
		$testTodo[] = 'test_mail';
		$testTodo[] = 'test_disque';
		$testTodo[] = 'test_disque_distant';
		break;
	case "kalisil":
		$testTodo[] = 'test_charge';
		$testTodo[] = 'test_services';
		$testTodo[] = 'test_services_deported';
		$testTodo[] = 'test_mail';
		$testTodo[] = 'test_disque';
		$testTodo[] = 'test_disque_distant';
		//$testTodo[] = 'test_kalirep_internet';
		$testTodo[] = 'test_cups_jobs';
		$testTodo[] = 'test_conf_files';
		break;
	case "kalirep":
		$testTodo[] = 'test_services';
		$testTodo[] = 'test_charge';
		$testTodo[] = 'test_disque';
		$testTodo[] = 'test_disque_distant';
		$restriction_service = Array("mysql");
		break;
	case "kalires":
	case "kalimodem":
		$testTodo[] = 'test_services';
		$testTodo[] = 'test_charge';
		$testTodo[] = 'test_disque';
		$testTodo[] = 'test_disque_distant';
		$restriction_service = Array("apache2");
		break;
	default:
		if($mode != '' && function_exists($mode)) {
			$testTodo[] = $mode;
		} else {
			die('ERROR : no mode defined');
		}
		break;
}

#
# CRONTAB 
#
# Check du serveur
# */5 * * * * root /usr/bin/php /var/www/kalilab/scripts/checkServeur.php 2>&1 >>/var/log/checkServeur.log
/* On attends pour éviter les pb */
/* Entre 3h et 4h30 on ne le lance pas */
$heure = (int)date('H'); $minute = (int)date('i');
if ($heure==3 || ($heure==4 && $minute<=30)) {
	die("$heure:$minute, en attente : non lancé.");
}

$adresseCC = "";
if(isset($_SERVER['argv']) && is_array($_SERVER['argv']) && isset($_SERVER['argv'][0]) && isset($_SERVER['argv'][1]) && strpos($_SERVER['argv'][1],"@") !== false) {
	$adresseCC = $_SERVER['argv'][1];
}

if (!function_exists('_s')) {
	function _s($txt) { return $txt; }
}

$baseDir = dirname(__FILE__) . "/..";
//include_once ($baseDir . "/include/lib.inc.php"); lib.inc normalement pas nécessaire (AS)
include_once ($baseDir . "/include/lib.serverStatus.inc.php");
include_once ($baseDir . "/include/lib.sysInfo.inc.php");
include_once ($baseDir . "/include/class.phpmailer.php");

if (!function_exists('klog')) { 
	function klog($dummy, $log) {
		echo $log . "\n";
	}
}
if (!function_exists('utf8_array')) {
	function utf8_array($array) {
		return is_array($array) ? array_map('utf8_array', $array) : utf8_encode($array);
	}
}

function exec_log_cmd($cmd, &$log) {
	$log .= "Exécution de la commande : '$cmd'\n";
	exec($cmd, $out, $ret);
	foreach($out as $row) {
		$log.= "output: " . $row . "\n";
	}
	$log .= "return: $ret\n";
	return $ret;
}


/*************************************/
/* Vérification des services (ports) */
/*************************************/
function test_services() {
	global $SERVER_SERVICES;
	global $SERVER_TYPE;
	global $restriction_service;

	if(is_array($restriction_service)) {
		$serviceToCheck = Array();
		foreach($restriction_service as $serviceRestricted) {
			$serviceToCheck[$serviceRestricted] = $SERVER_SERVICES[$SERVER_TYPE][$serviceRestricted];
		}
	} else {
		$serviceToCheck = $SERVER_SERVICES[$SERVER_TYPE];
	}
	
	$log_json = Array();
	$log_json["nom"] = "-- Tests des services (2) -------------";
	$log_services = "\n".$log_json["nom"]."\n";
	$problem_service = CHECKSERVEUR_OK;
	
	foreach($serviceToCheck as $name=>$data) {
		
		$log_json["process"][$name]["type"] = "nonCritique";
		$log_json["process"][$name]["etat"] = "";
		$process = $data['process'];
		$required = ($data['critical']==true);
		$pid = 0;
		
		$log_service = "$name, ";
		if ($required) {
			$log_service .= "critique, ";
			$log_json["process"][$name]["type"] = "critique";
		}
		if (service_installed($name)) {
			$log_service .= "installé, ";
			$log_json["process"][$name]["etat"] = "installe";
			
			/* Si on a un fichier PID */
			if ($data['pidfile'] != "") {
				if (file_exists($data['pidfile'])) {
					/* Le fichier PID existe */
					$log_service .= "pid : " . service_getpid($name).", ";
					if ($data['port'] > 0) {
						/* Test du port */
						$log_service .= "port : ".$data['port'].", ";
						$fp = fsockopen("127.0.0.1", $data['port'], $errno, $errstr, 30);
						if (!$fp) {
							$log_service .= "$errstr, ALERTE: REDEMARRAGE DU SERVICE";
							service_do($name, "restart", $output);
							print_output($output, $log_service);
							$problem_service = CHECKSERVEUR_ERROR;
							$log_json["process"][$name]["action"] = "restart";
							$log_json["process"][$name]["etat"] = "nonLanceNOK";
						} else {
							$log_json["process"][$name]["etat"] = "lanceOK";
							$log_service .= "Test de connection réussi.";
							fclose($fp);
						}
					} else {
						$log_service .= "OK.";
						$log_json["process"][$name]["etat"] = "lanceOK";
					}
				} else {
					/* Le fichier PID n'existe pas */
					if ($required == 1) {
						$log_service .= "non lancé, ALERTE: DEMARRAGE DU SERVICE";
						// On démarre le service
						$output = Array();
						service_do($name, "start", $output);
						print_output($output, $log_service);
						$problem_service = CHECKSERVEUR_ERROR;
						$log_json["process"][$name]["action"] = "start";
						$log_json["process"][$name]["etat"] = "nonLanceNOK";
					} else {
						$log_service .= "non lancé : OK.";
						$log_json["process"][$name]["etat"] = "nonLanceOK";
					}	
				}
			} else {
				/* Sans fichier PID */
				if (($pid=service_running($name))>0 || ($name=='firewall' && service_firewall_running())) {
					$log_service .= "lancé : OK.";
					$log_json["process"][$name]["etat"] = "lanceOK";
				} else {
					if ($required == 1) {
						$log_service .= "non lancé, ALERTE: DEMARRAGE DU SERVICE";
						// On démarre le service
						$output = Array();
						service_do($name, "start", $output);
						print_output($output, $log_service);
						$problem_service = CHECKSERVEUR_ERROR;
						$log_json["process"][$name]["action"] = "start2";
						$log_json["process"][$name]["etat"] = "nonLanceNOK";
					} else {
						$log_service .= "non lancé : OK.";
						$log_json["process"][$name]["etat"] = "nonLanceOK";
					}
				}
			}
			
		} else {
			if ($required) {
				//alternative present ?
				if($data["alternative"] !== "" && service_installed($data["alternative"])) {
					$log_service .= "non installé : OK., service alternatif : ".$data["alternative"]." installé.";
					$log_json["process"][$name]["etat"] = "nonInstalleOK";	
				} else {
					$log_service .= "non installé : NOK.";
					$log_json["process"][$name]["etat"] = "nonInstalleNOK";
					$problem_service = CHECKSERVEUR_ERROR;
				}
			} else {
				$log_service .= "non installé : OK.";
				$log_json["process"][$name]["etat"] = "nonInstalleOK";
			}
		}
		$log_service .= "\n";
		$log_json["process"][$name]["msg"] = $log_service;
		$log_services .= $log_service;
	}
	return Array($log_services, $log_json, $problem_service);
}

/*************************************/
/* Vérification des services déportés(ports) */
/*************************************/
function test_services_deported() {
	global $SERVER_SERVICES_DEPORTED,$SERVER_TYPE,$conf;
	
	$log_json = Array();
	$log_json["nom"] = "-- "._s("Tests des services déportés")."(2) -------------";
	$log_services = "\n".$log_json["nom"]."\n";
	$problem_service = CHECKSERVEUR_OK;
		
	foreach($SERVER_SERVICES_DEPORTED[$SERVER_TYPE] as $name=>$data) {
		$log_json["process"][$name]["type"] = "nonCritique";
		$log_json["process"][$name]["etat"] = "";
		$required = ($data['critical']==true);
		
		$log_service = "$name, ";
		if ($required) {
			$log_service .= "critique, ";
			$log_json["process"][$name]["type"] = "critique";
		}
		
		/* Test service accessible */
		$fp = fsockopen($conf['serveur'], $data['port'], $errno, $errstr, 30);
		if (!$fp) {
			$problem_service = CHECKSERVEUR_ERROR;
			$log_json["process"][$name]["etat"] = "nonLanceNOK";
		}  else {
			$log_json["process"][$name]["etat"] = "lanceOK";
			$log_service .= _s("Test de connection réussi.");
			fclose($fp);
		}
		
		$log_service .= "\n";
		$log_json["process"][$name]["msg"] = $log_service;
		$log_services .= $log_service;
	}
	return Array($log_services, $log_json, $problem_service);
}


/*********/
/* Mails */
/*********/
function test_mail() {
	global $SERVER_MAXMAILTIME;
	$fetchmailSleep = 30;
	$minutes = 30;
	$temporisation = 60;
	$killTime = 120;
	$service = "fetchmail";
	$log_json = Array();
	$log_json["nom"] = "-- Recherche de mails -------------";
	$log_mail = "\n".$log_json["nom"]."\n";
	ob_start();
	$lastMail = date("d-m-Y H:i:s", getLastMail());
	$log_lastMail = ob_get_contents();
	ob_end_clean();
	if($log_lastMail !== "") {
		$log_mail .= $log_lastMail;
		$problem_mail = CHECKSERVEUR_ERROR;
	} else {
		$log_mail .= "Dernier mail recu le : " . $lastMail . "\n";
		$log_json["lastMail"] = $lastMail;
		$problem_mail = CHECKSERVEUR_OK;
		if (!checkLastMail()) {
			$log_mail .= "Le dernier mail reçu a plus de ".$SERVER_MAXMAILTIME." heures.\n";
			$output=Array();
			$retourCheckPid = checkPidTime(Array("cmd" => $service,"minutes" => $minutes, "temporisation" => $temporisation, "timeBeforeKill" => $killTime, "log" => true));
			if($retourCheckPid["code"] == "old") {
				$log_mail = "ERREUR : La recherche mail ne fonctionne pas - Processus en cours depuis ".$retourCheckPid["timeHR"]." !";
				$problem_mail = CHECKSERVEUR_ERROR;
			} else if(in_array($retourCheckPid["code"],Array("new","empty1","empty2")) && $retourCheckPid["avertTime"] != 0) {
				$log_mail = "RETABLISSEMENT : La recherche mail est repartie - Alerte transmise la dernière fois le ".$retourCheckPid["avertTimeHR"];
				$problem_mail = CHECKSERVEUR_RETABLISSEMENT;
				avertFileExists(Array("name" => $service, "deleteFile" => true));
			} else if($retourCheckPid["code"] == "oldTempo") {
				$log_mail = "INPROGRESS : RechercheMail en cours...temporisation de l'alerte";
				$problem_mail = CHECKSERVEUR_INPROGRESS;
			} else if(!in_array($retourCheckPid["code"],Array("new","current","oldTempo"))) { //si fetchmail n'est pas en cours

				$return = checkFetchmail($output);
				$nb = 0;
				while ((int)$return > 1 && $nb<3) {
					if ((int)$return == 8) {
						// Si fetchmail est déja lancé
						$log_mail .= "Fetchmail est déjà lancé ($return), on réessaye dans $fetchmailSleep secondes.\n";

					} else {
						$log_mail .= "Fetchmail a retourné une erreur $return, relance dans $fetchmailSleep secondes.\n";
					}
					sleep($fetchmailSleep);
					$return=checkFetchmail($output);
					$nb++;
				}

				if($return > 1) {
					$log_mail .= "ERREUR: La recherche de mail ne fonctionne pas ($return).\n";
					print_output($output, $log_mail);
					$problem_mail = CHECKSERVEUR_ERROR;
				} else {
					$log_mail .= "La recherche de mail fonctionne. ($return)\n";
					avertFileExists(Array("name" => $service, "deleteFile" => true));
				}
			} else {
				$log_mail .= "La recherche de mail fonctionne, tâche en cours depuis ".$retourCheckPid["timeHR"].".\n";
				avertFileExists(Array("name" => $service, "deleteFile" => true));
			}
		} else {
			$log_mail .= "La recherche de mail fonctionne.\n";
			avertFileExists(Array("name" => $service, "deleteFile" => true));
		}
	}
	$log_json["msg"] = $log_mail;
	return Array($log_mail, $log_json, $problem_mail);
}

/*****************/
/* Espace Disque */
/*****************/
function test_disque() {
	global $SERVER_MINSPACEPCT;
	
	$log_disque = "";
	$log_json = Array();
	$log_json["nom"] = "-- Espace Disque -------------";
	$log_disque = "\n".$log_json["nom"]."\n";
	$problem_disque = CHECKSERVEUR_OK;
	$lesPart = checkDiskSpace();
	$erreur=0;

	foreach($lesPart as $part=>$data) {
		// cas des CDROM à ignorer
		if(strpos(str_pad($part,10, ' '), "cdrom") === FALSE) {
			$log_disque .= str_pad($part,10, ' ').str_pad($data['occupe'],10, ' ').'/ '.str_pad(	$data['total'],10,' ').' ('.$data['pct']."%)\n";
			$log_json["partition"][$part] = $data;
			if ($erreur == 0) $erreur = $data['erreur'];
		}
	}
	if ($erreur == 1) {
		$log_json["msg"] = "ALERT : Espace disque faible pour au moins une partition !!";
		$log_json["seuil"] = $SERVER_MINSPACEPCT;
		$problem_disque = CHECKSERVEUR_ERROR;
	}
	return Array($log_disque, $log_json, $problem_disque);
}

/*****************/
/* Espace Disque Distant */
/*****************/
function test_disque_distant() {
	global $SERVER_DIST_MINSPACEPCT;
	
	$log_disque = "";
	$log_json = Array();
	$log_json["nom"] = "-- Espace Disque Distant -------------";
	$log_disque = "\n".$log_json["nom"]."\n";
	$problem_disque = false;
	$lesPart = checkDiskSpaceDistant();
	$erreur=0;
	foreach($lesPart as $part=>$data) {
		$log_disque .= str_pad($data["serveurDistant"].":".$data["repertoireDistant"],70).str_pad($part,40, ' ').str_pad($data['occupe'],10, ' ').'/ '.str_pad(	$data['total'],10,' ').' ('.$data['pct']."%)\n";
		$log_json["partition"][$part] = $data;
		if ($erreur == 0) $erreur = $data['erreur'];
	}
	if ($erreur == 1) {
		$log_json["msg"] = "ALERT : Espace disque faible pour au moins un volume distant !!";
		$log_json["seuil"] = $SERVER_DIST_MINSPACEPCT;
		$problem_disque = CHECKSERVEUR_ERROR;
	}
	return Array($log_disque, $log_json, $problem_disque);
}

/*****************/
/* KaliRep acces internet */
/*****************/
function test_kalirep_internet() {
	$log_kalirep_internet = "";
	$log_json = Array();
	$log_json["nom"] = "-- KaliRep Ping -------------";
	$log_kalirep_internet = "\n".$log_json["nom"]."\n";
	$problem_kalirep_ping = false;
	
	global $conf;
	$user = "root";
	$port = "22";
	$wan = "8.8.8.8";
    
    if (!is_array($conf["serveurReplication"])) $listeServeurs = Array($conf["serveurReplication"]);
    else $listeServeurs = $conf["serveurReplication"];
	
    foreach ($listeServeurs as $serveur) {
    	if($serveur != '') {
    
    		exec("ssh -T -o StrictHostKeyChecking=no -p ".$port." ".$user."@".$serveur." <<**
    ping -c5 ".$wan."
    **",$output,$res);
    
    		
    		if(preg_match("/[1-5] packets transmitted, [1-5] received/",implode(' ',$output))) {
    			$erreur = 0;
    		} else {
    			$erreur = $output;
    			$log_kalirep_internet .= "ALERT : Le serveur KaliRep '$serveur' n'a pas accès à Internet !";
    			$log_json["msg"] = "ALERT : Le serveur KaliRep '$serveur' n'a pas accès à Internet !";
    			$problem_kalirep_ping = CHECKSERVEUR_ERROR;
    		}
    		
    	}
    }
	
	return Array($log_kalirep_internet, $log_json, $problem_kalirep_ping);
}

/*****************/
/* CUPS : jobs en cours  */
/*****************/
function test_cups_jobs() {

	// TEST DES SOCKET://
	$nbJobsMaxPerIp = 20;
	$log_cups_jobs = "";
	$log_json = Array();
	$log_json["nom"] = "-- CUPS Jobs -------------";
	$log_cups_jobs = "\n".$log_json["nom"]."\n";
	$problem_cups_jobs = false;
	
	$error_cups = Array();
	$listIp = Array();
	
	$output = Array();
	exec("/bin/ps axf | grep 'socket' | grep 'job'",$output,$res);
	
	foreach($output as $line) {
		if(preg_match("/^.* socket\:\/\/([0-9\.]+) .*$/",$line,$reg)) {
			$listIp[$reg[1]]++;
		}
	}

	foreach($listIp as $ip => $nb) {
		if($nb > $nbJobsMaxPerIp) {
			$error_cups["SOCKET OUVERT > ".$nbJobsMaxPerIp][] = $ip." (".$nb.")";
		}
	}

	// TEST DES JOBS PAR LPSTAT
	$nbJobsMaxPerPrinter = 400;
	$output = Array();
	exec("/bin/cat /etc/cups/printers.conf | grep '<Printer '",$output,$res);
	$printers = Array();
	foreach($output as $line) {
		$printer = substr($line,9,-1);
		if($printer != '') {
			$printers[] = $printer;
		}
	}

	foreach($printers as $printer) {
		$output = Array();
		exec("/usr/bin/lpstat -o '".$printer."'",$output,$res);
		$nbJobs = count($output);
		if($nbJobs > $nbJobsMaxPerPrinter) {
			$error_cups["JOBS EN COURS > ".$nbJobsMaxPerPrinter][] = $printer." (".$nbJobs.")";
		}
	}

	if(count($error_cups) > 0) {
		foreach($error_cups as $type => $tabError) {
			$erreur .= "<br />\r\n".$type." : ".implode(", ",$tabError);
		}
		$log_cups_jobs .= "ALERT : Des jobs CUPS restent en attente : ".$erreur;
		$log_json["msg"] = "ALERT : Des jobs CUPS restent en attente : ".$erreur;
		$problem_cups_jobs = CHECKSERVEUR_ERROR;
	}
	
	return Array($log_cups_jobs, $log_json, $problem_cups_jobs);
}

/**
 * Vérification des fichiers de conf
 */
function test_conf_files() {

	$log_json = Array();
	$log_json["nom"] = "-- CONF files -------------";
	$log_conf_files = "\n".$log_json["nom"]."\n";
	$problem_conf_files = false;
	$erreur = "";

	$files = Array();
	$files[] = '/etc/apache2/apache2.conf';
	$files[] = '/etc/apache2/ports.conf';
	$files[] = '/etc/apache2/httpd.conf';
	$files[] = '/etc/apache2/sites-available/*';
	$files[] = '/etc/apache2/sites-enabled/*';
	$files[] = '/etc/mysql/*';
	$files[] = '/etc/mysql/conf.d/*';
	$files[] = '/etc/samba/smb.conf';
	$files[] = '/etc/cups/cupsd.conf';
	$files[] = '/etc/openvpn/*';
	$files[] = '/etc/vsftpd.conf';
	$files[] = '/etc/vsftpd/user_list';
	$files[] = '/etc/netika/*';
	$files[] = '/etc/netika/backup/*';
	$files[] = '/etc/netika/backup/dist.d/*';
	$files[] = '/etc/fetchmail*';
	$files[] = '/etc/dovecot/dovecot.conf';
	$files[] = '/etc/postfix/main.cf';
	$files[] = '/etc/postfix/master.cf';
	$files[] = '/etc/php5/apache2/php.ini';
	$files[] = '/etc/php5/cli/php.ini';
	$files[] = '/etc/crontab';
	$files[] = '/etc/cron.d/*';
	$files[] = '/etc/cron.daily/*';
	$files[] = '/etc/cron.hourly/*';
	$files[] = '/etc/cron.weekly/*';
	$files[] = '/etc/cron.monthly/*';
	$files[] = '/etc/hosts';
	$files[] = '/etc/network/interfaces';
	$files[] = '/etc/network/if-up.d/*';
	$files[] = '/etc/ntp.conf';
	$files[] = '/etc/passwd';
	$files[] = '/etc/group';
	$files[] = '/etc/rsyncd.conf';
	$files[] = '/etc/ssh/sshd_config';
	$files[] = '/etc/ssh/ssh_config';
	$files[] = '/root/.ssh/authorized_keys';
	$files[] = '/etc/sudoers';
	$files[] = '/etc/sudoers.d/*';
	
	$filesExclure = Array();
	$filesExclure['/etc/cron.d/*'][] = 'kalilab';
	$filesExclure['/etc/cron.d/*'][] = 'demarrageConnexion*';
	$filesExclure['/etc/cron.d/*'][] = 'impressionTacheFondOMR';
	
	$dirSave = '/home/kalilab/confSave';
	$found = Array();
	@mkdir($dirSave);
	foreach($files as $file) {
		$strExclure = '';
		if(is_array($filesExclure[$file])) {
			$strExclure = " ! -name '".implode("' ! -name '",$filesExclure[$file])."' ";
		}
		$tab = Array();
		@exec("/usr/bin/find ".$file." -maxdepth 0 -type f  ! -name 'README' ".$strExclure."",$tab);
		foreach($tab as $f) {
			$found[] = $f;
			if(!file_exists($dirSave.$f)) {
				@mkdir(dirname($dirSave.$f), 0755, true);
				copy($f,$dirSave.$f);
			} else {
				if(md5_file($f) != md5_file($dirSave.$f)) {
					exec("/usr/bin/diff -b -I '^#' -I '^ #' ".$f." ".$dirSave.$f."",$tab2);
					if(!empty($tab2) > 0) {
						$erreur .= "<br />\r\n<b>".$f."</b> : <br />\r\n  ".implode("<br />\r\n  ",$tab2);
						$problem_conf_files = CHECKSERVEUR_ERROR;
					}
					@unlink($dirSave.$f);
					copy($f,$dirSave.$f);
				}
			}
		}
	}
	$tab = Array();
	@exec("/usr/bin/find $dirSave -maxdepth 4 -type f",$tab);
	foreach($tab as $f) {
		$f = str_replace($dirSave,'',$f);
		if(!in_array($f,$found)) {
			$erreur .= "<br />\r\n<b>".$f."</b> : <br />\r\n  Fichier supprimé";
			$problem_conf_files = CHECKSERVEUR_ERROR;
			@unlink($dirSave.$f);
		}
	}	
	
	if($problem_conf_files) {
		$log_conf_files .= "ALERT : Des fichiers de CONF ont été modifiés : ".$erreur;
		$log_json["msg"] = "ALERT : Des fichiers de CONF ont été modifiés : ".$erreur;
	}
	return Array($log_conf_files, $log_json, $problem_conf_files);
}

/**
 * Vérification de la charge
 */
function test_charge() {

	$log_charge = "";
	$log_json = Array();
	$log_json["nom"] = "-- Charge du système -------------";
	$log_charge = "\n".$log_json["nom"]."\n";
	$problem_charge = CHECKSERVEUR_OK;
	
	// Nombre de cpus
	$s = new sysInfo();
	$c = $s->cpu_info();
	$nombreCpus = max($c['cpus'],1);
	$log_charge .= "Nombre de processeurs: $nombreCpus\n";
	$log_json["cpu"] = $nombreCpus;
	
	// Seuil charge
	$coefficient = (double) (1 / $nombreCpus) + 1.5;
	$seuil = $nombreCpus*$coefficient;
	$log_charge .= "Seuil de charge ((1/nbProcesseurs + 1.5) * nbProcesseurs): $seuil\n";
	$log_json["seuil"] = $seuil;
	// Charge actuelle
	$charge = (double) exec('cat /proc/loadavg | awk \'{print $2}\'');
	$log_charge .= "Charge actuelle: $charge\n";
	$log_json["charge"] = $charge;
	// Erreur
	if ($seuil < $charge) {
		$log_charge .= "Problème: Charge du serveur très importante (".$charge.") !\n";
		$log_charge .= "\n";
		$problem_charge = CHECKSERVEUR_ERROR;
	}
	$log_charge .= "\n";
	
	// RAM
	$seuil_ram = 95;
	$c = $s->memory();
	$pc = 0;
	if($c['ram']['total'] > 0) {
		$pc = ($c['ram']['total'] - $c['ram']['free'] - $c['ram']['buffers'] - $c['ram']['cached']) * 100 / $c['ram']['total'];
	}
	if($pc >= $seuil_ram) {
		$log_charge .= "Problème: RAM utilisée à plus de ".$seuil_ram."% (".$pc.") !\n";
		$log_charge .= "\n";
		$problem_charge = CHECKSERVEUR_ERROR;
	}
	// SWAP
	if($c['swap']['percent'] >= $seuil_ram) {
		$log_charge .= "Problème: SWAP utilisé à plus de ".$seuil_ram."% (".$c['swap']['percent'].") !\n";
		$log_charge .= "\n";
		$problem_charge = CHECKSERVEUR_ERROR;
	}
	
	if($problem_charge == CHECKSERVEUR_ERROR) {
		$processEnCours = "";
		exec_log_cmd("ps faux", $processEnCours);
		$log_charge .= $processEnCours;
		$log_json["process"] = $processEnCours;
	}
	
	return Array($log_charge, $log_json, $problem_charge);
}

// Mail d'erreur
$mail_errors = "recherchemail@netika.net";
$problem = false;

$tabContent = Array();
$tabContent["info"]["Licence"] = $licence["numero"];
$tabContent["info"]["Host"] = $sysInfo['hostname'];
$tabContent["info"]["Ip"] = $sysInfo['ip'];
$tabContent["info"]["Date"] = date('d-m-Y H:i:s');
$tabContent["info"]["Distribution"] = $sysInfo['distrib'];
$tabContent["info"]["Type de serveur"] = $SERVER_TYPE;
$tabContent["info"]["Application"] = KL_APP_NAME;
$tabContent["info"]["Version des fichiers"] = get_kl_version();
$tabContent["info"]["Uptime"] = trim(`uptime`);

$log .= "== TESTS DU SERVEUR =====================\n";
$log .= "Host : ".$sysInfo['hostname']."\n";
$log .= "Ip : ".$sysInfo['ip']."\n";
$log .= "Date : ".date('d-m-Y H:i:s')."\n";
$log .= "Distribution : ".$sysInfo['distrib']."\n";
$log .= "Type de serveur : $SERVER_TYPE\n";
$log .= "Mode : $mode\n";
$log .= "Application : ".KL_APP_NAME."\n";
$log .= "Mode : ".$mode."\n";
$log .= "Version des fichiers : ".get_kl_version()."\n";
$log .= trim(`uptime`). "\n";
// Fonctions de test
$test_functions = Array(
	'test_charge' 			=> Array("temporisation" => 60),
	'test_services' 			=> Array("temporisation" => 60, "SHA1Pid" => true),
	'test_services_deported' 	=> Array("temporisation" => 60),
	'test_mail' 				=> Array("temporisation" => 60),
	'test_disque' 				=> Array("temporisation" => 60),
	'test_disque_distant' 		=> Array("temporisation" => 60),
	'test_kalirep_internet' 	=> Array("temporisation" => 60),
	'test_cups_jobs' 			=> Array("temporisation" => 60),
	'test_conf_files' 			=> Array("temporisation" => 60)
);

// Lancement des test
$logTab = Array();
$tabProblem = Array();
foreach($test_functions as $function => $functionParam) {
	if(!in_array($function,$testTodo)) continue;
	list($logtmp,$logTab,$problemtmp) = $function();
	if (in_array($problemtmp,Array(CHECKSERVEUR_ERROR,CHECKSERVEUR_RETABLISSEMENT))) {
		$log .= $logtmp;
		$tabContent["services"][$function] = Array("code" => $problemtmp, "message" => $logTab);
	}
	switch($problemtmp) { //a la détection, on crée un fichier d'avertissement par fonction.
		case CHECKSERVEUR_ERROR :
			makePidFile(Array("name" => $function, "data" => $functionParam, "dataSHA1" => $logtmp));
			$dataTemporisation = checkPidTime(Array("cmd" => $function, "name" => $function, "fncPerso" => "delayAvert", "log" => true, "minutes" => $functionParam["minutes"], "temporisation" => $functionParam["temporisation"], "SHA1Pid" => $functionParam["SHA1Pid"], "dataSHA1" => $logtmp, "deleteOnNew" => false));
			if($dataTemporisation["code"] == "old" || $dataTemporisation["code"] == "new") {
				$problem = true;
				$tabProblem[] = $function;
			}
		break;
		case CHECKSERVEUR_OK : //on transmet un message si on est rétabli
			$avertissement=pidFileExists(Array("name" => $function, "deleteFile" => true));
			if($avertissement["present"]) {
				avertFileExists(Array("name" => $function, "deleteFile" => true));
				$log .= "Rétablissement du service : ".$function."\n";
				$tabContent["services"][$function] = Array("code" => CHECKSERVEUR_RETABLISSEMENT, "message" => sprintf("rétablissement - date de création de l'avertissement : %s",$avertissement["time"]));
				$problem = true;
			}
		break;
		case CHECKSERVEUR_RETABLISSEMENT :
			avertFileExists(Array("name" => $function, "deleteFile" => true));
			pidFileExists(Array("name" => $function, "deleteFile" => true));
			$log .= "Rétablissement du service : ".$function."\n";
			$tabContent["services"][$function] = Array("code" => CHECKSERVEUR_RETABLISSEMENT, "message" => "rétablissement du service");
			$problem = true;
		break;
	}
}

$filtrage = Array("Licence","Date","Application");
$tabContent = utf8_array($tabContent);
echo $log;
if ($problem && $conf['debug'] == false) {
	echo "\n";
	$str = "Un probleme a été détecté, envoi du mail a ".$mail_errors.($adresseCC != ""? " et ".$adresseCC:"");
	$nb = strlen($str) + 10;
	for($i=0;$i<$nb;$i++) echo "-";	echo "\n| !! " . $str . " !! |\n";	for($i=0;$i<$nb;$i++) echo "-";	echo "\n";

	$mail = new phpmailer();
	$mail->Encoding = "base64";
	$mail->From     = $conf['email'];
	$mail->FromName = $licence['detenteur'][0];
	$mail->Host     = $conf['smtp'];
	$mail->Mailer   = "smtp";
	$mail->AddAddress($mail_errors,"NETIKA");
	if($adresseCC != "")	$mail->AddCC($adresseCC,$adresseCC);
	$mail->Subject  =  "ALERT PROBLEME SERVEUR [NG][".$mode."] ". $sysInfo['hostname']." : ".implode(", ",$tabProblem)."";
	$mail->Body	    = $log;
	$mail->IsHTML(true);
	if(($json = json_encode($tabContent)) !== false) {
		$mail->AltBody = $json;
	} else {
		$mail->AltBody = json_encode(Array("erreur encodage JSON"));
		echo "erreur encodage JSON";
	}
	foreach($tabContent["info"] as $tKey => $tValue) {
		if(in_array($tKey,$filtrage)) {
			$mail->AddCustomHeader("X-NETIKA-".str_replace(" ","_",strtoupper($tKey)).":".preg_replace("/\R/","",$tValue));
		}
	}
	$mail->AddCustomHeader("X-NETIKA-TYPE:".pathinfo($_SERVER["SCRIPT_FILENAME"],PATHINFO_FILENAME));
	
	if(!$mail->Send()) {
		echo "Erreur d'envoi du mail : ".$mail->ErrorInfo."\n";
	}
	$mail->ClearAddresses();
	$mail->ClearAttachments();
} else {
	echo "\nAucun mail a envoyer.\n";
}

