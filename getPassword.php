<?                                  
include_once ("include/conf.inc.php");
define("KALILAB_SESSION_NO_UPDATE","1");
include_once ("include/lib.inc.php");

afficheHead(getSrOption("laboNom"),"",true);

entete();

if($patientLogged->isAuth()) {
	$patientLogged->logout();
}

$loginPatient = getSrOption("kaliResPatient");
$loginMedecin = (getSrOption("kaliResMedecin")||getSrOption("kaliResCorrespondant")||getSrOption("kaliResPreleveur"));

if( ($sNiveau == "patient" && !$loginPatient) || ($sNiveau == "demandeur" && !$loginMedecin) ) {
	klRedir("index.php",5,_s("Redirection en cours ..."));
	afficheFoot();
	die();
}

if($choix == "changePassword") {
	if($_SESSION["keyForm"] != "" && $keyFormForm == $_SESSION["keyForm"]) {
		if(strlen($sLogin) > 0 && strlen($sMail) > 0) {
			$sc = new SoapClientKalires();
			$changePassw = $sc->regenPassword($sNiveau, $sLogin, $sMail);
			if($changePassw->result == "1") {
				$sMsg=""._s("Le nouveau mot de passe a bien été demandé, vous allez le recevoir par e-mail à l'adresse spécifiée")."";
				afficheMessage($sMsg);
				klRedir("index.php",5,_s("Redirection en cours ..."));
				afficheFoot();
				die();
			} else if($changePassw == "multiAccount") {
				$sMsg="<font color=red>"._s("Erreur : veuillez contacter le laboratoire, plusieurs comptes correspondent à vos identifiants.")."</font>";
			} else {
				$sMsg="<font color=red>"._s("Erreur : le changement de mot de passe a échoué")."</font>";
			}
		} else {
			$sMsg="<font color=red>"._s("Erreur : il faut saisir votre identifiant et adresse e-mail")."</font>";
		}
	} else {
		$sMsg="<font color=red>"._s("Erreur : session incorrecte")."</font>";
	}
}
unset($_SESSION["keyForm"]);

afficheMessage($sMsg);

$keyForm = uniqid(date("YmdHis"));
$_SESSION["keyForm"] = $keyForm;
echo "<form name=principal action=\"getPassword.php\" method=post>";
echo "<input type=hidden name=choix value=\"changePassword\">"
	."<input type=\"hidden\" name=\"keyFormForm\" value=\"".$keyForm."\">"
	."<input type=\"hidden\" name=\"sNiveau\" value=\"".$sNiveau."\">";
?>
	<table class="corps" width=500 align=center cellpadding="2" cellspacing="3" border="0" style="border:1px solid #bbb;">
	<tr class=titre><td align=center colspan=2><?echo _s("Demande de nouveau mot de passe");?> :</td></tr>
	<? if($sNiveau == "patient" && getSrOption("passwordPerso") == 0) { ?>
		<tr class=corpsFonce><td colspan=2 style="font-size:11px;"><?echo _s("Votre mot de passe a expiré");?>.</td></tr>
	<? } ?>
		<tr class=corpsFonce><td colspan=2 style="font-size:11px;"><?echo _s("Veuillez saisir vos identifiant et adresse e-mail avec lesquels vous êtes identifés auprès du laboratoire. Si vous n'avez pas ces informations, merci de contacter le laboratoire.");?></td></tr>
		<tr><td align=right><?echo _s("Identifiant");?> : </td><td><input type="text" value="" name="sLogin" autocomplete="off" ></td></tr>
		<tr><td align=right><?echo _s("Adresse e-mail");?> : </td><td><input type="text" value="" name="sMail" autocomplete="off" ></td></tr>
		<tr><td align="center" colspan=2><input type="submit" name="send" value="<?echo _s("Demander un nouveau mot de passe");?>"></td></tr>
	</table>
<?
echo "</form>";

afficheFoot();
?>