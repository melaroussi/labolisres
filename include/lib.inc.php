<?php

include_once ($conf["baseDir"]."include/lib.patient.inc.php");
include_once ($conf["baseDir"]."include/lib.soap.inc.php");
include_once ($conf["baseDir"]."include/lib.date.inc.php");
include_once ($conf["baseDir"]."include/Kfile.class.php");
include_once ($conf['baseDir']."include/epaiement/paiementEnLigne.ctrl.php");

foreach($_GET as $key=>$value){
	${$key} = $value;
}

foreach($_POST as $key=>$value){
	${$key} = $value;
}

if (NO_SESSION_START!=1) {
	session_start();
    if (!isset($_SESSION['patientLogged'])) {
		$patientLogged = new PatientLogged('','');
		$_SESSION['patientLogged'] = $patientLogged;
	} else $patientLogged = $_SESSION['patientLogged'];
}

if(isset($patientLogged)) {
	if(!defined('KALILAB_SESSION_NO_UPDATE')) $patientLogged->update();
} else {
	$conf['userLang'] = "fr_FR";$conf['userLangLocale'] = "french";
}

putenv("LANG=".$conf['userLang']);
putenv("LANGUAGE=".$conf['userLang']);

function afficheHead($titre,$script,$javascript=false,$displayAllScript=false){
	global $conf,$patientLogged;
	$res= "<!DOCTYPE html>\n<html lang="fr"><head><title>".$titre."</title>";
        $res.= "\n\t<meta charset=\"UTF-8\">";
        $res.= "\n\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">";
	$res.= "\n\t<link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"".$conf["baseURL"]."favico.kalires.ico\" />";
	$res.= "\n\t<link rel=\"icon\" type=\"image/gif\" href=\"".$conf["baseURL"]."images/kalires.gif\" />";
	$res.= "\n\t<link rel=\"stylesheet\" href=\"".$conf["baseURL"]."include/kalires2.css\">";
	$res.= "\n\t<link rel=\"stylesheet\" href=\"".$conf["baseURL"]."include/calendar-system.css\">";
	$res.= "\n\t<link rel=\"stylesheet\" href=\"".$conf["baseURL"]."style2.css\">";
	$res.= "\n\t<link rel=\"stylesheet\" href=\"".$conf["baseURL"]."include/qtip.css\">";
	$res.= "\n\t";
    $res.= "\n\t<SCRIPT LANGUAGE='Javascript' src=\"".$conf["baseURL"]."include/lib.js\" ></script>";
    $res.= "\n\t<SCRIPT LANGUAGE='Javascript' src=\"".$conf["baseURL"]."include/validator.js\" ></script>";
    $res.= "\n\t<SCRIPT LANGUAGE='Javascript' src=\"".$conf["baseURL"]."include/jquery.js\" ></script>";
    $res.= "\n\t<SCRIPT LANGUAGE='Javascript' src=\"".$conf["baseURL"]."include/jquery.plugin.qtip.js\" ></script>";
	$res.= "\n\t<script type=\"text/javascript\" src=\"".$conf["baseURL"]."include/calendar.js\"></script>";
	$res.= "\n\t<script type=\"text/javascript\" src=\"".$conf["baseURL"]."include/calendar-fr.js\"></script>";
	$res.="\n</head><body topmargin=0 leftmargin=0 >";

	if($_SESSION["accesPermalink"]){
		$authorized = Array();
		switch($_SESSION["accesPermalinkLevel"]){
			case 0: 
				$authorizedPages = Array(
										"index.php"					=> "",
										"afficheDossier.php"		=> "",
										"consultation.php"			=> Array("numId"),
										"changePassword.php"		=> ""
									);
				break;
			case 1: 
				$authorizedPages = Array(
										"index.php"					=> "",
										"afficheDossier.php"		=> "",
										"consultation.php"			=> "",
										"prescription.php"			=> "",
										"listePrescription.php"		=> "",
										"changePassword.php"		=> ""
									);
				break;
			case 2: 
				$authorizedPages = Array(
										"index.php"					=> "",
										"afficheDossier.php"		=> "",
										"consultation.php"			=> "",
										"changePassword.php"		=> ""
									);
				break;
		}
		$goToDenied = true;
		$url = parse_url($_SERVER['REQUEST_URI']);
		foreach($authorizedPages as $page => $conditions){
			if(strpos($url["path"], $page) !== FALSE) {
				$conditionsMet = true;
				if(is_array($conditions) && count($conditions)>0){
					foreach($conditions as $var){
						if(strpos($url["query"], $var) === FALSE) {
							$conditionsMet = false;
							break;
						}
					}
				}
				if($conditionsMet){
					$goToDenied = false;
					break;
				}
			}
		} 
		if($goToDenied){
			$_SESSION["loginError"] = 6;
			$patientLogged->logout();
			header("Location:denied.php");
		}
	}

	echo $res;	
}

function afficheFoot($options=Array()) {
?>
			<br /><br />
			</div>
					<div class="clear mozclear"></div>
				</div>
			</div>
			<div class="hide" id="nsFooterClear"><!-- for NS4's sake --></div>
			<div id="footer" class="gap">
			</div>
		</div>
	</div>
	</body>
	</html>
	<?
}

function afficheMessage($sMsg,$style=""){
	if ($sMsg!="") echo "<center><div class=\"corps\" id=\"afficheMessage\" style=\"".$style."\"><b>$sMsg</b></div></center>";
}

function afficheMessageSil($message=false) {
	global $patientLogged;
	
	if($message !== false) {
		$str = "";
		$msg = "";
		if($message["generique"]["value"] != "") {
			$msg .= $message["generique"]["value"];
		}
		
		if($message["specifique"]["value"] != "") {
			if($message["generique"]["value"] != "") {
				$msg .= "<br /><br /> ";
			}
			$msg .= $message["specifique"]["value"];
		}
		
		if($msg != "") {
		
			$msg = formateTexteAccueil($msg);
			$masque = false;
			if($patientLogged->messageAffiche != "" && $patientLogged->messageAffiche > max($message["generique"]["timestamp"],$message["specifique"]["timestamp"])) {
				$masque = true;
			}
			
			$str = "<center><div class=\"corps hand\" onClick=\"masqueMessage();\" style=\"text-align:left;width:600px;border-bottom:#6495ED solid 1px;border-right:#6495ED solid 1px;border-top:#4C95D6 solid 2px;border-left:#4C95D6 solid 2px;position:relative;margin:0px;\">"
					."<img src=\"images/".(($masque)?("plus.gif"):("minus.gif"))."\" id=\"imgMessage\" style=\"position:absolute;top:3px;right:3px;\" title=\""._s("Afficher / Masquer")."\">"
					."<div id=\"messageLabo\" style=\"display:".(($masque)?("none"):("block")).";margin:4px;padding:2px;\">".$msg."</div>"
					."<div id=\"messageReduit\" style=\"display:".(($masque)?("block"):("none")).";margin:1px;padding:1px;color:#666;font-style:italic;font-size:10px;\">"._s("Message du laboratoire")." : ".tronquer(strip_tags(nl2br($msg)),70)."</div>"
				."</div></center>";
				
		}
		$patientLogged->message = $str;
	}

	?>
	<script type="text/javascript">
		function masqueMessage(afficher) {
			if($('#messageLabo').is(':visible')) {
				$('#messageLabo').hide();
				$('#messageReduit').show();
				$('#imgMessage').attr('src','images/plus.gif');
				var afficher = 0;
			} else {
				$('#messageReduit').hide();
				$('#messageLabo').show();
				$('#imgMessage').attr('src','images/minus.gif');
				var afficher = 1;
			}
			jQuery.ajax({
		        type: "POST",
		        url: 'consultation.ajax.php?afficher='+afficher,
		        success: function(responseText,textStatus,msgObj){ }
			});
		}
	</script>
	<?php

	echo $patientLogged->message;
	
}

function paiementAllowed($idSite=0) {
	$urlPaiement  = getSrOption('urlPaiement');
	$txtPaiement  = getSrOption('txtPaiement');
	$typePaiement = getSrOption('interfacePaiement');

	if($idSite != 0 && paiementEnLigneCtrl::isInterfaceMonoSite($typePaiement)){
		$idPaiement   = getSiteOption("MERCHANT_ID_PAIEMENT", $idSite);
		$clePaiement  = getSiteOption("SECRET_KEY_PAIEMENT" , $idSite);
	} else {
		$idPaiement   = getSrOption('merchantIdPaiement');
		$clePaiement  = getSrOption('secretKeyPaiement');
	}
	return ($txtPaiement != ''&& (($urlPaiement != '' && $typePaiement == "CBI") || (($typePaiement == "MERCANET" || $typePaiement == "MONETICO" || $typePaiement == "PAYBOX") && $idPaiement != '' && $clePaiement != '') || ($typePaiement == "OGONE"  && $idPaiement != '')));
}

function getStrPaiement($idDemande,$numDemande,$montant, $idSite=0, $affichage='texte',$argEmail=false,$argIdPatient=false) {
	global $patientLogged, $conf;
	
	$autorise = $paiement = false;
	if($patientLogged instanceof PatientLogged && $patientLogged->isAuth()) {
		$email = $patientLogged->email;
		$idPatient = $patientLogged->id();
		$autorise = $patientLogged->paiement;
	} elseif($argEmail !== false && $argIdPatient !== false) {
		$email = $argEmail;
		$idPatient = $argIdPatient;
		$autorise = true;
	} else {
		return '';
	}
	$strPaiement = "";
	$urlPaiement = getSrOption('urlPaiement');
	$txtPaiement = getSrOption('txtPaiement');
	$type        = getSrOption('interfacePaiement');
	$merchantId  = getSrOption('merchantIdPaiement');
	$secretKey   = getSrOption('secretKeyPaiement');
	$tpeNumber   = getSrOption('tpeNumber');
	$numDemande  = str_replace(array("-","_","/"," "),array("","","",""),$numDemande);
	
	$returnUrl = $conf["baseURL"].'redirect.php?numDemande='.$numDemande.'&idDemande='.$idDemande.'&montant='.$montant;
	$responseUrl = $conf["baseURL"].'paiement.php';
	if($idSite != 0 && paiementEnLigneCtrl::isInterfaceMonoSite($type)){
		$merchantId  = getSiteOption("MERCHANT_ID_PAIEMENT", $idSite);
		$secretKey   = getSiteOption("SECRET_KEY_PAIEMENT" , $idSite);
		$returnUrl = $conf["baseURL"].'redirect.php?numDemande='.$numDemande.'&idDemande='.$idDemande.'&montant='.$montant.'&idSite='.$idSite;
		$responseUrl = $conf["baseURL"].'paiement.php?idSite='.$idSite;
	}
	
	if($autorise && paiementAllowed($idSite) ) {
		$y = date("Y");
		$md = date("md");
		$h = date("H");
		$is = date("is");
		$idDemandeFixe = sprintf("%012d",$idDemande);
		list($montant1,$montant2) = explode('.',$montant);
		$montant1 = sprintf("%06d",$montant1);
		$montant2 = sprintf("%02d",$montant2);
		
		if ($type=='CBI') {
			$tokenTmp = sha1("#|Jv".$idPatient."=+&£".$idDemande."*!W<".$numDemande."};Ku".$montant1."J?;s".$montant2."%kEn".$y."Er".$md."9*".$h."£ù".$is.".§Ft");
			$tokenCalcule = $montant2.substr($idDemandeFixe,6,6).substr($tokenTmp,30,6).$h.substr($tokenTmp,0,10).$y.substr($tokenTmp,10,5).$is.substr($tokenTmp,20,10).$md.substr($tokenTmp,15,5).$montant1.substr($idDemandeFixe,0,6);
			$urlPaiement = str_replace(Array("[[token]]","[[idDemande]]","[[numDemande]]","[[email]]","[[montant]]"),Array(urlencode($tokenCalcule),urlencode($idDemande),urlencode($numDemande),urlencode($email),urlencode($montant)),$urlPaiement);
			switch($affichage) {
				case 'texte': 	 	$strPaiement = "<a href=\"".$urlPaiement."\">".htmlentities($txtPaiement)." <img src=\"images/facture.gif\" border=\"0\"></a>";
					break;
				case 'reduit':  	$strPaiement = "<a href=\"".$urlPaiement."\" title=\"".htmlentities($txtPaiement)."\"><img src=\"images/facture.gif\" border=\"0\"></a>";
					break;
				case 'url':  		$strPaiement = $urlPaiement;
					break;
				case 'redirect':  	return $urlPaiement;
			}
		} else {
			/*
			* Attention :
			*	pour le compte monetico, l'adresse responseURL est configurée dans le compte par monetico.
			*	Pour le compte test 2b6536860c1ef50677da elle est http://www.netika.net/monetico/Phase2Back.php
			*/
			$paiement = paiementEnLigneCtrl::get($type,$merchantId,$secretKey,$tpeNumber, getSrOption('testPaiement'));
		}

		if($paiement) {
			$transactionReference = $numDemande;
			if ($type=="MONETICO") {
				$transactionReference = substr($transactionReference,-12);
			} else if ($type == "MERCANET") {
				$transactionReference = $transactionReference.date('ymdHs');
			}
			
		    $paiement->set('amount', $montant);
		    $paiement->set('returnURL', $returnUrl);
		    $paiement->set('responseURL', $responseUrl);
		    $paiement->set('transactionReference', $transactionReference);
		    $paiement->set('customerId', $idPatient);
		    $paiement->set('orderId', $idDemande.'-'.date('ymdHs'));
		    $paiement->set('customerEmail', $email);
			$retForm = $paiement->getForm('formPayer', 'formPayer'.$idDemande);
			if ($retForm["erreur"]) {
				echo "<div align='center'><font style=\"color:red\"><b>"._s("Erreur interface de paiement en ligne : ")."</b>".$retForm["data"]."</font></div>";
				@file_put_contents("/var/log/kalires/epaiement.log", "[".date("Y-m-d H:i:s")."] ".$retForm["data"]."\n", FILE_APPEND);
			} else {
				echo $retForm["data"];
				
				switch($affichage) {
					case 'texte': 	 	$strPaiement = "<a href=\"#\" onclick=\"$('form#formPayer".$idDemande."').submit();return false;\">".htmlentities($txtPaiement)." <img src=\"images/facture.gif\" border=\"0\"></a>";
						break;
					case 'url':
					case 'reduit':  	$strPaiement = "<a href=\"#\" onclick=\"$('form#formPayer".$idDemande."').submit();return false;\" title=\"".htmlentities($txtPaiement)."\"><img src=\"images/facture.gif\" border=\"0\"></a>";
						break;
					case 'redirect':	$strPaiement = "<script language=\"javaScript\">$('form#formPayer".$idDemande."').submit();</script>";
						break;
				}
			}
		}
	}
	return $strPaiement;
}

function isTypePaiementOk($typePaiement) {
	$interfaceOk = Array("OGONE", "MONETICO", "MERCANET", "PAYBOX");

	if (in_array($typePaiement, $interfaceOk)) return true;
	else return false;
}

function filtrageAcces($niveauLimite,$urlNonAuth,$urlNonAcces) {
	// urlNonAuth : l'adresse (index.php?automate=centaur...) du lien pour reloader la page
	// urlNonAcces : l'adresse (index.php?automate=centaur...) pour revenir en arriere si la personne
	// n'a pas le niveau necessaire
	global $conf,$patientLogged;
	if (!isset($patientLogged)) {$patientLogged= new PatientLogged(); }
	$patientLogged->filtrageNiveau($niveauLimite,$urlNonAuth,$urlNonAcces,$droit,$acces);
	$conf['debug_filtrageAcces'] = true;
}

function navGetInputDate($arg=Array(),$peutEtreVide=false,$dateExtended = false,$afficheImg = true,$dateLunaire = false,$bloqueFutur=false,$widthImg="20"){
	global $conf;
	if(!isset($arg['id'])) $arg['id'] = uniqid('ng');

	$res = "<INPUT autocomplete=\"off\" type=\"text\" SIZE=\"8\" MAXLENGTH=\"11\" ";
	foreach($arg as $nom => $val){
        if ($nom=='value' && $val=='00-00-0000') $val='';
        if( $nom == 'onBlur'){
        }else{
        	$res .= "$nom=\"$val\" ";
        }
	}
	$res .= "onFocus=\"\" help=\""._s("010105 = 01-01-2005<BR>01011900 = 01-01-1900<BR>'espace' pour la date du jour<BR>+ ou - pour demain ou hier<BR>Double cliquez ou saisissez 'c' pour afficher le calendrier","input")."\" onKeyUp=\"validator_masque_date(event,this);\" onBlur=\"validator_verifie_date(event,this,".($peutEtreVide?"true":"false").",".($dateLunaire?"true":"false").",true,".(($bloqueFutur)?("true"):("false")).");".$arg['onBlur']."\" onDblClick=\"return showCalendar('".$arg['id']."'".($dateExtended?",1900,2050,10":"").");\"";
	$res .= ">";
	if($afficheImg) $res .= "<img src=\"".$conf['baseURL']."/images/icocal.gif\" width=\"$widthImg\" class=\"hand\" title=\""._s("Montrer le calendrier")."\" onClick=\"return showCalendar('".$arg['id']."'".($dateExtended?",1900,2050,10":"").");\">";
	return $res;
}

function klFlush(){
	echo str_repeat(" ",4096);
	flush();
}

if(!function_exists('ob_end_get_contents')){
	function ob_end_get_contents(){
		$res = ob_get_contents();
		ob_end_clean();
		return $res;

	}
}

function klRedir($page,$nbSecondes,$texte){

	if($page=='actionreloadkalilab')
	{

		echo "\n<SCRIPT LANGUAGE=Javascript>";
		echo "function changeUrl(){reloadAllKalilab();}\n";
		echo "setTimeout(\"changeUrl()\",".$nbSecondes."000);\n";
		echo "</SCRIPT>";
		echo klMessage('info',$texte,sprintf(_s("Si cette page ne se rafraichit pas dans %s secondes cliquer %s ici"),$nbSecondes,"<a href=\"#\" onClick=\"changeUrl();return false;\">")."</a>","");

	} elseif($page=='actionclose')
	{

		echo "\n<SCRIPT LANGUAGE=Javascript>";
		echo "function changeUrl(){self.close();}\n";
		echo "setTimeout(\"changeUrl()\",".$nbSecondes."000);\n";
		echo "</SCRIPT>";
		echo klMessage('info',$texte,sprintf(_s("Si cette page ne se ferme pas dans %s secondes cliquer %s ici"),$nbSecondes,"<a href=\"javascript:self.close();\" onClick=\"changeUrl();return false;\">")."</a>","");

	} else {
		echo "\n<SCRIPT LANGUAGE=Javascript>";
		echo "function changeUrl(){document.location.href='"._secho($page,'ads')."';}\n";
		echo "setTimeout(\"changeUrl()\",".$nbSecondes."000);\n";
		echo "</SCRIPT>";
		echo klMessage('info',$texte,sprintf(_s("Si cette page ne s'enlève pas dans %s secondes cliquer %s ici"),$nbSecondes,"<a href=\"$page\" onClick=\"changeUrl();return false;\">")."</a>","");
	}

}

if (!defined("ENT_COMPAT")) define("ENT_COMPAT", 2);
if (!defined("ENT_NOQUOTES")) define("ENT_NOQUOTES", 0);
if (!defined("ENT_QUOTES")) define("ENT_QUOTES", 3);
if (!function_exists('html_entity_decode')) {
	function html_entity_decode ($string, $opt = ENT_COMPAT) {
	
		$trans_tbl = get_html_translation_table (HTML_ENTITIES);
		$trans_tbl = array_flip ($trans_tbl);
		
		if (($opt & ENT_QUOTES)  > 0) { // Translating single quotes
		
			// Add single quote to translation table;
			// doesn't appear to be there by default
			$trans_tbl["&apos;"] = "'";
			$trans_tbl["&#039;"] = "'";
		
		}
		
		if (!(($opt & ENT_COMPAT) > 0)) { // Not translating double quotes
			
			// Remove double quote from translation table
			unset($trans_tbl["&quot;"]);
		
		}
		
		return strtr ($string, $trans_tbl);
	}
}

if( ! function_exists('var_export') ) {
	function var_export($a){
		$result = "";
		switch (gettype($a)){
		     case "array":
		             reset($a);
		             $result = "array(";
		             while (list($k, $v) = each($a))
		                     $result .= "'$k' => ".var_export($v).", ";
		             $result .= ")";
		             break;
		     case "string":
		             $result = "'".addslashes($a)."'";
		             break;
		     case "boolean":
		             $result = ($a) ? "true" : "false";
		             break;
		     default:
		             $result = $a;
		             break;
		}
		return $result;
	}
}

if( !function_exists('dngettext')){
    function dngettext ( $domain , $msgid1 , $msgid2 , $n ){
        if($n>1){
            return dgettext($domain,$msgid2);
        }else{
            return dgettext($domain,$msgid1);
        }
    }
}

// Affiche un message
function alert($data) {
	?>
	<script type="text/javascript">
	    alert('<?php echo _secho($data,"javascript"); ?>');
	</script>
	<?php
}

function printPre($data,$echo=true) {
    if ($echo) {
        echo "<pre>";
		print_r($data);
		echo "</pre>";
        return 0;
    } else{
        $str = "<pre>";
			ob_start();
			print_r($data);
		$str .= ob_end_get_contents();
		$str .= "</pre>";
		return $str;
	}
}

function showInfoHelp($texte, $info) {
    return "<span title=\""._secho($info)."\" style=\"cursor:help;\">"._secho($texte)."<i> (?) </i></span>";
}

function randomHTMLColor() {
    srand((float) time());
    return sprintf("%x",100+33*rand(0,4)).sprintf("%x",100+33*rand(0,4)).sprintf("%x",100+33*rand(0,4));
}

function imagePath($nom) {
    global $conf;
    return $conf["baseURL"]."images/$nom";
}

function javascript($code) {
    return "<script type=\"text/javascript\">$code</script>\n";
}

function stripN($str) {
    return str_replace(Array("\n","\r","\t")," ",$str);
}

function tooltip_init() {
	global $conf;
	echo "<script type=\"text/javascript\" src=\"".$conf['baseURL']."include/tooltip.js\"></script>\n";
	echo "<style type=\"text/css\">div#tooltip {position:absolute; visibility:hidden; z-index:100; background-color:#FFEEC7; border:1px solid black; padding:2px; font-size:8pt; }</style>";
	echo "<div id=\"tooltip\"></div>\n";
}

function tooltip_show($tip, $color="#000000", $bgcolor="#FFEEC7") {
	return " onmouseover=\"tooltip.show(event,this);\" onmouseout=\"tooltip.hide(this);\" title=\""._secho($tip,"input")."\" tooltip_bgcolor=\"$bgcolor\" tooltip_color=\"$color\"";
}

/*****
 *
 *		echo klMessage('error',"Impossible de rajouter cette astreinte/garde");
 *		echo klMessage('error',"Impossible de rajouter ","Erreur regle <a href=dslds>ddsds</a>#".$dataAlarme['id']." ".$dataAlarme['nom'],"");
 *		echo klMessage('warning',"Impossible de rajouter cette astreinte/garde");
 *		echo klMessage('warning',"Impossible de rajouter cette astreinte/garde","Erreur regle #".$dataAlarme['id']." ".$dataAlarme['nom']);
 *		echo klMessage('info',"Impossible de rajouter cette astreinte/garde");
 *		echo klMessage('info',"Impossible de rajouter cette astreinte/garde","Erreur regle #".$dataAlarme['id']." ".$dataAlarme['nom']);
 *
 *****/   
function klMessage($type,$titre,$info="",$infoSecho="all"){
	$str = "";

	switch( $type ){
		case 'error' : $img = 'icoError.gif';break;
		case 'warning' : $img = 'icoWarning.gif';break;
		case 'info' : 
		default:	$img = 'icoInfo.gif';break;
	}
	
	$str .= "<TABLE align=center width=320  cellspacing=0 cellpadding=0 border=0>
				<TR><TD width=6></TD><TD width=94></TD><TD width=60%></TD><TD width=6></TD></TR>
				<TR>
					<TD colspan=2 class=corpsFonce style=\"text-align:left;background-repeat:no-repeat;\"  background=\"".imagePath("skinCoinHautGauche.gif")."\">
						<nobr>&nbsp;<IMG style=\"margin-top:2px\" src=\"".imagePath($img)."\"><b> "._secho($titre,$infoSecho)."</b>&nbsp;</nobr>
					</TD>
					<TD colspan=2 background=\"".imagePath("skinDroite.jpg")."\" style=\"background-repeat:  repeat-y;\" valign=top><img src=\"".imagePath("skinCoinHautDroit.gif")."\" height=\"6\" width=\"6\"></TD>
					</TR>"
				.($info==""?"":"<TR class=corps>
					<TD colspan=3  style=\"padding:2px;padding-left:5px\">
						"._secho($info,$infoSecho)."
					</TD>
					<td valign=\"top\" background=\"".imagePath("skinDroite.jpg")."\" class=blanc style=\"background-repeat: repeat-y;\" width=\"6\"><img src=\"".imagePath("skinCoinHautDroit.gif")."\" height=\"6\" width=\"6\"></td>
					</TR>")
				."
				<tr>
					<td height=\"6\" background=\"".imagePath("skinBas.jpg")."\"><img src=\"".imagePath("skinCoinBasGauche.gif")."\" height=\"6\" width=\"6\"></td>
					<td background=\"".imagePath("skinBas.jpg")."\" height=\"6\"><img height=\"6\" src=\"".imagePath("spacer.gif")."\" width=\"1\"></td>"
					.($info==""?"":"<td background=\"".imagePath("skinBas.jpg")."\" height=\"6\"><img height=\"6\" src=\"".imagePath("spacer.gif")."\" width=\"1\"></td>")
					."<td height=\"6\"><img src=\"".imagePath("skinCoinBasDroit.gif")."\" height=\"6\" width=\"6\"></td>"
					.($info!=""?"":"<td ></td>")
					."</tr>
				</TABLE>";
	return $str;
}

function _s($tmp,$rien=""){
	return _secho($tmp,$rien);
}

function _secho($txt,$type="all"){
	if(stripos($type,'2mysql')!==false) $type=str_ireplace('2mysql','ads',$type);
    if(stripos($type,'form')!==false) $type=str_ireplace('form','hen',$type);
    if(stripos($type,'input')!==false) $type=str_ireplace('input','hen',$type);
    if(stripos($type,'url')!==false) $type=str_ireplace('url','ure',$type);
    if(stripos($type,'javascript')!==false) $type=str_ireplace('javascript','hseads',$type);
    if(stripos($type,'vbs')!==false) {
		 $txt=str_ireplace("'","'&chr(39)&'",$txt);
		 $txt=str_ireplace('"',"'&chr(34)&'",$txt);
	}
    if(stripos($type,'vb2')!==false) {
		 $txt=str_ireplace('"',"\"&chr(34)&\"",$txt);
		 $txt=str_ireplace("'","\"&chr(39)&\"",$txt);
	}

    if(stripos($type,'hde')!==false) $txt=html_entity_decode($txt);
	if(stripos($type,'hsc')!==false && stripos($type,'hen')===false && stripos($type,'all')===false) $txt=htmlspecialchars($txt,ENT_QUOTES);
	if(stripos($type,'hen')!==false || stripos($type,'all')!==false) $txt=htmlentities($txt,ENT_QUOTES);
	if(stripos($type,'hse')!==false && stripos($type,'hee')===false) $txt=htmlspecialchars($txt,ENT_COMPAT);
	if(stripos($type,'hee')!==false) $txt=htmlentities($txt,ENT_COMPAT);
	if(stripos($type,'n2b')!==false || stripos($type,'all')!==false) $txt=nl2br($txt);
	if(stripos($type,'ure')!==false) $txt=urlencode($txt);
	if(stripos($type,'ads')!==false) $txt=addcslashes($txt,"\n\r\t\'\"");
	if(stripos($type,'asl')!==false) $txt=addslashes($txt);
	if(stripos($type,'sts')!==false) $txt=stripslashes($txt);
	if(stripos($type,'nbsp')!==false) $txt=str_replace(" ","&nbsp;",$txt);
	if(stripos($type,'mail')!==false) $txt="<a href=\"mailto:".$txt."\">".$txt."</a>";

	return $txt;

}

function getInfoLaboratoire() {
	$laboNom = getSrOption('laboNom');
	$laboAddresse = getSrOption('laboRue1') . getSrOption('laboRue2');
	$laboCodePostal = getSrOption('laboCodePostal');
	$laboVille = getSrOption('laboVille');
	$laboTelephone = getSrOption('laboTelephone');
	return Array($laboNom,$laboAddresse,$laboCodePostal,$laboVille,$laboTelephone);
}

function getSrOption($opt) {
	global $_SESSION;
	if(!isset($_SESSION['KALIRES_OPTION'][$opt])) {
		$sc = new SoapClientKalires();
		$options = $sc->getSrOptions();
		foreach(get_object_vars($options) as $option=>$value) {
			$_SESSION['KALIRES_OPTION'][$option] = $value;
		}
	}
	return $_SESSION['KALIRES_OPTION'][$opt];
}

function getSiteOption($opt, $site) {
	global $_SESSION;
	if(!isset($_SESSION['KALIRES_OPTION_SITE'][$site][$opt])) {
		$sc = new SoapClientKalires();
		$_SESSION['KALIRES_OPTION_SITE'] = $sc->getSiteOptions();
	}
	return $_SESSION['KALIRES_OPTION_SITE'][$site][$opt];
}

function isSrOption($opt,$value) {
	return (getSrOption($opt) == $value);
}

function mkPassKaliRes($longueur) {  
	for ($i = 1; $i <= $longueur; $i++) {
		$rand = rand (1,2);  
		if ($rand == 1) {  
			$pass .= rand (1,9);  
		} elseif ($rand == 2) {  
			$pass .= strtolower (chr (rand (97,122)));  
		}  
	}  
	$pass = str_replace("o","a",$pass);  
	return $pass;
}

function isDebug() {
    global $conf,$userLogged,$_SERVER;
    if(!is_array($_SERVER['argv'])) $_SERVER['argv'] = Array();
    return ($conf['debug'] || (!in_array('CRON',$_SERVER['argv']) && $userLogged->id == "1" && file_exists($conf["baseDir"]."DEBUG_EN_COURS")) );
}

function klErrorPrint($txt="") {
	global $conf;
	if(isDebug()) {
		$err = debug_backtrace();
		$str = "<table class=bBorder style=\"border-color:red;\">";
		$str .= "<tr><td colspan=3 class=rouge style=\"font-size:12px;\"><b>$txt</b></td></tr>";
		for($i=1;$i<count($err);$i++) {
			$e = $err[$i];
			$style = "";
			if($i == 1) $style = "color:red;";
			$str .= "<tr><td class=descrGrand style=\"$style\">".str_ireplace($conf["baseDir"],"",$e["file"])."</td><td class=descrGrand style=\"$style\" align=right><b>".$e["line"]."</b></td><td class=descrGrand style=\"$style\">".(($e["class"]!="")?($e["class"]." : "):("")).$e["function"]."(".implode(", ",$e["args"]).")</td></tr>";
		}
		$str .= "</table>";
	}
	return $str;
}

function kdDebug($txt,$login="",$token="",$session="") {
	$prefix = Array();
	if($login != "") $prefix[]=$login;
	if($token != "") $prefix[]=substr($token,0,5);
	if($session != "") $prefix[]=substr($session,0,5);
	@file_put_contents("/var/log/kalires/kalidom.log", "[".date("Y-m-d H:i:s")."] ".((count($prefix)>0)?("[".implode('-',$prefix)."] "):("")).$txt."\n", FILE_APPEND);
}

function returnFunction($data) {
	return $data;
}

function encodePassword($type,$pass) {
	switch($type) {
		case "patient":
			return hash("sha256","£*gn°".substr($pass,0,3)."60.Pm".substr($pass,3)."Az*+");
			break;
		case "demandeur":
		case "medecin":
		case "correspondant":
		case "preleveur":
		case "preleveurDom":
			return hash("sha256","?.&P".substr($pass,0,1)."mm0HG".substr($pass,1)."è^ *");
			break;
	}
}

function encodeUserToken($token,$niveau,$login,$password) {
	if($token != "") {
		$token = sha1("0(!".$token.";Jy".$niveau."l-à".$login."+B,".$password."oO=");
	}
	return $token;
}

function navGetTab($navId,$data,$options=""){ 
	$selected = false;

	if(!is_array($data)) return "";
	
	$nbrColonne=count($data);
	$taille=floor(99/$nbrColonne);
	
	foreach($data as $i => $tab){	$selected = $selected || $tab['selected'];	}
	if(!$selected) $data[0]['selected']=true;

	$str="<TABLE  $options cellspacing=0 cellpadding=0><TR height=11>";
		
	foreach($data as $i => $tab){
		if(isset($tab['onClick'])) $onClick=$tab['onClick'];
		else $onClick=$tab['onBeforeClick']."klNavTabSwitch('".$navId."',$i);";
		$str .=	"<TD width=\"$taille%\" class=\"tab-".($tab['selected']?"on":"off")." ".($tab['selected']?"":"corpsFonce")."\" name=\"".$navId."navcell\" id=\"".$navId."navcell\"   onClick=\"".$onClick.";\" >".$tab['name']."</TD>";	
	}
	$str.="<TD class=tab-none >&nbsp;</TD></TR><TR><TD colspan=\"".(count($data)+1)."\" align=center width=\"100%\" class=\"tab-core\" valign=top>";

	foreach($data as $i => $tab){	$str .=	"<table cellspacing=\"5\" width=\"100%\" name=\"".$navId."tb\" id=\"".$navId."tb\" class=\"tab-content ".(!$tab['selected']?"hide":"show")."\" ><tr><td align=center>".$tab['data']."</td></tr></table>";	}
	
	$str.="</TD></TR></TABLE>";

	return $str;
}

function afficheNombre($nombre,$nbrChiffreApresVirgule=2) {
	$nombre=str_replace(",",".",$nombre);
	$nombre=@number_format($nombre,$nbrChiffreApresVirgule,'.','');
	return $nombre;
}

function afficheNombreCourt($nombre) {
	$nombre=str_replace(",",".",$nombre);
	if ($nombre>0) $nombre=number_format($nombre,4,'.','');
	if (strpos($nombre,".")) $nombre=trim($nombre,"0");
	if (strpos($nombre,".")==(strlen($nombre)-1)) $nombre.="00";
	return $nombre;
}


function getDemandeStatusStr($status){
	switch($status){
		case 'saisie': 
		case 'enCours': 
		case 'complet': $str = "<font color=\"#ff6600\">"._s("En cours")."</font>"; break;
		case 'valide': 
		case 'valideValab': $str = "<font color=\"#009933\">"._s("Validée")."</font>"; break;
	}
	return $str;
}

function getPCStatusStr($status){
	switch($status){
		case 'saisie': $str = "<font color=\"#00349a\">"._s("Saisie")."</font>"; break;
		case 'valide': $str = "<font color=\"#009933\">"._s("Validée")."</font>"; break;
	}
	return $str;
}

function logWithSha($arg=Array()){
	global $patientLogged;
	if(isset($arg["sha"]) && isset($arg["login"])){
		$_SESSION["accesPermalink"] = 1;
		$patientLogged = new PatientLogged( $arg["login"] , $arg["sha"], "", "demandeur", true, $arg["user"]);
		if ($patientLogged && $patientLogged->isLogin()) {
			$_SESSION["accesPermalinkNum"] = $_GET["numId"];
			$_SESSION["accesPermalinkNumIPP"] = $_GET["numIPP"];
			$patientLogged->loadAccesSession();
			return true;
		}else{
			return false;
		}	
	}else{
		return false;
	}
	
}

function entete($menu="",$menuSite=true) {
	global $conf;
	list($laboNom,$laboAddresse,$laboCodePostal,$laboVille,$laboTelephone) = getInfoLaboratoire();
	
	$backGroundLogo = Kfile::getUrl("logo/logo-kalires-TN.jpg",false,true,true);
	if($backGroundLogo === false) {
		$backGroundLogo = $conf["baseURL"]."images/logo-kalires-default.jpg";
	}
?>
		<div id="bodyWrap">
			<div class="pageWrapper">
				<div id="header">
					<div id="logo" style="filter:alpha(Opacity=60);-moz-opacity:0.6">
					</div>
					<div id="logo2" style="background: url('<?=$backGroundLogo?>') no-repeat center;">
					</div>
					<div id="heading">
						<div class="head"></div>
						<div class="top">
							<?php include($conf['baseDir']."menuTop.php")?>
						</div>
						<div class="sub">
							<?php include($conf['baseDir']."menuTop2.php")?>
						</div>
					</div>
				</div>
			</div>
			
			<div class="pageWrapper" id="main">
				
				<div id="mainOuter">
					<div id="mainInner">
						<div class="left" style="padding-left: 1px">
							<ul>								
						
                <?php
									$hr = false;
									
									if (isset($patientLogged) && $patientLogged->isAuth()) {
										if(!$_SESSION["accesPermalink"] || $_SESSION["accesPermalinkLevel"] == 1 || $_SESSION["accesPermalinkLevel"] == 2){
											echo "<li><a href='".$conf["baseURL"]."consultation.php'><img border=0 width=16 src=\"images/liste16.gif\"> "._s("Liste des demandes")."</a></li>";
											if(!$_SESSION["accesPermalink"] || $_SESSION["accesPermalinkLevel"] == 1){
												if ($_SESSION["accesPC"]) {
													echo "<li id=\"newPresc\" >
														 	<a href='".$conf["baseURL"]."prescription.php'><img border=0 width=13 src=\"images/icoaddtoutpetit.gif\"> &nbsp;"._s("Nouvelle prescription")."</a>
														 </li>
														 <li id=\"listePresc\" >
													 	<a href='".$conf["baseURL"]."listePrescription.php'><img border=0 width=16 src=\"images/hprimFiche.gif\"> "._s("Liste des prescriptions")."</a>
													 </li>";
												}
												if($_SESSION["refAnalyse"] > 0) {



													echo "<li><a href='".$conf["baseURL"]."referentiel.php'><img border=0 width=16 src=\"images/icodico.gif\"> "._s("Référentiel d'analyses")."</a></li>";
												}

											}										
											echo "<li><a href='".$conf["baseURL"]."changePassword.php'><img border=0 src=\"images/option.gif\"> "._s("Options")."</a></li>";
											echo "<li><a href='".$conf["baseURL"]."index.php?logout=1'><img border=0 src=\"images/logout16.gif\"> "._s("Déconnexion")."</a></li>";
											$hr = true;
										}
									}
									
									if( $hr ) echo "<hr>";
									$hr = false;

									if(is_array($menu)){
										foreach($menu as $tmp){
											global $SCRIPT_NAME;
											if(basename($SCRIPT_NAME) == $tmp['lien']) $class = "active";
											else $class = "";
											if( $tmp['target'] == 'remote' ){
												echo "<li class=\"$class\"><a href=\"#\" onClick=\"makeRemote('print','".$tmp['lien']."',800,600);return false;\">".$tmp['nom']."</a></li>";
											}else{
												echo "<li class=\"$class\"><a href=\"".$tmp['lien']."\">".$tmp['nom']."</a></li>";
											}
										}
										$hr = true;
									}	
									if( $hr ) echo "<hr>";
								?>
							</ul>
								<?
									if( $menuSite ){
										global $content;
										$content = "resultat";
										include($conf['baseDir']."menuLeft.php");
									}
								?>
								<div class="clear mozclear"></div>
						</div>
						<div id="content"><br />


<?
}

function afficheBorneImg($b){
	switch($b){
		case '1':
			return "borneDepasseHaut.gif";
		case '-1':
			return "borneDepasseBas.gif";
		case '0':
			return "borneNormale.gif";
	}
	return $b;
}

function formateTexteAccueil($txt) {
	$txt = nl2br($txt);
	$txt = str_replace("\r\n","\n",$txt);
	$txt = str_replace("\n","",$txt);
	$txt = changeTxtAna($txt);
	$replace = Array(
		"[[siteNom]]"				=> getSrOption("laboNom"),
		"[[siteRue1]]"				=> getSrOption("laboRue1"),
		"[[siteRue2]]"				=> getSrOption("laboRue2"),
		"[[siteCodePostal]]"		=> getSrOption("laboCodePostal"),
		"[[siteVille]]"				=> getSrOption("laboVille"),
		"[[siteTel]]"				=> getSrOption("laboTelephone"),
		"[[siteMail]]"				=> getSrOption("laboMail"),
		"[[lienPdf]]"				=> "<A href=\"http://www.adobe.com/fr/products/reader/\">Adobe Reader</A>",
		"[[lien]]"					=> "<A style='color: #163477;text-decoration: none;' HREF='".getSrOption("kaliResURL")."'><B>".getSrOption("kaliResURL")."</B></A>",
		"[[typeLogin]]"				=> ((getSrOption("loginPatient")=="numSecu")?("Numéro de sécurité sociale"):("Numéro patient")),
		"[[dureePasswordPatient]]"	=> getSrOption("validPassPerso")
	);
	foreach($replace as $key => $val) {
		$txt = str_replace($key,$val,$txt);
	}
	return $txt;
}

function changeTxtAna($str,$vide=false) {
	if($vide) {
		$str=str_replace("[b0]","",$str);
		$str=str_replace("[b]","",$str);
		$str=str_replace("[i0]","",$str);
		$str=str_replace("[i]","",$str);
		$str=str_replace("[ul0]","",$str);
		$str=str_replace("[ul]","",$str);
		$str=str_replace("[super0]","",$str);
		$str=str_replace("[super]","",$str);
		$str=str_replace("[sub0]","",$str);
		$str=str_replace("[sub]","",$str);
	}else {
		$str=str_replace("[b0]","</B>",$str);
		$str=str_replace("[b]","<B>",$str);
		$str=str_replace("[i0]","</I>",$str);
		$str=str_replace("[i]","<I>",$str);
		$str=str_replace("[ul0]","</U>",$str);
		$str=str_replace("[ul]","<U>",$str);
		$str=str_replace("[super0]","</SUP>",$str);
		$str=str_replace("[super]","<SUP>",$str);
		$str=str_replace("[sub0]","</SUB>",$str);
		$str=str_replace("[sub]","<SUB>",$str);
	}
	return $str;
}
	
function tronquer($chaineLongue, $longueurMax=75, $cesure='...', $tooltip=false, $tooltipSupp = "") {
	if ( strlen($chaineLongue) > $longueurMax ) {
		$chaineCourte = substr($chaineLongue, 0, $longueurMax-strlen($cesure)) . $cesure;
		if ( $tooltip ) {
			$chaineCourte = '<span class="hand" '.tooltip_show($tooltipSupp.$chaineLongue).'>'.$chaineCourte.'</span>';
		}
		return $chaineCourte;
	}
	else {
		return $chaineLongue;
	}
}

function filterDemandes(&$lesDemandes, $lesFiltres) {
	$resTab = Array();
	$nb = $total = 0;
	$filterLog = 'Application du FILTRE' . PHP_EOL;
	if ($lesFiltres["orderIntervenant"] > 0) {
		if(is_array($lesDemandes)) foreach($lesDemandes as $key => $demParInt) {
			$resTab = Array();
			if(is_array($demParInt)) foreach ($demParInt as $data) {
				if($nb >= 100) continue;
				$filterLog .= "Demande " . $data['numDemande'] . PHP_EOL;
				$nb = sortDemandes($data, $lesFiltres, $resTab);
			}
			$lesDemandes[$key] = doUsort($resTab, $lesFiltres);
			$total += count($resTab);
		}
	} else {
		if(is_array($lesDemandes[0])) foreach($lesDemandes[0] as $data) {
			if($nb >= 100) continue;
			$filterLog .= "Demande " . $data['numDemande'] . PHP_EOL;
			$nb = sortDemandes($data, $lesFiltres, $resTab);
		}
		$lesDemandes[0] = doUsort($resTab, $lesFiltres);
		$total = count($resTab);		
	}
	echo "<!-- $filterLog -->";
	return $total;
}

function doUsort($resTab, $lesFiltres) {
	if (!empty($lesFiltres['sort'])) {
		if ($lesFiltres['sort'] == 'date' || $lesFiltres['sort'] == '') {
			usort($resTab, 'filterDemandesTriDate');
		}
		if ($lesFiltres['sort'] == 'dossier') {
			usort($resTab, 'filterDemandesTriDossier');
		}
		if ($lesFiltres['sort'] == 'patient') {
			usort($resTab, 'filterDemandesTriPatient');
		}
	}
	return $resTab;
}

function sortDemandes($data, $lesFiltres, &$resTab) {
	$get = true;
	foreach($lesFiltres as $field=>$value) {
		if (!empty($value)) {
			if ($field == 'nomPatient' && !preg_match('/'.$value.'/i', $data[$field])) { $get = false; break; }
			if ($field == 'prenomPatient' && !preg_match('/'.$value.'/i', $data[$field])) { $get = false; break; }
			if ($field == 'etat' && $value == 'enCours')
				if ($data["status"] != 'saisie' && $data["status"] != 'enCours' && $data["status"] != 'complet') { $get = false; break; }
			if ($field == 'etat' && $value == 'valide')
				if ($data["status"] != 'valide' ) { $get = false; break; }
			if ($field == 'numDemande' && !preg_match('/'.$value.'/i', $data['numDemande']) && !preg_match('/'.$value.'/i', $data['numDemandeExterne']) && !preg_match('/'.$value.'/i', $data['numPermanentExterne'])) { $get = false; break; }
			if ($field == 'idMedecin' && !isset($data["medecins"][$value])) { $get = false; break; }
			if ($field == 'idCorrespondant' && !isset($data["correspondants"][$value])) { $get = false; break; }
			if ($field == 'codeChapitre') {
				$chapitreTrouve = false;
				foreach($data['analyses'] as $lesAnalyses) {
					if ($lesAnalyses['codeChapitre'] == $value)  $chapitreTrouve = true;
				}					
				if (!$chapitreTrouve) { $get = false; break; }
			}
			if ($field == 'consulte' && $value == 'dejaVu' && empty($data["dateVisu"])) { $get = false; break; }
			if ($field == 'consulte' && $value == 'pasVu'  && !empty($data["dateVisu"])) { $get = false; break; }	
			
			if ($field == 'dateDebut' && $data['demandeDate']<saisieDate($value) ) { $get = false; break; }	
			if ($field == 'dateFin' && $data['demandeDate']>saisieDate($value) ) { $get = false; break; }	
		}
	}
	if ($get) {
		$filterLog .= "On prends la demande ". PHP_EOL;
		$resTab[] = $data;
	} else {
		$filterLog .= "$field ne corresponds pas" . PHP_EOL;
	}
	
	return count($resTab);
}

function filterDemandesTriDate( $a, $b ) {
	if ($a['demandeDate']<$b['demandeDate']) return 1;
	elseif ($a['demandeDate']>$b['demandeDate']) return -1;
	else {
		if ($a['preleveHeure']<$b['preleveHeure']) return 1;
		elseif ($a['preleveHeure']>$b['preleveHeure']) return -1;
	}
	return 0;
}

function filterDemandesTriDossier( $a, $b ) {
	if ($a['numDemande']>$b['numDemande']) return 1;
	elseif ($a['numDemande']<$b['numDemande']) return -1;
	else return 0;
}

function filterDemandesTriPatient( $a, $b ) {
	if ($a['nomPatient']>$b['nomPatient']) return 1;
	elseif ($a['nomPatient']<$b['nomPatient']) return -1;
	else {
		if ($a['prenomPatient']<$b['prenomPatient']) return 1;
		elseif ($a['prenomPatient']>$b['prenomPatient']) return -1;
	}
	return 0;
}

function afficheAnalyses($tabAnalyses, $filter = Array()) {
	$return = "";
	if (isset($filter['codeChapitre'])) $codeChapitre = $filter['codeChapitre'];
	else  $codeChapitre = '';
	$space = "";
	foreach($tabAnalyses as $analyse) {
		if (!empty($codeChapitre) && $analyse['codeChapitre']!=$codeChapitre) continue; // On ne prends pas les autres chapitres
		
		$class = "analyse";
		if ($analyse['new']) {
			$class .= " analysenew";
		} elseif ($analyse['valide']) {
			$class .= " analysevalide";
		}
		if ($analyse['horsBorne']) {
			$class .= " analysehorsborne";
		}
		
		$return .= $space . '<span class="'.$class.'">'.$analyse['codeAnalyse'].'</span>';
		$space = "&nbsp;&middot; ";
		
	}
	return $return;
}

function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

function listeCodePrescription($tabAnalysePresc=array(),$dimensionSup=false) {
	$tabAnalyse = Array();
	if(is_array($tabAnalysePresc) && count($tabAnalysePresc)>0) {
		for($iAnalyse=0 ; $iAnalyse < count($tabAnalysePresc) ; $iAnalyse++) { 
			for($iActes=0 ; $iActes < count($tabAnalysePresc[$iAnalyse]['actes']) ; $iActes++) { 
				if (!$dimensionSup) $tabAnalyse[] = $tabAnalysePresc[$iAnalyse]['actes'][$iActes];
				else $tabAnalyse[]["codeAnalyse"] = $tabAnalysePresc[$iAnalyse]['actes'][$iActes]; 
			} 
		}
		
		if(count($tabAnalyse) > 0) {
			return $tabAnalyse;
		} else {
			return  false;
		}
	} else {
		return false;
	}
}

function stripslashesRecurse($entry) {
	if(is_string($entry)) return stripslashes($entry);
	else if(is_array($entry)) {
		foreach($entry as $k => $e) {
			$entry[$k] = stripslashesRecurse($e);
		}
		return $entry;
	}
	
}

function getMonnaie($type="sigle") {
	global $conf;
	$monnaie["type"] = "france"; // "suisse" pour les octante, nonante .. ou "belge" pour les billons, trillons ..
	if (isset($conf["monnaieSigle"]) && $conf["monnaieSigle"]!="") {
		$monnaie["sigle"]=$conf["monnaieSigle"];
		$monnaie["txt"]=$conf["monnaieTxt"];
		$monnaie["html"]=$conf["monnaieHtml"];
		if($conf["monnaieType"] != "") $monnaie["type"] = $conf["monnaieType"];
		if ($conf["monnaieChar"]>0 && $conf["monnaieChar"]<256) $monnaie["char"]=chr($conf["monnaieChar"]);
		else $monnaie["char"]=$conf["monnaieChar"];
	}
	else {
		$monnaie["sigle"]="";
		$monnaie["txt"]="euros";
		$monnaie["html"]="&euro;";
		$monnaie["char"]=chr(128);
		$monnaie["type"] = "france";
	}
	if($type == "all") {
		return $monnaie;
	} else {
		return $monnaie[$type];
	}
}

function res_json_encode( $data ) {            
    if( is_array($data) || is_object($data) ) { 
        $islist = is_array($data) && ( empty($data) || array_keys($data) === range(0,count($data)-1) ); 
        
        if( $islist ) { 
            $json = '[' . implode(',', array_map('res_json_encode', $data) ) . ']'; 
        } else { 
            $items = Array(); 
            foreach( $data as $key => $value ) { 
                $items[] = res_json_encode("$key") . ':' . res_json_encode($value); 
            } 
            $json = '{' . implode(',', $items) . '}'; 
        } 
    } elseif( is_string($data) ) { 
        # Escape non-printable or Non-ASCII characters. 
        # I also put the \\ character first, as suggested in comments on the 'addclashes' page. 
        $string = '"' . utf8_encode(addcslashes($data, "\\\"\n\r\t/" . chr(8) . chr(12))) . '"';
        $json    = ''; 
        $len    = strlen($string); 
        # Convert UTF-8 to Hexadecimal Codepoints. 
        for( $i = 0; $i < $len; $i++ ) { 
            
            $char = $string[$i]; 
            $c1 = ord($char); 
            
            # Single byte; 
            if( $c1 <128 ) { 
                $json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1); 
                continue; 
            } 
            
            # Double byte 
            $c2 = ord($string[++$i]); 
            if ( ($c1 & 32) === 0 ) { 
                $json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128); 
                continue; 
            } 
            
            # Triple 
            $c3 = ord($string[++$i]); 
            if( ($c1 & 16) === 0 ) { 
                $json .= sprintf("\\u%04x", (($c1 - 224) <<12) + (($c2 - 128) << 6) + ($c3 - 128)); 
                continue; 
            } 
                
            # Quadruple 
            $c4 = ord($string[++$i]); 
            if( ($c1 & 8 ) === 0 ) { 
                $u = (($c1 & 15) << 2) + (($c2>>4) & 3) - 1; 
            
                $w1 = (54<<10) + ($u<<6) + (($c2 & 15) << 2) + (($c3>>4) & 3); 
                $w2 = (55<<10) + (($c3 & 15)<<6) + ($c4-128); 
                $json .= sprintf("\\u%04x\\u%04x", $w1, $w2); 
            } 
        } 
    } else { 
        # int, floats, bools, null 
        $json = strtolower(var_export( $data, true )); 
    } 
    return $json; 
}

function _mime_content_type($filename) {
	$mime_types = array(
		'txt' => 'text/plain',
		'htm' => 'text/html',
		'html' => 'text/html',
		'php' => 'text/html',
		'css' => 'text/css',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'xml' => 'application/xml',
		'swf' => 'application/x-shockwave-flash',
		'flv' => 'video/x-flv',

		// images
		'png' => 'image/png',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif',
		'bmp' => 'image/bmp',
		'ico' => 'image/vnd.microsoft.icon',
		'tiff' => 'image/tiff',
		'tif' => 'image/tiff',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',

		// archives
		'zip' => 'application/zip',
		'rar' => 'application/x-rar-compressed',
		'exe' => 'application/x-msdownload',
		'msi' => 'application/x-msdownload',
		'cab' => 'application/vnd.ms-cab-compressed',

		// audio/video
		'mp3' => 'audio/mpeg',
		'qt' => 'video/quicktime',
		'mov' => 'video/quicktime',

		// adobe
		'pdf' => 'application/pdf',
		'psd' => 'image/vnd.adobe.photoshop',
		'ai' => 'application/postscript',
		'eps' => 'application/postscript',
		'ps' => 'application/postscript',

		// ms office
		'doc' => 'application/msword',
		'docx' => 'application/vnd.openxmlformats',
		'pptx' => 'application/vnd.openxmlformats',
		'xlsx' => 'application/vnd.openxmlformats',
		'rtf' => 'application/rtf',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',

		// open office
		'odt' => 'application/vnd.oasis.opendocument.text',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
	);

	$mimeType=false;

	// 1. recherche "Maison"
	if($mimeType==false) {
		$ext = strtolower(array_pop(explode('.',$filename)));
		if(array_key_exists($ext, $mime_types))	$mimeType = $mime_types[$ext];
	}

	// 2. mime_content_type
	if($mimeType==false && function_exists('mime_content_type')) $mimeType=mime_content_type($filename);

	// 3. finfo_file
	if($mimeType==false && function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME);
		$mimeType = finfo_file($finfo, $filename);
		finfo_close($finfo);
	}

	// 4. valeur par défaut
	if($mimeType==false) $mimeType = 'application/octet-stream';

	return $mimeType;
}

?>
