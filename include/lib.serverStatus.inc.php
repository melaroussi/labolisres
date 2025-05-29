<?php
include_once ($conf['baseDir']. "/include/lib.sysInfo.inc.php");
if(!defined('KL_APP_NAME')) define('KL_APP_NAME','KaliLab');

// chmod
chmod($conf['baseDir'] . "/scripts/serverctl.sh", 0755);

// Script CTL (a mettre dans le sudoers)
if (posix_getuid() == 0){
	$isSudo = "";
} else {
	$isSudo = "/usr/bin/sudo ";
}

$SERVER_CTL = $isSudo . $conf['baseDir'] . "/scripts/serverctl.sh";

// Temps max pour la reception de mails
$SERVER_MAXMAILTIME = 12;

// Espace disque minimum pour l'alerte (en %)
$SERVER_MINSPACEPCT = 95;
$SERVER_DIST_MINSPACEPCT = 95;

// Taille de fichier pour déclencher la recherche checkBigFiles
$SERVER_DIR = "/";
$SERVER_DIR_KALILAB = "/var/www/kalilab/";
$SERVER_FILESIZE = "500M";

// Profondeur de recherche du nombre de fichiers par repertoire
$SERVER_DIRDEPTH = 10;

// Espace disque minimum pour l'alerte (en Mo) s'il y a moins de $SERVER_MINPCDISK %
//$SERVER_MINDISKSPACE = 500;

 
// Récap du systeme
function recapSys() {
    $sysInfo = new sysInfo();
    $results['ip'] = $sysInfo->ip_addr();
    $results['distrib'] = $sysInfo->distro();
    $results['uptime'] = $sysInfo->uptime();
    $results['hostname'] = $sysInfo->chostname();
    $tmp = $sysInfo->cpu_info();
    $results['cpu'] = $tmp['model'];
    $tmp = $sysInfo->memory();
    $results['ram'] = $tmp['ram']['total'];
    $tmp = $sysInfo->filesystems();
    $results['filesystems'] = $tmp;
    return $results;
}

$sysInfo = recapSys();

// TYPE DE SYSTEME
$SERVER_TYPE = 'fedora10';
if (preg_match('/^[Dd]ebian/',$sysInfo['distrib'])) $SERVER_TYPE = 'debian5';
if (preg_match('/^[Uu]buntu/',$sysInfo['distrib'])) $SERVER_TYPE = 'debian5';

if (file_exists("/etc/fedora-release")) {
	$SERVER_TYPE = 'fedora10';
} else if (file_exists("/etc/centos-release")) {
	$SERVER_TYPE = 'redhat';
} else if (file_exists("/etc/redhat-release")) {
	$SERVER_TYPE = 'redhat';
}

/* Apache user */
if ($SERVER_TYPE == 'debian5') $APACHE_USER = 'www-data';
else $APACHE_USER = 'apache';

// SERVICES
$SERVER_SERVICES = Array();
$SERVER_SERVICES['debian5'] = Array(
	"cron" => Array('init'=>"/etc/init.d/cron", 'exec'=>"", 'process'=>"/usr/sbin/cron", 'critical'=>true, 'pidfile'=>'/var/run/crond.pid', 'port'=>0, 'description'=>"Tâches automatiques (crontab)"),
	"apache2" => Array('init'=>"/etc/init.d/apache2", 'exec'=>"", 'process'=>"/usr/sbin/apache2", 'critical'=>true, 'pidfile'=>'/var/run/apache2.pid', 'port'=>80, 'description'=>"Serveur d'application (apache)"),
	"mysql" => Array('init'=>"/etc/init.d/mysql", 'exec'=>"", 'process'=>"/usr/sbin/mysqld", 'critical'=>true, 'pidfile'=>'/var/run/mysqld/mysqld.pid', 'port'=>3306, 'description'=>"Base de données (mysql)"),
	"dovecot" => Array('init'=>"/etc/init.d/dovecot", 'exec'=>"", 'process'=>"/usr/sbin/dovecot", 'critical'=>true, 'pidfile'=>'/var/run/dovecot/master.pid', 'port'=>143, 'description'=>"Messagerie locale (dovecot)"),
	"postfix" => Array('init'=>"/etc/init.d/postfix", 'exec'=>"", 'process'=>"/usr/lib/postfix/master", 'critical'=>true, 'pidfile'=>'/var/spool/postfix/pid/master.pid', 'port'=>25, 'description'=>"Envois de messages (postfix)"),
	"cups" => Array('init'=>"/etc/init.d/cups", 'exec'=>"", 'process'=>"/usr/sbin/cupsd", 'critical'=>check_app_name('kalisil'), 'pidfile'=>'/var/run/cups/cupsd.pid', 'port'=>631, 'description'=>"Serveur d'impression (cups)"),
	"ntp" => Array('init'=>"/etc/init.d/ntp", 'exec'=>"", 'process'=>"/usr/sbin/ntpd", 'critical'=>true, 'pidfile'=>'', 'port'=>0, 'description'=>"Serveur de temps internet (ntp)"),
	"proftpd" => Array('init'=>"/etc/init.d/proftpd", 'exec'=>"/usr/sbin/proftpd", 'process'=>"proftpd:", 'critical'=>check_app_name('kalisil'), 'pidfile'=>'/var/run/proftpd.pid', 'port'=>21, 'description'=>"Serveur FTP (proftpd)", 'alternative' => "vsftpd"),
	"vsftpd" => Array('init'=>"/etc/init.d/vsftpd", 'exec'=>"/usr/sbin/vsftpd", 'process'=>"/usr/sbin/vsftpd", 'critical'=>check_app_name('kalisil'), 'pidfile'=>'/var/run/vsftpd/vsftpd.pid', 'port'=>21, 'description'=>"Serveur FTP (vsftpd)", 'alternative' => "proftpd"),
	"firewall" => Array('init'=>"/etc/init.d/firewall", 'exec'=>"", 'process'=>"", 'critical'=>false, 'pidfile'=>'', 'port'=>0, 'description'=>"Pare-feu"),
);
ksort($SERVER_SERVICES['debian5']);
$SERVER_SERVICES['fedora10'] = Array(
	"cron" => Array('init'=>"/etc/init.d/crond", 'exec'=>"", 'process'=>"crond", 'critical'=>true, 'pidfile'=>'/var/run/crond.pid', 'port'=>0, 'description'=>"Tâches automatiques (crontab)"),
	"apache2" => Array('init'=>"/etc/init.d/httpd", 'exec'=>"", 'process'=>"/usr/sbin/httpd", 'critical'=>true, 'pidfile'=>'/var/run/httpd.pid', 'port'=>80, 'description'=>"Serveur d'application (apache)"),
	"mysql" => Array('init'=>"/etc/init.d/mysqld", 'exec'=>"", 'process'=>"/usr/libexec/mysqld", 'critical'=>true, 'pidfile'=>'/var/run/mysqld/mysqld.pid', 'port'=>3306, 'description'=>"Base de données (mysql)"),
	"dovecot" => Array('init'=>"/etc/init.d/dovecot", 'exec'=>"", 'process'=>"/usr/sbin/dovecot", 'critical'=>true, 'pidfile'=>'/var/run/dovecot/master.pid', 'port'=>143, 'description'=>"Messagerie locale (dovecot)"),
	"postfix" => Array('init'=>"/etc/init.d/postfix", 'exec'=>"", 'process'=>"/usr/libexec/postfix/master", 'critical'=>true, 'pidfile'=>'/var/spool/postfix/pid/master.pid', 'port'=>25, 'description'=>"Envois de messages (postfix)"),
	"cups" => Array('init'=>"/etc/init.d/cups", 'exec'=>"", 'process'=>"cupsd", 'critical'=>check_app_name('kalisil'), 'pidfile'=>'/var/run/cupsd.pid', 'port'=>631, 'description'=>"Serveur d'impression (cups)"),
	"ntp" => Array('init'=>"/etc/init.d/ntpd", 'exec'=>"", 'process'=>"ntpd", 'critical'=>true, 'pidfile'=>'', 'port'=>0, 'description'=>"Serveur de temps internet (ntp)"),
	"proftpd" => Array('init'=>"/etc/init.d/proftpd", 'exec'=>"/usr/sbin/proftpd", 'process'=>"proftpd:", 'critical'=>check_app_name('kalisil'), 'pidfile'=>'/var/run/proftpd.pid', 'port'=>21, 'description'=>"Serveur FTP (proftpd)", 'alternative' => "vsftpd"),
	"vsftpd" => Array('init'=>"/etc/init.d/vsftpd", 'exec'=>"/usr/sbin/vsftpd", 'process'=>"/usr/sbin/vsftpd", 'critical'=>check_app_name('kalisil'), 'pidfile'=>'/var/lock/subsys/vsftpd', 'port'=>21, 'description'=>"Serveur FTP (vsftpd)", 'alternative' => "proftpd"),
	"firewall" => Array('init'=>"/etc/init.d/firewall", 'exec'=>"", 'process'=>"", 'critical'=>false, 'pidfile'=>'', 'port'=>0, 'description'=>"Pare-feu"),
);
ksort($SERVER_SERVICES['fedora10']);
$SERVER_SERVICES['redhat'] = Array(
	"cron" => Array('init'=>"/etc/init.d/crond", 'exec'=>"", 'process'=>"crond", 'critical'=>true, 'pidfile'=>'/var/run/crond.pid', 'port'=>0, 'description'=>"Tâches automatiques (crontab)"),
	"apache2" => Array('init'=>"/etc/init.d/httpd", 'exec'=>"", 'process'=>"/usr/sbin/httpd", 'critical'=>true, 'pidfile'=>'/var/run/httpd/httpd.pid', 'port'=>80, 'description'=>"Serveur d'application (apache)"),
	"mysql" => Array('init'=>"/etc/init.d/mysqld", 'exec'=>"", 'process'=>"/usr/libexec/mysqld", 'critical'=>true, 'pidfile'=>'/var/run/mysqld/mysqld.pid', 'port'=>3306, 'description'=>"Base de données (mysql)"),
	"dovecot" => Array('init'=>"/etc/init.d/dovecot", 'exec'=>"", 'process'=>"/usr/sbin/dovecot", 'critical'=>true, 'pidfile'=>'/var/run/dovecot/master.pid', 'port'=>143, 'description'=>"Messagerie locale (dovecot)"),
	"postfix" => Array('init'=>"/etc/init.d/postfix", 'exec'=>"", 'process'=>"/usr/libexec/postfix/master", 'critical'=>true, 'pidfile'=>'/var/spool/postfix/pid/master.pid', 'port'=>25, 'description'=>"Envois de messages (postfix)"),
	"cups" => Array('init'=>"/etc/init.d/cups", 'exec'=>"", 'process'=>"cupsd", 'critical'=>check_app_name('kalisil'), 'pidfile'=>'/var/run/cupsd.pid', 'port'=>631, 'description'=>"Serveur d'impression (cups)"),
	"ntp" => Array('init'=>"/etc/init.d/ntpd", 'exec'=>"", 'process'=>"ntpd", 'critical'=>true, 'pidfile'=>'', 'port'=>0, 'description'=>"Serveur de temps internet (ntp)"),
	"proftpd" => Array('init'=>"/etc/init.d/proftpd", 'exec'=>"/usr/sbin/proftpd", 'process'=>"proftpd:", 'critical'=>check_app_name('kalisil'), 'pidfile'=>'/var/run/proftpd.pid', 'port'=>21, 'description'=>"Serveur FTP (proftpd)", 'alternative' => "vsftpd"),
	"vsftpd" => Array('init'=>"/etc/init.d/vsftpd", 'exec'=>"/usr/sbin/vsftpd", 'process'=>"/usr/sbin/vsftpd", 'critical'=>check_app_name('kalisil'), 'pidfile'=>'/var/lock/subsys/vsftpd', 'port'=>21, 'description'=>"Serveur FTP (vsftpd)", 'alternative' => "proftpd"),
	"firewall" => Array('init'=>"/etc/init.d/firewall", 'exec'=>"", 'process'=>"", 'critical'=>false, 'pidfile'=>'', 'port'=>0, 'description'=>"Pare-feu"),
);
ksort($SERVER_SERVICES['redhat']);

// Test si un service est installé
function service_installed($service) {
	global $SERVER_SERVICES, $SERVER_TYPE; 
	if (is_array($SERVER_SERVICES[$SERVER_TYPE][$service]) && file_exists($SERVER_SERVICES[$SERVER_TYPE][$service]['init'])) 
		if ($SERVER_SERVICES[$SERVER_TYPE][$service]['exec']!='') {
			if (file_exists($SERVER_SERVICES[$SERVER_TYPE][$service]['exec'])) return true;
			else return false;
		} else {
			return true;
		}
	else 
		return false;
}

// Test si un service est lancé
function service_running($service) {
	global $SERVER_SERVICES, $SERVER_TYPE;
	
	if (is_array($SERVER_SERVICES[$SERVER_TYPE][$service]) && $SERVER_SERVICES[$SERVER_TYPE][$service]['process']!='') {
		$process = $SERVER_SERVICES[$SERVER_TYPE][$service]['process'];
		// Recherche si le process est lancé
		$command = "/bin/ps axf | grep '$process' | grep -v 'grep' | awk '{print $1}'";
		exec($command, $output, $return);
		//echo $command;
		
		if ($return == 0) {
			return (int)$output[0];
		} else {
			return 0;
		}
	} else {
		return 0;
	}
}

// Récupération du PID
function service_getpid($service) {
	global $SERVER_SERVICES, $SERVER_TYPE;
	
	if (is_array($SERVER_SERVICES[$SERVER_TYPE][$service]) && $SERVER_SERVICES[$SERVER_TYPE][$service]['pidfile']!='') {
		$pidfile = $SERVER_SERVICES[$SERVER_TYPE][$service]['pidfile'];
		// Recherche si le process est lancé
		$command = "cat $pidfile";
		exec($command, $output, $return);
		//echo $command;
		
		if ($return == 0) {
			return (int)$output[0];
		} else {
			return 0;
		}
	} else {
		return 0;
	}	
}

// Test si le firewall est lancé
function service_firewall_running() {
	global $SERVER_SERVICES, $SERVER_TYPE, $SERVER_CTL;
	$command = $SERVER_CTL . " firewall running";
	exec($command, $output, $return);
	if ($return == 0) {
		return true;
	} else {
		return false;
	}
}

// Action sur un service
function service_do($service, $action, &$output) {
	global $SERVER_SERVICES, $SERVER_TYPE, $conf, $SERVER_CTL;
	
	if (is_array($SERVER_SERVICES[$SERVER_TYPE][$service]) && file_exists($SERVER_SERVICES[$SERVER_TYPE][$service]['init'])) {
		$init = $SERVER_SERVICES[$SERVER_TYPE][$service]['init'];
		
		// Arret/démarrage
		$command = $SERVER_CTL . " service $init $action";
		exec($command, $output, $return);
		
		if ($return == 0) {
			return true;
		} else {
			return false;
		}		
		
	} else {
		return false;	
	}
}

// Affiche les derniers mails d'une mailbox IMAP
function afficheMails($maMailBox) {
	global $licence, $conf;
	
	include_once $conf['baseDir'] . 'include/lib.reception.inc.php';
	$mailbox = Kalilab_Mailbox::getInstance();
	if (($mbox = $mailbox->open($maMailBox)) !== null) {
		return false;
	}
	$message_count = imap_num_msg($mbox); 
	$i = $message_count;
	$max = 30;
	$lastDate = '';
	
	echo "<table width='100%' cellpadding=3 cellspacing=1><tr class='titre'><td colspan=5>$maMailBox : $message_count messages au total. $max derniers messages.</td></tr>";
	if ($message_count == 0) {
		echo "<tr><td colspan=5>Aucun message.</td></tr>";
	} else {
		while ($max>0 && $i>0) {
			$header = imap_header($mbox, $i); 
			$body = trim(substr(imap_body($mbox, $i), 0, 50));
			$prettydate = date(DATE_FORMAT . " H:i:s", $header->udate);
			if ($i==$message_count) $lastDate = $header->udate;
			if (isset($header->from[0]->personal)) {
				$personal = $header->from[0]->personal;
			} else {
				$personal = $header->from[0]->mailbox;
			}
			$subject = trim(substr($header->subject, 0, 30));
			
			$email = "$personal <{$header->from[0]->mailbox}@{$header->from[0]->host}>";
			echo "<tr><td>($i)</td><td>$prettydate</td><td>$subject</td><td>$email</td><td>$body (...)</td></tr>";
			$i--;
			$max--;
		}
		if ($message_count>$max) {
			echo "<tr><td colspan='5'>(...)</td></tr>";
		}
	}
	
	
	echo "</table>";
	imap_close($mbox);
	
	return $lastDate;
}

// Date du dernier message recu
function getLastMail() {
	global $licence;
	global $conf;
	include_once $conf['baseDir'] . 'include/lib.reception.inc.php';

	$mailBoxes = Array("INBOX.traite","INBOX");
	$last = "";
	foreach ($mailBoxes as $i=>$mailBox) {
		$mailbox = Kalilab_Mailbox::getInstance();
		if (($mbox = $mailbox->open($mailBox)) !== null) {
			$message_count = imap_num_msg($mbox); 
			if ($message_count>0) {
				$header = imap_header($mbox, $message_count); 
				$last = $header->udate;
			}		
			imap_close($mbox);
		}
	}	
	return $last;
}

// Test d'erreur de mail
function checkLastMail() {
	global $SERVER_MAXMAILTIME;
	$maxtime = $SERVER_MAXMAILTIME * 3600;
	$last = getLastMail();
	$m24 = time() - $maxtime;
	if ($last<=$m24) {
		return false;
	} else {
		return true;
	}
}

// Test fetchmail
function checkFetchmail(&$output) {
	global $licence;
	$sudoCmd = exec("which sudo");
	$mailUser = 'k' . $licence['numero'];
	$fetchmailFile = "/etc/fetchmailrc.".$licence['numero'];
	chown($fetchmailFile, $mailUser);   // Owner : la licence
	chmod($fetchmailFile, 0600);        // Droit rw- --- ---
	exec("$sudoCmd -H -u $mailUser /usr/bin/fetchmail -f $fetchmailFile 2>&1", $output, $return);
	if ($return != 0) {
		return $return;
	} else {
		return true;
	}
}


function checkPidTime($args) {
	/*
		$args["cmd"] command à filtrer
		$args["grep"] à appliquer apres cmd, sinon on prend tout en zappant le header de ps
		$args["pidName"] nom du fichier pid à générer, si vide, ça sera le champs cmd qui sera repris
		$args["minutes"] nombre de minutes de presence max du pid
		$args["cmdForce"] on fait cet exec plutot que le ps de base. (pidName obligatoire)
		$args["fncPerso"] appel une fonction qui retourne un tableau + une valeur de retour
		$args["temporisation"] permet de retourner OLD selon temporisation
		$args["avertName"] nom du fichier d'avert à générer (permet de savoir le dernier OLD pour une tempo)
		$args["timeBeforeKill"] Temps en minutes avant que l'on kill le process (le kill se fait entre timeBeforeKill et timeBeforeKill+5 par sécurité)
		$args["removePidFile"] Supprime le fichier de PID automatiquement lorsque c'est OK.
	*/
	!isset($args["pidName"])		&& $args["pidName"]			= "neti_".$args["cmd"].".pid";
	!isset($args["avertName"])		&& $args["avertName"]		= "neti_".$args["cmd"].".avert";
	!isset($args["minutes"])		&& $args["minutes"] 		= 0;
	!isset($args["removePidFile"])	&& $args["removePidFile"]	= false;
	!isset($args["SHA1Pid"])		&& $args["SHA1Pid"]			= false;
	!isset($args["dataSHA1"])		&& $args["dataSHA1"]		= false;
	!isset($args["deleteOnNew"])	&& $args["deleteOnNew"]		= true;
	
	
	if($args["cmdForce"] != "") {
		$execCommand = $args["cmdForce"];
		exec($execCommand, $output, $return);
	} else if($args["fncPerso"] != "") {
		list($return,$output) = call_user_func($args["fncPerso"],$args);
	} else {
		if($args["grep"] != "") {
			$bonus = ' | grep "'.$args["grep"].'"'; // on grep en plus (ex : commande lancée par sudo...)
		}
		$execCommand = 'ps -C '.$args["cmd"].' --no-headers -o pid,comm,command ';
		exec($execCommand, $output, $return);
	}
	if($args["log"])	{
		$tmpArgs = func_get_args(); //compat 5.1
		file_put_contents("/var/log/kalilab/checkPid.log","=======\n[".date("Y-m-d H:i:s")."] ARGS : ".print_r($tmpArgs,true)."\n",FILE_APPEND);
		file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] ".$execCommand."\n",FILE_APPEND);
	}
	$avertFile = "/tmp/".$args["avertName"];
	$timeAvertFile = 0;
	if(file_exists($avertFile)) {
		$timeAvertFile = filectime($avertFile);
	}
	if($return != 1) {
		if(is_array($output) && count($output) > 0) {
			$pidFile = "/tmp/".$args["pidName"];
			if(file_exists($pidFile)) {
				$pidOld = file_get_contents($pidFile);
				$timeOld = filectime($pidFile);
				if($args["log"])	file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] fileExists ".$pidFile." - PID : ".$pidOld." - TIME : ".date("Y-m-d H:i:s",$timeOld)."\n",FILE_APPEND);
			}
			$tmp = explode(" ",trim($output[0]));
			$pid = trim($tmp[0]);
			if($pidOld != $pid) {
				if($pidOld != "")	unlink($pidFile);
				file_put_contents($pidFile,$pid);
				if($args["log"])	file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] pidOld!=pid - OLD : ".($pidOld!=""?$pidOld:"inexistant")." - PID : ".$pid."\n",FILE_APPEND);
				if($args["deleteOnNew"])	@unlink($avertFile);
				return Array("code" => "new", "time" => time(), "timeHR" => date("Y-m-d H:i:s",time()), "avertTime" => $timeAvertFile, "avertTimeHR" => date("Y-m-d H:i:s",$timeAvertFile));
			} else {
				$delai = $args["minutes"] * 60;
				if($args["log"])	file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] pidOld=pid ".$pidFile." - PID : ".$pidOld." - TIMEOLD : ".date("Y-m-d H:i:s",$timeOld)." - MINUTES ".$args["minutes"]." - TIME (NOW()-minutes) : ".date("Y-m-d H:i:s",(time() - $delai))."\n",FILE_APPEND);
				if((time() - $timeOld) >= $delai) {
					if($args["timeBeforeKill"] > 0 && $pidOld > 0) {
						$onKill = (((time() - $timeOld)/60 >= $args["timeBeforeKill"]) && (((time() - $timeOld)/60) < $args["timeBeforeKill"]+5));
						if($args["log"])	file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] PID : CHECK KILL - ".$args["timeBeforeKill"]." <= ".((time() - $timeOld)/60)." <= ".($args["timeBeforeKill"]+5)." ?! : ".($onKill?"KILL":"NO KILL")." - ".$pidOld."\n",FILE_APPEND);
						if($onKill) {
							exec("kill -9 ".$pidOld);
							file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] kill -9 ".$pidOld."\n",FILE_APPEND);
							@unlink($pidFile);
						}
					}
					if($args["temporisation"] > 0) {
						$timeAvert = ($timeAvertFile > 0 ? $timeAvertFile : 0);
						if($timeAvert == 0 || ((time() - $timeAvert) > ($args["temporisation"]*60))) {
							@unlink($avertFile);
							file_put_contents($avertFile,$pid);
							if($args["log"])	file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] PID : OLD AVEC TEMPO - ".(time() - $timeAvert)." > ".($args["temporisation"]*60)." \n",FILE_APPEND);
							return Array("code" => "old", "time" => $timeOld, "timeHR" => date("Y-m-d H:i:s",$timeOld), "avertTime" => $timeAvertFile, "avertTimeHR" => date("Y-m-d H:i:s",$timeAvertFile));
						} else {
							if($args["log"])	file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] PID : OLD IGNORE CAUSE TEMPO - ".(time() - $timeAvert)." <= ".($args["temporisation"]*60)." \n",FILE_APPEND);
							return Array("code" => "oldTempo", "time" => $timeOld, "timeHR" => date("Y-m-d H:i:s",$timeOld), "avertTime" => $timeAvertFile, "avertTimeHR" => date("Y-m-d H:i:s",$timeAvertFile));
						}
					}
					if($args["log"])	file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] PID : OLD \n",FILE_APPEND);
					return Array("code" => "old", "time" => $timeOld, "avertTime" => $timeAvertFile, "avertTimeHR" => date("Y-m-d H:i:s",$timeAvertFile));
				} else {
					if($args["log"])	file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] PID : CURRENT \n",FILE_APPEND);
					@unlink($avertFile);
					return Array("code" => "current", "time" => $timeOld, "timeHR" => date("Y-m-d H:i:s",$timeOld), "avertTime" => $timeAvertFile, "avertTimeHR" => date("Y-m-d H:i:s",$timeAvertFile));
				}
			}
		} else {
			if($args["log"])	file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] NO PS : EMPTY \n",FILE_APPEND);
			@unlink($avertFile);
			if($args["removePidFile"]) {
				@unlink($pidFile);
			}
			return Array("code" => "empty1", "time" => 0, "timeHR" => "", "avertTime" => $timeAvertFile, "avertTimeHR" => date("Y-m-d H:i:s",$timeAvertFile));
		}
	} else {
		if($args["log"])	file_put_contents("/var/log/kalilab/checkPid.log","[".date("Y-m-d H:i:s")."] NO PS : empty \n",FILE_APPEND);
		@unlink($avertFile);
		if($args["removePidFile"]) {
			@unlink($pidFile);
		}
		return Array("code" => "empty2", "time" => 0, "timeHR" => "", "avertTime" => $timeAvertFile, "avertTimeHR" => date("Y-m-d H:i:s",$timeAvertFile));
	}
}

function makePidFile($args) {
	/**
	 *	avertName : forcer le nom du fichier d'avert	 
	 */	
	!isset($args["pidFile"]) && $args["pidFile"] = "/tmp/neti_".$args["name"].".pid";
	
	$args["typeFile"] = "pid";
	$retour = pidFileExists($args);
	if(!$retour["present"]) {
		if($args["data"]["SHA1Pid"]) {
			file_put_contents($args["pidFile"],sha1($args["dataSHA1"]));
		} else {
			file_put_contents($args["pidFile"],$args["name"]);
		}
		
	}
}

/**
 * Fonction qui permet d'ajouter un délai sur un avertissement
 */ 
function delayAvert($args) {
	!isset($args["pidName"]) && $args["pidName"] = "neti_".$args["name"].".pid";
	
	if(file_exists("/tmp/".$args["pidName"])) {
		if($args["SHA1Pid"]) {
			return Array(0,Array(sha1($args["dataSHA1"])));
		} else {
			return Array(0,Array($args["name"]));
		}
	} else {
		return Array(1,Array()); //pas de fichier..OK
	}
}

function checkServerFileExists($args) {
	/**
	 *	name : nom de l'avertissement
	 *	avertName : forcer le nom du fichier d'avert	
	 *	deleteFile : par défaut à false, permet la suppression directe du fichier d'avertissement	 
	 */		
	!isset($args["typeFile"]) && $args["typeFile"] = "avert";
	!isset($args["avertFile"]) && $args["avertFile"] = "/tmp/neti_".$args["name"].".".$args["typeFile"];
	!isset($args["deleteFile"]) && $args["deleteFile"] = false;
	
	if(file_exists($args["avertFile"])) {
		$ctime = filectime($args["avertFile"]);
		if($args["deleteFile"])	{
			@unlink($args["avertFile"]);
		}
		return Array("present" => true, "time" => date("Y-m-d H:i:s",$ctime));
	}
	return Array("present" => false, "time" => 0);
}

function avertFileExists($args) {
	!isset($args["typeFile"]) && $args["typeFile"] = "avert";
	
	return checkServerFileExists($args);
}

function pidFileExists($args) {
	!isset($args["typeFile"]) && $args["typeFile"] = "pid";
	
	return checkServerFileExists($args);
}

// Check de l'espace disque 
function checkDiskSpace() {
	global $SERVER_MINSPACEPCT;
	$data = Array();
	
	$command = "df -hPk | grep '^/dev' | sed -r 's/[ ]+/ /g' ";
	exec($command, $output, $return);
	foreach($output as $i=>$row) {
		$tab = explode(' ', $row);
		list($pct) = explode('%', $tab[4]);
		
		if ($pct>$SERVER_MINSPACEPCT) {
			$erreur = 1;
		} else {
			$erreur = 0;
		}
		$data[$tab[5]] = Array('occupe'=>$tab[2],'disponible'=>$tab[3],'total'=>$tab[1],'pct'=>$pct, 'erreur'=>$erreur);
	}
	ksort($data);
	return $data;	
}

// Check de l'espace disque des volumes externes
function checkDiskSpaceDistant() {
	global $SERVER_DIST_MINSPACEPCT;
	$data = Array();
	
	$command = "df -hPk | grep ':/vol/' | sed -r 's/[ ]+/ /g' ";
	exec($command, $output, $return);
	foreach($output as $i=>$row) {
		$tab = explode(' ', $row);
		list($pct) = explode('%', $tab[4]);
		list($serveurDistant,$repertoireDistant) = explode(':/vol/',$tab[0]);
		if ($pct>$SERVER_DIST_MINSPACEPCT) {
			$erreur = 1;
		} else {
			$erreur = 0;
		}
		$data[$tab[5]] = Array('serveurDistant' => $serveurDistant, 'repertoireDistant' => $repertoireDistant, 'occupe'=>$tab[2],'disponible'=>$tab[3],'total'=>$tab[1],'pct'=>$pct, 'erreur'=>$erreur);
	}
	ksort($data);
	return $data;	
}

// Check les fichiers de plus de 500Mo 
function checkBigFiles($dirPerso="", $taillePerso="") {
	global $SERVER_FILESIZE, $SERVER_DIR;
	
	echo "\nVérification des gros fichiers sur le serveur, cette opération peut prendre un certain temps.\n";
	if(isset($dirPerso) && $dirPerso != "") $dir = $dirPerso;
	else $dir = $SERVER_DIR;
	
	if(isset($taillePerso) && $taillePerso != "") $taille = $taillePerso;
	else $taille = $SERVER_FILESIZE;
	
	$command = "find ".$dir." -type f -size +".$taille." -exec ls -lh {} \; | awk '{ print $NF \" \" $9 \" : \" $5 }' ";
	exec($command, $output, $return);
	
	ksort($output);
	return $output;	
}

// Check les répertoires avec beaucoup de fichiers à partir du rep courant
function checkLotOfFiles($dirPerso="", $profondeurPerso="") {
	global $SERVER_DIR_KALILAB, $SERVER_DIRDEPTH;
	
	echo "\nVérification du nombre de fichiers sur le serveur, cette opération peut prendre un certain temps.\n";
	if(isset($dirPerso) && $dirPerso != "") $dir = $dirPerso;
	else $dir = $SERVER_DIR_KALILAB;
	
	if(isset($profondeurPerso) && $profondeurPerso != "") $depth = $profondeurPerso;
	else $depth = $SERVER_DIRDEPTH;
	
	$command = "for i in $(find ".$dir." -maxdepth ".$depth." -mindepth 1 -type d); do echo $(find \$i | wc -l) : \$i; done; ";
	// commande qu'on peut taper à la main directement en dessous
	//$command = sort -nr <( for i in $(find /var/www/kalilab/ -maxdepth 10 -mindepth 1 -type d); do echo $(find $i | wc -l) ": $i"; done;) | head -20
	exec($command, $output, $return);
	
	foreach($output as $i=>$row) {
		$tab = explode(' : ', $row);
		$data[$tab[1]] = $tab[0];
	}
	
	arsort($data);
	return $data;	
}

/* Vérification du crontab OBSOLETE */
function checkCrontab(&$log) {
	global $APACHE_USER;
	
	$erreur = false;
	$file = "/etc/crontab";
	exec('/bin/cat '.$file.' | /bin/grep -vE "(^$|^[[:space:]]*#)" | /bin/sed -r "s/[[:blank:]]+/ /g"', $out, $ret);
	
	/* Ce qui doit être présent */
	/* Commun */
	$cronTab = Array(
		Array("Recherche de mails", "root", "moduleCommande/commandeInternet/rechercheMail.php", false),
		Array("Vérification des réceptions", "APACHE_USER", "moduleCommande/commandeInternet/verifReception.php", false),
		Array("Intégration de catalogues", "APACHE_USER", "moduleCommande/commandeInternet/comparaisonAutomatique.php", false),
		Array("Démon Kalilab", "root", "moduleKalilab/gestion/cronDemon.php", false),
		Array("Sauvegardes", "root", "scripts/backup/backup.sh", false),
	);	
	
	if (check_app_name('kalisil')) {
		/* SIL */
		$cronSil = Array(
			Array("Impressions TF", "APACHE_USER", "moduleSil/demande/impression/impressionTacheFond.php", false),
			Array("Envois TF", "APACHE_USER", "moduleSil/demande/impression/envoiTacheFond.php", false),
			Array("Réception Image", "APACHE_USER", "moduleSil/connexion/receptionImage.php", false),
			Array("hprimCron", "APACHE_USER", "moduleSil/hprim/hprimCron.php", false),
			Array("Envoi", "APACHE_USER", "moduleSil/connexion/envoi.php", false),
			Array("Annulation", "APACHE_USER", "moduleSil/connexion/annulation.php", false),
			Array("Scans", "APACHE_USER", "moduleKalilab/scan/cron.php", false),
			Array("Lib Archivage", "APACHE_USER", "include/lib.archivage.inc.php", false),
			Array("Vérification réplication", "APACHE_USER", "listeTables.php", false),
			Array("Démarrage des connection", "APACHE_USER", "moduleSil/connexionRS232/demarrageConnexionAll.php", false),
			Array("Vérification du serveur", "root", "scripts/checkServeur.php", false),
		);
		$cronTab = array_merge($cronTab, $cronSil);
	} else {
		/* LAB */
		$cronLab = Array(
			Array("Recherche de mails (F)", "root", "/usr/bin/fetchmail -f /etc/fetchmailrc", false), // Fetchmail est lancé dans rechercheMail pour les SIL
		);
		$cronTab = array_merge($cronTab, $cronLab);
	}
	
	foreach($out as $i=>$row) {	
		$data = explode(' ', $row);
		if (count($data) < 7) continue;
		
		$heure = array_shift($data);
		$minute = array_shift($data);
		$dow = array_shift($data);
		array_shift($data);
		array_shift($data);
		$user = array_shift($data);
		$commande = implode(' ', $data);
		
		foreach($cronTab as $j=>$cronData) {
			if (preg_match("£".$cronData[2]."£", $commande)) {
				$log .= $cronData[0] ." | ".$cronData[2]." | ";
				
				/* Présence */
				$cronTab[$j][3] = true;
				
				/* Test de l'utilisateur */
				if ($cronData[1] == "APACHE_USER") $myUser = $APACHE_USER;
				else $myUser = 'root';
				
				if ($myUser != $user) {
					$log .= "'$user' au lieu de '$myUser' | ERREUR";
					$erreur = true;
				} else {
					$log .= "user:$user | OK";
				}
				
				$log .= " \n";
			}			
		}	
	}
	
	/* Vérification des non présences */
	$log .= "\nNON PRESENT : \n";
	foreach ($cronTab as $j=>$cronData) {
		if ($cronData[3] == false ) {
			$log .= $cronData[0] ." | ".$cronData[2]."\n";
			//$erreur = true;
		}
	}
		
	return !$erreur;
}

/* Affichage output exec */
function print_output($output, &$log) {
	if (is_array($output)) {
		if (trim($output[0])!='') $log .= "\n >> ".implode("\n >> ",$output);
	}
	return true;
}

/* Version des fichiers */
function get_kl_version() {
	global $conf;
	$version = "";
	$fd = fopen($conf['baseDir'].'/CVS/Tag','r');
	$version = fgets($fd,1000);
	$version = substr($version, 1, -1);
	fclose($fd);
	return $version;
}

/* Dernier patch de la bd */
function get_kl_patch() {
	global $cBdUniq;
	$db = $cBdUniq->query('SELECT idPatch FROM kalilabUpdatePatch ORDER BY idPatch DESC LIMIT 1');
	if ($db && $db->next()) {
		list($versionC,$idP) = explode('_',$db->get('idPatch'));
	}
	list($version, $build) = explode('.',$versionC);
	
	return "V".$version."_".$build." / $idP";
}

/* Test de l'app name */
function check_app_name($name) {
	if ( strToLower(KL_APP_NAME) == strToLower($name) ) return true;
	else return false;
}


/**
 * Récupération des données des services
 */
function getServicesStatut() {
	global $SERVER_SERVICES;
	global $SERVER_TYPE;
	
	$return = Array();
	
	foreach($SERVER_SERVICES[$SERVER_TYPE] as $name=>$data) {
	
		$process = $data['process'];
		$required = ($data['critical']==true);
		$pid = 0;
		
		$return[$name] = Array();
		$return[$name]['description'] = $data['description'];
		if (service_installed($name)) {
			$return[$name]['installed'] = true;
			
			/* Si on a un fichier PID */
			if ($data['pidfile'] != "") {
				if (file_exists($data['pidfile'])) {
					/* Le fichier PID existe */
					$return[$name]['pid'] = service_getpid($name);
					if ($data['port'] > 0) {
						/* Test du port */
						$return[$name]['port'] = $data['port'];
						$fp = fsockopen("127.0.0.1", $data['port'], $errno, $errstr, 30);
						if (!$fp) {
							$return[$name]['problem'] = true;
						} else {
							$return[$name]['problem'] = false;
							fclose($fp);
						}
					} else {
						$return[$name]['problem'] = false;
					}
				} else {
					/* Le fichier PID n'existe pas */
					if ($required == 1) {
						$return[$name]['problem'] = true;
					}
				}
			} else {
				/* Sans fichier PID */
				if (($pid=service_running($name))>0 || ($name=='firewall' && service_firewall_running())) {
					$return[$name]['problem'] = false;
				} else {
					if ($required == 1) {
						$return[$name]['problem'] = true;
					} else {
						$return[$name]['problem'] = false;
					}
				}
			}
			
		} else {
			$return[$name]['problem'] = false;
		}	
	}

	return $return;
}

?>
