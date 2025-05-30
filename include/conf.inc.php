<?php
 /**
  * Config de KaliRes
  *
  *
  * @package KaliLab
  * @module KaliLab
  * @author Netika <info@netika.net>
  * @cvs $Id: conf.inc.php,v 1.9.8.2 2017-08-21 11:33:45 sebastien Exp $
  * @tests T00000=
  **/
include("licence.ver");

$conf["bench"]['start'] = microtime();
$conf['debug'] = false;
$conf['debugMail'] = false;
$conf['debugSql'] = false;
$conf['debugSaveKo'] = false;
$conf['debugTraduction'] = false;
$conf["baseDir"]="/var/www/kalires/";
$conf["osServeur"] = 'linux';

if(preg_match("/(^[https]).*/",$_SERVER['SCRIPT_URI'])) $protocol = "https";
elseif( isset($_SERVER['HTTPS']) ) $protocol = "https";
else  $protocol = "http";

$host = $_SERVER["HTTP_HOST"];

$conf["baseURLProt"]=$protocol;
$conf["baseURL"]=$protocol."://".$host."/";

$conf["lpp"]=20; //Ligne Par Page
$conf['smtp'] = 'smtp.netika.net';
$conf["serveurExternalise"] = true;
$conf['email'] = 'k'.$licence['numero'].'@kalilab.fr';

$conf['emailFromLabel'] = $licence['detenteur'][0];
$conf['emailFrom'] = $conf['email'];
$conf["serveurSoap"] = "https://kalisil/soapServer/";

$conf["dataDir"]="/var/www/kalires-data/";

/* CONF DEVEL */
$mydir = dirname(__FILE__);
if (file_exists($mydir.'/conf.devel.inc.php')) include($mydir.'/conf.devel.inc.php');

if( file_exists($conf['baseDir']."include/conf.update.inc.php") ) {
	@include($conf['baseDir']."include/conf.update.inc.php");
}

?>