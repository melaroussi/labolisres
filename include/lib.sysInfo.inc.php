<?
/**
  * Librairie de sysInfo
  *
  *
  * @package KaliLab
  * @module KaliLab
  * @author Netika <info@netika.net>
  * @tests T00000
  **/
  
  
?><?
class sysInfo {
    
    /**
    * vhostname :
    * Get our apache SERVER_NAME or vhost
    *
    * @return string $result Apache SERVER_NAME or vhost
    *
    * @since 14 Mai 2005
    *
    **/
    
    function vhostname () {
        if (! ($result = getenv('SERVER_NAME')))
            $result = 'N.A.';
            
        return $result;
    } 
  
  
    /**
    * chostname :
    * Get our canonical hostname
    *
    * @return string $result Canonical hostname
    *
    * @since 14 Mai 2005
    *
    **/
    
    function chostname () {
        if ($fp = fopen('/proc/sys/kernel/hostname', 'r')) {
            $result = trim(fgets($fp, 4096));
            fclose($fp);
            $result = gethostbyaddr(gethostbyname($result));
        }
        else
            $result = 'N.A.';
   
        return $result;
    } 
    
    
    /**
    * ip_addr :
    * Get the IP address of our canonical hostname
    *
    * @return string $result IP
    *
    * @since 14 Mai 2005
    *
    **/
     
    function ip_addr () {
        if (!($result = getenv('SERVER_ADDR')))
            $result = gethostbyname($this->chostname());
    
        return $result;
    } 


    /**
    * kernel :
    * Retrieve kernel version
    *
    * @return string $result Kernel version
    *
    * @since 14 Mai 2005
    *
    **/
    
    function kernel () {
        if ($fd = fopen('/proc/version', 'r')) {
            $buf = fgets($fd, 4096);
    
        fclose($fd);

        if (preg_match('/version (.*?) /', $buf, $ar_buf)) {
            $result = $ar_buf[1];
            if (preg_match('/SMP/', $buf))
                $result .= ' (SMP)';
    
        }
        else
            $result = 'N.A.';
        }
        
        else 
            $result = 'N.A.';
     
        return $result;
    } 
  
  
    /**
    * uptime :
    * Retrieve uptime
    *
    * @return string $sys_ticks Uptime
    *
    * @since 14 Mai 2005
    *
    **/
    
    function uptime () {
        global $text;
        $fd = fopen('/proc/uptime', 'r');
        $ar_buf = explode(' ', fgets($fd, 4096));
        fclose($fd);

        $sys_ticks = trim($ar_buf[0]);

        return $sys_ticks;
    } 


    /**
    * users :
    * Number of logged users
    *
    * @return int $result Number of users
    *
    * @since 14 Mai 2005
    *
    **/
    
    function users () {
        $who = explode('=', executeProgram('who', '-q'));
        $result = $who[1];
        return $result;
    } 


    /**
    * loadavg :
    * Retrieve cpu load average
    *
    * @return array $results Load
    *
    * @since 14 Mai 2005
    *
    **/
    
    function loadavg () {
        if ($fd = fopen('/proc/loadavg', 'r')) {
            $results = explode(' ', fgets($fd, 4096));
            fclose($fd);
        } 
        else $results = array('N.A.', 'N.A.', 'N.A.');
    
        return $results;
    } 


    /**
    * cpu_info :
    * Retrieve cpu info
    *
    * @return array $results Cpu Infos
    *
    * @since 14 Mai 2005
    *
    **/
    
    function cpu_info () {
        $results = array();
        $ar_buf = array();

        if ($fd = fopen('/proc/cpuinfo', 'r')) {
            while ($buf = fgets($fd, 4096)) {
                list($key, $value) = preg_split('/\s+:\s+/', trim($buf), 2); 
                // All of the tags here are highly architecture dependant.
                // the only way I could reconstruct them for machines I don't
                // have is to browse the kernel source.  So if your arch isn't
                // supported, tell me you want it written in.
                
                switch ($key) {
                    case 'model name':
                    $results['model'] = $value;
                    break;
                    
                    case 'cpu MHz':
                    $results['mhz'] = sprintf('%.2f', $value);
                    break;
                    
                    case 'cycle frequency [Hz]': // For Alpha arch - 2.2.x
                    $results['mhz'] = sprintf('%.2f', $value / 1000000);
                    break;
                    
                    case 'clock': // For PPC arch (damn borked POS)
                    $results['mhz'] = sprintf('%.2f', $value);
                    break;
          
                    case 'cpu': // For PPC arch (damn borked POS)
                    $results['model'] = $value;
                    break;
                    
                    case 'L2 cache': // More for PPC
                    $results['cache'] = $value;
                    break;
                    
                    case 'revision': // For PPC arch (damn borked POS)
                    $results['model'] .= ' ( rev: ' . $value . ')';
                    break;
                    
                    case 'cpu model': // For Alpha arch - 2.2.x
                    $results['model'] .= ' (' . $value . ')';
                    break;
                    
                    case 'cache size':
                    $results['cache'] = $value;
                    break;
                    
                    case 'bogomips':
                    $results['bogomips'] += $value;
                    break;
                    
                    case 'BogoMIPS': // For alpha arch - 2.2.x
                    $results['bogomips'] += $value;
                    break;
                    
                    case 'BogoMips': // For sparc arch
                    $results['bogomips'] += $value;
                    break;
                    
                    case 'cpus detected': // For Alpha arch - 2.2.x
                    $results['cpus'] += $value;
                    break;
                    
                    case 'system type': // Alpha arch - 2.2.x
                    $results['model'] .= ', ' . $value . ' ';
                    break;
                    
                    case 'platform string': // Alpha arch - 2.2.x
                    $results['model'] .= ' (' . $value . ')';
                    break;
                    
                    case 'processor':
                    $results['cpus'] += 1;
                    break;
                    
                    case 'Cpu0ClkTck': // Linux sparc64
                    $results['mhz'] = sprintf('%.2f', hexdec($value) / 1000000);
                    break;
                    
                    case 'Cpu0Bogo': // Linux sparc64 & sparc32
                    $results['bogomips'] = $value;
                    break;
                    
                    case 'ncpus probed': // Linux sparc64 & sparc32
                    $results['cpus'] = $value;
                    break;
                } 
            } 
            
            fclose($fd);
        } 

        $keys = array_keys($results);
        $keys2be = array('model', 'mhz', 'cache', 'bogomips', 'cpus');

        while ($ar_buf = each($keys2be)) 
            if (! in_array($ar_buf[1], $keys)) 
                $results[$ar_buf[1]] = 'N.A.';
            
        return $results;
    }

    
    /**
    * memory :
    * Retrieve memory specs
    *
    * @return array $results Memory specs
    *
    * @since 14 Mai 2005
    *
    **/
    
    function memory () {
        if ($fd = fopen('/proc/meminfo', 'r')) {
            $results['ram'] = array();
            $results['swap'] = array();
            $results['devswap'] = array();

            while ($buf = fgets($fd, 4096)) {
                if (preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['ram']['total'] = $ar_buf[1];
                } else if (preg_match('/^MemFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['ram']['free'] = $ar_buf[1];
                } else if (preg_match('/^Cached:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['ram']['cached'] = $ar_buf[1];
                } else if (preg_match('/^Buffers:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['ram']['buffers'] = $ar_buf[1];
                } else if (preg_match('/^SwapTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['swap']['total'] = $ar_buf[1];
                } else if (preg_match('/^SwapFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['swap']['free'] = $ar_buf[1];
                } 
            } 
            
            $results['ram']['shared'] = 0;
            $results['ram']['used'] = $results['ram']['total'] - $results['ram']['free'];
            $results['swap']['used'] = $results['swap']['total'] - $results['swap']['free'];
            fclose($fd);
            $swaps = file ('/proc/swaps');
            $swapdevs = explode("\n", $swaps);
    
            for ($i = 1; $i < (sizeof($swapdevs) - 1); $i++) {
                $ar_buf = preg_split('/\s+/', $swapdevs[$i], 6);
    
                $results['devswap'][$i - 1] = array();
                $results['devswap'][$i - 1]['dev'] = $ar_buf[0];
                $results['devswap'][$i - 1]['total'] = $ar_buf[2];
                $results['devswap'][$i - 1]['used'] = $ar_buf[3];
                $results['devswap'][$i - 1]['free'] = ($results['devswap'][$i - 1]['total'] - $results['devswap'][$i - 1]['used']);
                $results['devswap'][$i - 1]['percent'] = round(($ar_buf[3] * 100) / $ar_buf[2]);
            } 
            
            // I don't like this since buffers and cache really aren't
            // 'used' per say, but I get too many emails about it.
            $results['ram']['t_used'] = $results['ram']['used'];
            $results['ram']['t_free'] = $results['ram']['total'] - $results['ram']['t_used'];
            $results['ram']['percent'] = round(($results['ram']['t_used'] * 100) / $results['ram']['total']);
            $results['swap']['percent'] = round(($results['swap']['used'] * 100) / $results['swap']['total']);
        } 
        else {
            $results['ram'] = array();
            $results['swap'] = array();
            $results['devswap'] = array();
        } 
        
        return $results;
    } 


    /**
    * filesystems :
    * Retrieve filesystems informations 
    *
    * @return array $results Any filesystems informations 
    *
    * @since 14 Mai 2005
    *
    **/
    
    function filesystems () {
        $df = executeProgram('df', '-kP');
        $mounts = explode("\n", $df);
        $fstype = array();
        
        if ($fd = fopen('/proc/mounts', 'r')) {
            while ($buf = fgets($fd, 4096)) {
                list($dev, $mpoint, $type) = preg_split('/\s+/', trim($buf), 4);
                $fstype[$mpoint] = $type;
                $fsdev[$dev] = $type;
            } 
            fclose($fd);
        } 
        
        for ($i = 1, $max = sizeof($mounts); $i < $max; $i++) {
            $ar_buf = preg_split('/\s+/', $mounts[$i], 6);
        
            $results[$i - 1] = array();
        
            $results[$i - 1]['disk'] = $ar_buf[0];
            $results[$i - 1]['size'] = $ar_buf[1];
            $results[$i - 1]['used'] = $ar_buf[2];
            $results[$i - 1]['free'] = $ar_buf[3];
            $results[$i - 1]['percent'] = round(($results[$i - 1]['used'] * 100) / $results[$i - 1]['size']) . '%';
            $results[$i - 1]['mount'] = $ar_buf[5];
            ($fstype[$ar_buf[5]]) ? $results[$i - 1]['fstype'] = $fstype[$ar_buf[5]] : $results[$i - 1]['fstype'] = $fsdev[$ar_buf[0]];
        } 
        
        return $results;
    } 


    /**
    * filesystems :
    * Retrieve distro name
    *
    * @return array $results Distro name
    *
    * @since 14 Mai 2005
    *
    **/
    
    function distro () {
        if ($fd = fopen('/etc/debian_version', 'r')) {
            
            $buf = fgets($fd, 1024);
            fclose($fd);
            $result = 'Debian ' . trim($buf);
        
        } elseif ($fd = fopen('/etc/SuSE-release', 'r')) {
            $buf = fgets($fd, 1024);
            fclose($fd);
            $result = trim($buf);
            
        } elseif ($fd = fopen('/etc/mandrake-release', 'r')) {
            $buf = fgets($fd, 1024);
            fclose($fd);
            $result = trim($buf);
          
        } elseif ($fd = fopen('/etc/fedora-release', 'r')) {
            $buf = fgets($fd, 1024);
            fclose($fd);
            $result = trim($buf);
          
        } elseif ($fd = fopen('/etc/redhat-release', 'r')) {
            $buf = fgets($fd, 1024);
            fclose($fd);
            $result = trim($buf);
          
        } elseif ($fd = fopen('/etc/gentoo-release', 'r')) {
            $buf = fgets($fd, 1024);
            fclose($fd);
            $result = trim($buf);
          
        } elseif ($fd = fopen('/etc/slackware-version', 'r')) {
            $buf = fgets($fd, 1024);
            fclose($fd);
            $result = trim($buf);
          
        } elseif ($fd = fopen('/etc/eos-version', 'r')) {
            $buf = fgets($fd, 1024);
            fclose($fd);
            $result = trim($buf);
          
        } elseif ($fd = fopen('/etc/trustix-release', 'r')) {
            $buf = fgets($fd, 1024);
            fclose($fd);
            $result = trim($buf);
          
        } elseif ($fd = fopen('/etc/arch-release', 'r')) {
            $buf = fgets($fd, 1024);
            fclose($fd);
            $result = trim($buf);
          
        } else {
            $result = 'N.A.';
        }
        
        return $result;
    } 
    
    /**
     * Récupération de l'utilisateur apache
     */
    function userapache () {
    	$user = 'apache';
    	if (preg_match('/^[Dd]ebian/', sysInfo::distro())
    	|| preg_match('/^[Uu]buntu/', sysInfo::distro())) {
    		$user = 'www-data';
    	}
    	return $user;
    }
  
    /**
    * backup :
    * Liste les fichiers des répertoires de backup avec les options necessaires
    *
    * @return array Tableau des fichiers et leurs caractéristiques
    * @since 14 Mai 2005
    *
    **/
 
    function backup($dir) {
    
    	/*=====================================================================
    		backup($dir)
    			
    		- execute la commande ls -l --full-time $fichier pour retourner le listing
    		du repertoire avec la taille et la date complete des fichiers
    		- parse le resultat de la commande et retourne un tableau de la forme :
    			[nom fichier] => [date]
    						[taille]
    		pour chaque fichier
    	=======================================================================*/
    	
    	//require(APP_ROOT . '/config.php');
    	$opt = '-l --full-time --sort=time ' . $dir;
    	$res = executeProgram('ls', $opt);
    	$ligne = explode("\n", $res);
    	/*$tmp = $ligne[sizeof($ligne) - 1];
        array_unshift($ligne, $tmp);
        array_pop($ligne);*/
        
        for ( $i = 1 ; $i < sizeof($ligne); $i++ ) {
    		preg_match_all('/(.*)\s+(.*)\s+(.*)\s+(.*)\s+(.*)\s+(.*)\s+(.*)\s+(.*)\s+(.*)/', $ligne[$i], $tmp, PREG_SET_ORDER);
    		preg_match_all('/(.*)\.(.*)/', $tmp[0][7], $tmp2, PREG_SET_ORDER);
    		$nom = $tmp[0][9];
    		$results[$nom]['date'] = $tmp[0][6] . " " . $tmp2[0][1]; //on concatene la date et l'heure
    		$results[$nom]['size'] = $tmp[0][5];

            //preparation du chemin pour md5
            $path = $dir .'/' . $nom;
            $res = executeProgram('md5sum', $path);
            preg_match_all('/(.*)\s+(.*)/', $res, $tmp, PREG_SET_ORDER);
            $results[$nom]['md5'] = $tmp[0][1];
            //$results[$nom]['md5'] = '-';
	   }
            
        return $results;
    
	}


	/**
	 * Vérifie si un paquet est installé et retourne la version 
	 */
	function installedPackage( $pName ) {
		$version = exec('dpkg -l | grep -E "^ii[ ]*' . $pName . '[ ]+" | awk \'{print $3}\'');
		if (empty($version)) {
			return false;
		} else {
			return $version;
		}
	}
	
	
	/**
	 * Récupération des logiciels installés
	 */
	function checkSoftDebian() {
		global $_modules;
		$return = Array();
		
		// TODO: a completer
		$lesSoft = Array(
			"apache2",
			"libapache2-mod-php5",
			"php5",
			"php5-mysql",
			"php5-xsl",
			"php5-imap",
			"php-soap",
			"php-pear",
			"mysql-server",
			"mysql-client",
			"dovecot-common",
			"dovecot-imapd",
			"postfix",
			"fetchmail",
			"cups",
			"cups-bsd",
			"mytop",
			"htop",
			"bash",
			"ntp",
			"samba",
			"smbclient",
			"cvs",
			"zip",
			"unzip",
			"html2text",
			"xpdf",
			"antiword",
			"php-fpdf",
			"pdftk",
			"libtiff4",
			"vsftpd",
			"openvpn",
			"vtun",
		); 
		$lesSoftKalisil = array(
			"ghostscript",
			"ted",
			"imagemagick",
			"libjpeg-progs",
			"php5-curl"
		);
		if ($_modules->activated('Sil'))
			$lesSoft = array_merge($lesSoft, $lesSoftKalisil);
		
		sort($lesSoft);
		// Récupération des versions
		exec('dpkg -l | grep -E "^ii *('.implode(' |', $lesSoft).')" |awk \'{print $2"|"$3}\'', $versions, $ret);
		$tab = Array();
		foreach($versions as $s) {
			list($soft, $version) = explode('|', $s );
			$tab[$soft] = $version;
		}
		
		// Vérifications
		foreach($lesSoft as $s) {
			if (isset($tab[$s])) {
				$return[$s] = $tab[$s];
			} else {
				$return[$s] = false;
			}
		}
		return $return;
	}
	
	/**
	 * Vérifie si une appli est installée
	 */
	function checkSoftIsInstalled( $pkg ) {
		if (preg_match('/^[A-Za-z0-9-]+$/', $pkg))
			if (preg_match('/^Debian/', $this->distro())) {
					return (exec('dpkg -l | grep -E "^ii *'.$pkg.'" |wc -l') > 0);
			} else {
				//TODO : FEDORA
			}
		else 
			return false;
	}
	
	/**
	 * Recherche des logs de sauvegarde
	 */
	function listLsBackup() {
		$files = array();
		$backupDir = "/home/kalilab/backup/";
		if (file_exists($backupDir)) {
			$dir = @opendir($backupDir);
			if ($dir) {
				while ($file=readdir($dir)) {
					if (preg_match('/^LS_(.*)/', $file, $matches)) {
						$files[$matches[1]] = $backupDir.'/'.$file;
					}
				}
				return $files;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Parse des logs de sauvegarde
	 */
	function parseLsBackup( $pFile ) {
		$return = Array();
		if (file_exists($pFile)) {
			$fd = fopen($pFile,'r');
			while($ligne=fgetcsv($fd, 10000, ';')) {
				if(!empty($ligne[0])) 
					$return[] = Array( $ligne[0], $ligne[1], afficheTailleOctet($ligne[2]));
			}
			fclose($fd);
		}
		return $return;	
	}
	
	/**
	 * Récupération du nom du type de sauvegarde
	 */
	function getLsBackupName( $pName ) {
		if ($pName == 'LOCAL')
			return _s("Sauvegarde locale");
		else if (preg_match('/^SSH_(.*)/', $pName, $matches)) 
			return sprintf(_s("Sauvegarde distante sécurisée sur %s"), $matches[1]);
		else if (preg_match('/^SMB_(.*)/', $pName, $matches)) 
			return sprintf(_s("Sauvegarde distante sur partage sur %s"), $matches[1]);
		else if (preg_match('/^FTP_(.*)/', $pName, $matches)) 
			return sprintf(_s("Sauvegarde distante FTP sur %s"), $matches[1]);
	}
	
	
	/** 
	 * Récupération de l'heure du serveur
	 */
	function getServerTime() {
		return date('d-m-Y H:i:s') . ' GMT' . date("O");
	}
	
	/**
	 * Liste des partages samba
	 */
	function listSambaShares() {
		exec("testparm -s", $out, $ret);
		if ($ret != 0) {
			echo "Erreur, le fichier de configuration samba n'est pas accessible.";
			return false;
		} else {
			$return = '';
			foreach ($out as $l) $return.=$l . PHP_EOL;
			return "$return";
		}
	}
	
	
	
	/* Derniere version installée */
	function getKaliVersion() {
		global $cBdUniq;
		$db = $cBdUniq->query('select version,build,nbPatch from kalilabUpdate where status="update ok" ORDER BY date DESC LIMIT 1;');
		if ($db && $db->next()) {
			return Array(
				'version' => $db->get('version'),
				'build' => $db->get('build'),
				'nbPatch' => $db->get('nbPatch'));
		}
		return false;
	}

	

	/**
	 * Recherche du type de version de zend installé
	 */
	function getZendType() {
		$ZENDGUARD = "Zend Guard Loader";
		$ZENDOPTIMIZER = "Zend Optimizer";
	
		if (extension_loaded($ZENDGUARD)) {
			return "Guard";
		}
		
		if (extension_loaded($ZENDOPTIMIZER)) {
			return "Optimizer";
		}
		
		return "None";
	}

}



/**
 * getBackupInfo :
 * Récupère les informations sur les fichiers de backup
 *
 * @param $amovible int Spécifie si on doit récupérer les infos de backup sur média amovible
 * @return array Tableau des fichiers et leurs caractéristiques
 * @since 14 Mai 2005
 *
 **/
 
function getBackupInfo($amovible) {

    $sysInfo = new sysInfo();
    
    //avec l'ancienne backup
    /*$tmp = $sysInfo->backup('/home/kalilab/backup/kalilabJournalier/');
    $results['hdd_daily_backup'] = $tmp;

    $tmp = $sysInfo->backup('/home/kalilab/backup/kalilabMensuel/');
    $results['hdd_monthly_backup'] = $tmp;

    $tmp = $sysInfo->backup('/mnt/cdrom/kalilabJournalier/');
    $results['cd_daily_backup'] = $tmp;
    
    $tmp = $sysInfo->backup('/mnt/cdrom/kalilabMensuel/');
    $results['cd_monthly_backup'] = $tmp;*/
    
    $tmp = $sysInfo->backup('/home/kalilab/backup/archives');
	$results['hddbackup'] = $tmp;
	
    if ( $amovible ) {  
	   $tmp = $sysInfo->backup('/mnt/cdrom');
        $results['cdbackup'] = $tmp;
    }
    
    return $results;
}


/**
 * createBackupCache :
 * Génère le fichier de cache des backup pour Kalilab
 *
 * $param $integrite int Vérification de l'integrité des données
 *
 * @since 14 Mai 2005
 *
 **/
 
function createBackupCache($integrite) {

    $sysInfo = getBackupInfo($integrite);
    
    //avec l'ancienne backup
    //$daily_integrity = verificationBackup($sysInfo['hdd_daily_backup'], $sysInfo['cd_daily_backup']);
    //$monthly_integrity = verification_backup($sysInfo['hdd_monthly_backup'], $sysInfo['cd_monthly_backup']);
    
    if ( $integrite ) {
        $integrity = verificationBackup($sysInfo['hddbackup'], $sysInfo['cdbackup']);
    
        //avec l'ancienne backup
        /*echo "<TR class=titre><TD colspan=5>Sauvegarde Journalière</TD></TR>\n";
        html_backup($sysInfo['hdd_daily_backup'], $daily_integrity);
    
        echo "<TR class=titre><TD colspan=5>Sauvegarde Mensuelle</TD></TR>\n";
        html_backup($sysInfo['hdd_monthly_backup'], $monthly_integrity);
    
        echo "<TR class=titre><TD colspan=5>Sauvegarde Amovible Journalière</TD></TR>\n";
        html_backup($sysInfo['cd_daily_backup'], $daily_integrity);
    
        echo "<TR class=titre><TD colspan=5>Sauvegarde Amovible Mensuelle</TD></TR>\n";
        html_backup($sysInfo['cd_monthly_backup'], $monthly_integrity);*/
    
        $text = "<TR class=titre><TD colspan=5>" . _s("Sauvegarde disque dur") . "</TD></TR>\n";
        $text .= htmlBackup($sysInfo['hddbackup'], 0, 0);
     
        $text .= "<TR class=titre><TD colspan=5>" . _s("Sauvegarde amovible") . "</TD></TR>\n";
        $text .= htmlBackup($sysInfo['cdbackup'], $integrity, 1);
    }
    
    else {
        $text = "<TR class=titre><TD colspan=5>" . _s("Sauvegarde disque dur") . "</TD></TR>\n";
        $text .= htmlBackup($sysInfo['hddbackup'], 0, 0);
    }
   
    $fp = fopen('/var/www/kalilab/cache/sysInfoBackup.cache','w');
    fwrite($fp, $text);
    fclose($fp);
    
}


/**
 * createBackupFtpCache :
 * Génère le fichier de cache des backup sur ftp pour Kalilab 
 *
 * $param $host string hote distant
 * $param $login string utilisateur
 * $param $pass string pass
 * $param $dir string répertoire distant
 *
 * @since 22 Août 2005
 *
 **/
 
function createBackupFtpCache($dir) {

    // identification avec /root/.netrc
    if ( $fd = fopen('/root/.netrc', 'r') ) {
        while ( $buf = fgets($fd, 4096) ) {
            list($key, $value) = preg_split('/\s+/', trim($buf));
            switch ($key) {
                case 'machine':
                    $host[] = $value;
                    break;
                case 'login':
                    $login[] = $value;
                    break;
                case 'password':
                    $pass[] = $value;
                    break;
                default:
                    break;
            }
        }    
    }
    
    //on récupère les différents répertoires pour chaque serveur
    $dirs = explode(" ", trim($dir));
    $text = "";
    
    foreach ($host as $key => $value ) {
        // Mise en place d'une connexion basique
        $connId = ftp_connect($value);
        
        // Identification avec un nom d'utilisateur et un mot de passe
        $login_result = ftp_login($connId, $login[$key], $pass[$key]);
        
        $text .= "<TR class=titre><TD colspan=5>" . _s("Sauvegarde FTP sur ") . "<I>" . $value . "</I></TD></TR>\n";
        
        if ( $connId && $login_result ) {
            
            $text .= "<TR><TD><B>" . _s("Nom") . "</B></TD><TD><B>" . _s("Date") . "</B></TD><TD><B>" . _s("Taille") . "</B></TD><TD><B>" . _s("Etat") . "</B></TD></TR>\n";
            
            // la récupération a lieu en deux temps pour la présentation des données
            
            // Récupération du contenu d'un dossier
            if ( $dirs[$key] == "./" ) $dirs[$key] = ""; //obligé sinon bug avec ftp_nblist
            $contents = ftp_nlist($connId, $dirs[$key] . "backCu*");
            sort($contents);
            
            foreach ( $contents as $key2 => $value ) {
                $taille = ftp_size($connId, $value);
                $text .= "<TR><TD>" . $value . "</TD>";
                $text .= "<TD>" . afficheDate(date(DATE_FORMAT." H:i:s", ftp_mdtm($connId, $value))) . "</TD>"; 
                $text .= "<TD>" . afficheTailleOctet($taille) . "</TD>";
                $text .= "<TD>" . verificationBackupFtp($value, $taille) . "</TD></TR>";
            }
            
            // Récupération du contenu d'un dossier (si dir est de la forme ./ -> bug)
            $contents = ftp_nlist($connId, $dirs[$key] . "backI*");
            arsort($contents);
            foreach ( $contents as $key2 => $value ) {
                $taille = ftp_size($connId, $value);
                $text .= "<TR><TD>" . $value . "</TD>";
                $text .= "<TD>" . afficheDate(date(DATE_FORMAT." H:i:s", ftp_mdtm($connId, $value))) . "</TD>"; 
                $text .= "<TD>" . afficheTailleOctet($taille) . "</TD>";
                $text .= "<TD>" . verificationBackupFtp($value, $taille) . "</TD></TR>";
            }
            
            ftp_close($connId);
        }
        
        else {
            $text .= "<TR><TD colspan=5><B>" . _s("Impossible de se connecter à l'hôte distant") . "</B></TD></TR>";
        }
    }
    
    $fp = fopen('/var/www/kalilab/cache/sysInfoBackupFtp.cache','w');
    fwrite($fp, $text);
    fclose($fp);
    
}


/**
 * createBackupSftpCache :
 * Génère le fichier de cache des backups par sftp pour Kalilab 
 * Rq : utilise le fichier /home/kalilab/sftpRecap généré à la fin du transfert sftp
 *
 * @since 22 Août 2005
 *
 **/
 
function createBackupSftpCache() {

    
    $text = "<TR class=titre><TD colspan=5>" . _s("Sauvegarde SFTP") . "<I>" . $value . "</I></TD></TR>\n";
    $text .= "<TR><TD><B>" . _s("Nom") . "</B></TD><TD><B>" . _s("Date") . "</B></TD><TD><B>" . _s("Taille") . "</B></TD><TD><B>" . _s("Etat") . "</B></TD></TR>\n";

    if ( file_exists('/home/kalilab/sftpRecap') ) {
        $fp = fopen('/home/kalilab/sftpRecap', 'r');
        while ( $buf = fgets($fp, 4096) ) {
            preg_match_all('/(.*)\s+(.*)\s+(.*)\s+(.*)\s+(.*)\s+/', $buf, $tmp, PREG_SET_ORDER);
            if ( strpos($tmp[0][5], "ackCu") ) { // "ackCu pour backCurrent
                $current[$tmp[0][5]] = $tmp[0]; 
            }
            if ( strpos($tmp[0][5], "ackIn") ) { // "ackIn pour backIncr
                /* on va préparer le tableau pour le trier :
                le seul moyen pour avoir un trie simple c'est de lire le numéro d'incrément, 
                l'insérer comme clé dans le tableau puis tier le tableau suivant les clés*/
                
                /*on récupère le numéro sachant que le nom de 
                fichier est de la forme backIncr15-01.01.01.tgz -> très bof!!*/
                $a = strpos($tmp[0][5], 'r') + 1;
                $b = strpos($tmp[0][5], '-');
                $n = substr($tmp[0][5], $a, $b-$a);
                
                $incr[$n] = $tmp[0];
            }
        }
        
        ksort($current);
        krsort($incr);
        
        $text = affichageBackupFtp($current);
        $text .= affichageBackupSftp($incr);
    }
    
    
    else {
        $text .= "<TR><TD colspan=5><B>" . _s("Fichier de récapitulation inaccessible.") . "</B></TD></TR>";
    }
    
    $fp = fopen('/var/www/kalilab/cache/sysInfoBackupSftp.cache','w');
    fwrite($fp, $text);
    fclose($fp);
}


/**
 * affichageBackupSftp :
 * retourne une chaine HTML pour l'affichage des fichiers sftp
 *
 * @param string $data  Tableau de données
 *
 * @return string chaine html
 *
 * @since 14 Mai 2005
 *
 **/
 
function affichageBackupSftp($data) {
    $text = "";
    foreach ( $data as $key => $value ) {
    
            $text .= "<TR><TD>" . $value[5] . "</TD>\n";  
            $text .= "<TD>" . $value[2] . " " . $value[3] . " " . $value[4] . "</TD>\n";  
            $taille = substr($value[1], strrpos(trim($value[1]), " "));
            $text .= "<TD>" . afficheTailleOctet($taille) . "</TD>\n";
            $text .= "<TD>" . verificationBackupFtp($value[5], $taille) . "</TD></TR>";
    }
    return $text;
}


/**
 * verificationBackup :
 * Récupère les informations sur les fichiers de backup
 *
 * @param string $backup1 backup de reférence(disque dur)
 * @param string $backup2 backup de reférence(amovible)
 *
 * @return array Tableau des fichiers en erreur
 *
 * @since 14 Mai 2005
 *
 **/
 
function verificationBackup($backup1, $backup2) {
	/* 
	$backup1 = sauvegarde sur disque dur
	$backup2 = sauvegarde sur média amovible
	
	Fonction qui compare le md5 de chaque fichier de
	$backup1 avec $backup2.
	Si différent, place une erreur sur le fichier
	Si un fichier de $backup1 n'est pas dans $backup2, erreur
	*/
	
	while ( list($name, $value) = each($backup1) ) {
		if (  $backup2[$name] ) {
			if ( $backup1[$name]['md5'] != $backup2[$name]['md5'] )
				$results[] = $name;
		}
		
		else $results[] = $name;
	}
	
	return $results;	
}


/**
 * verificationBackupFtp :
 * Récupère les informations sur les fichiers de backup
 *
 * @param string $backup1 backup de reférence(disque dur)
 * @param string $distSize taille du fichier distant
 *
 * @return string Etat du fichier
 *
 * @since 14 Mai 2005
 *
 **/
 
function verificationBackupFtp($file, $distSize) {
	
	// si c'est backCurrent, on la "tag" à OK car elle est jour une fois par semaine seulement
	if ( strpos($file, "Current") ) return _s("Transféré périodiquement");
	if ( strpos($file, ".part") ) return "<FONT color=red>" . _s("Défaillant") . "</FONT>";
	
	// construction du fichier local en fonction du fichier distant
	$local = "backIncr" . substr($file, strpos($file, '-') + 1);
	
	// récupération de la taille du fichier local
	$localLs = executeProgram('ls', '-l /home/kalilab/backup/archives/' . $local);
	preg_match_all('/(.*)\s+(.*)\s+(.*)\s+(.*)\s+(.*)\s+/', $localLs, $tmp, PREG_SET_ORDER);
	
	// affichage du résultat
	if ( !$tmp[0][2] ) return "<FONT color=green>" . _s("Dépassée") . "</FONT>";
    if ( $tmp[0][2] !=  $distSize ) return "<FONT color=red>" . _s("Défaillant") . "</FONT>";
    return _s("OK");
}


/**
 * htmlBackup :
 * Génère le code HTML pour la recap des backups
 *
 * @param array $backup Tableau issu de type getBackupInfo
 * @param array $verif Tableau issu de verificationBackup
 * @param int $media 0=disque dur 1=autre
 * 
 * @return string $text Code HTML
 *
 * @since 14 Mai 2005
 *
 **/
 
function htmlBackup($backup_type, $verif, $media) {
    
    $text = "<TR><TD width=35%><b>Nom</b></TD>\n";
    $text .= "<TD width=25%><b>Date</b></TD>\n";
    $text .= "<TD width=15%><b>Taille</b></TD>\n";
    $text .= "<TD width=25%><b>Etat</b></TD></TR>\n";

    while ( list($name, $value) = each($backup_type) ) {
		if ( $media == 0 && $name != "backCurrent.tgz")
			$text .= "<TR><TD><A HREF=\"#\" onClick=\"makeRemote('descIncr', 'sysInfoBackupDetail.php?fichier=" . $name . "', 500, 400); return false;\">" . $name . "</A></TD>\n";
		else
			$text .= "<TR><TD>" . $name . "</TR></TD>\n";
			
		$text .= "<TD>" . afficheDateTime($value['date']) . "</TD>\n";
		$text .= "<TD>" . afficheTailleOctet($value['size']) . "</TD>\n";
		$text .= "<TD>";
		
		//test sur l'integrité
		if ( $verif && in_array($name, $verif) )
			$text .= "<FONT color=red>" . _s("Défaillant") . "</FONT>";
		else
			$text .= "OK";
					
		$text .= "</TD></TR>\n";
    }
    
    return $text;
}

error_reporting(5); // So that stupid warnings do not appear when we stats files that do not exist.


/**
 * createBargraph :
 * Construit une barre de progression 
 *
 * @param string $percent Pourcentage à représenter
 *
 * @return string Code HTML de la barre
 * 
 * @since 14 Mai 2005
 *
 **/   
 
function createBargraph($percent) {

    preg_match_all('/(\d+)(.*)/', $percent, $tmp, PREG_SET_ORDER);

    if ( $tmp[0][1] < 90 )
        $_text =  '<table><tr><td bgcolor="#66FF00"><font size="-1">';
    else
        $_text =  '<table><tr><td bgcolor="red"><font size="-1">';
	
    for($i = 0; $i < $tmp[0][1] / 2 ; $i++)
        $_text .= '&nbsp;';
	
    $_text .= "</td><td><font size=\"-1\">$percent</font></td></tr></font></table>";

    return $_text;
} 


/**
 * findProgram :
 * Find a system program.  Do path checking
 *
 * @param string $program Program to find
 *
 * @return string Path of the program
 * 
 * @since 14 Mai 2005
 *
 **/   
 
function findProgram ($program) {
    $path = array('/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin');

    if (function_exists("is_executable")) {
        while ($this_path = current($path)) {
            if (is_executable("$this_path/$program")) {
                return "$this_path/$program";
            }     
            next($path);
        }
    }
    else {
        return strpos($program, '.exe');
    }

    return;
} 


/**
 * executeProgram :
 * Execute a system program.
 *
 * @param string $program Program to execute
 * @param string $args Program arguments
 *
 * @return string Return of the program
 * 
 * @since 14 Mai 2005
 *
 **/   
 
function executeProgram ($program, $args = '') {
    $buffer = '';
    $program = findProgram($program);

    if (!$program) {
        return;
    } 
    // see if we've gotten a |, if we have we need to do patch checking on the cmd
    if ($args) {
        $args_list = explode(' ', $args);
        for ($i = 0; $i < count($args_list); $i++) {
            if ($args_list[$i] == '|') {
                $cmd = $args_list[$i + 1];
                $new_cmd = findProgram($cmd);
                $args = str_replace("| $cmd", "| $new_cmd", $args);
            } 
        } 
    } 
    // we've finally got a good cmd line.. execute it
    if ($fp = popen("$program $args", 'r')) {
        while (!feof($fp)) {
            $buffer .= fgets($fp, 4096);
        } 
        return trim($buffer);
    } 
} 
    

 /**
 * convertSecondesDHM :
 * Formate un temps en secondes en day_hour_minutes 
 *
 * @param int $sec Nombre de secondes
 *
 * @return string Chaine formatée
 * 
 * @since 14 Mai 2005
 *
 **/ 
    function convertSecondesDHM($sec) {

        global $text;

        $min = $sec / 60;
        $hours = $min / 60;
        $days = floor($hours / 24);
        $hours = floor($hours - ($days * 24));
        $min = floor($min - ($days * 60 * 24) - ($hours * 60));

        if ($days != 0) $result = "" . _n("%s jour ","%s jours ",$days);
			if(false) _s("%s jours ");
        if ($hours != 0) $result .= "" . _n("%s heure ","%s heures ",$hours);
 			if(false) _s("%s heures ");
        $result .= "" . _n("%s minute ","%s minutes ",$min); 
			if(false) _s("%s minutes ");
        return $result;
    }



/**
 * Vérification du requiretty
 */
function checkRequireTTY() {
	if ( preg_match('/^[Ff]edora/', sysInfo::distro())) {
		if (exec('cat /etc/sudoers | grep "requiretty" | grep -Ev "^#" | wc -l') > 0) {
			klog("Attention : l'instruction 'requiretty' dans le sudoers détectée, il faut la supprimer avec visudo");
		}
	}
}


?>
