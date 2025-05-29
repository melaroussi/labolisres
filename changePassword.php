<?                                  
 /**                                
  * Page d'identification et de consultation des résultats                     
  *                                 
  *        		                 
  * @package KaliLab                
  * @module KaliLab                
  * @author Netika <info@netika.net>
  * @tests
  * @cvs
  * @tables
  **/                               
?><?

include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

$typeDestinataire=$patientLogged->niveau;

afficheHead(_s("Options du compte")." - ".getSrOption("laboNom"),"",true);
filtrageAcces("patient","index.php","index.php");

entete();

if($choix == "changePassword") {
	if($_SESSION["keyForm"] != "" && $keyFormForm == $_SESSION["keyForm"]) {
		if($sPasswordOld != "" && $sPassword1 != "" && $sPassword2 != "") {
			if(strlen($sPassword1) >= 5) {
				if($sPassword1 == $sPassword2) {
					$sc = new SoapClientKalires();
					$changePassw = $sc->changePassword($sNiveau, $sLogin, $sPasswordOld, $sPassword1);
					if($changePassw->result == "1") {
						$sMsg=""._s("Le nouveau mot de passe a bien été enregistré")."";
					} elseif($changePassw->result == "2") {
						$sMsg="<font color=red>"._s("Erreur : le nouveau mot de passe doit être différent de l'ancien")."</font>";
					} else {
						$sMsg="<font color=red>"._s("Erreur : le changement de mot de passe a échoué")."</font>";
					}
				} else {
					$sMsg="<font color=red>"._s("Erreur : les 2 mots de passe ne sont pas identiques")."</font>";
				}
			} else {
				$sMsg="<font color=red>"._s("Erreur : le mot de passe doit faire au minimum 5 caractères")."</font>";
			}
		} else if($sPassword1 != "" || $sPassword2 != ""){
			$sMsg="<font color=red>"._s("Erreur : veuillez remplir tous les champs requis pour le changement de mot de passe")."</font>";
		}
		
		$optSaved = false;
		if($patientLogged->getOptionUtilisateur("kaliresMail") != $kaliResOpt["kaliresMail"]) {
			$patientLogged->setOptionUtilisateur(Array("kaliresMail"=>($kaliResOpt["kaliresMail"])?$kaliResOpt["kaliresMail"]:"0"),$patientLogged->id,$patientLogged->niveau);
			$br = "";
			if($sMsg != "") $br = "</br>";
			$sMsg.= $br._s("Options personnelles enregistrées")."";
			$optSaved = true;
		}
		
		if($patientLogged->userOption["clearFilter"] != $userOption["clearFilter"]) {
			$patientLogged->setOptionUtilisateur(Array("optionsUtilisateur"=>Array("clearFilter"=>($userOption["clearFilter"])?$userOption["clearFilter"]:"0")),$patientLogged->id,$patientLogged->niveau);
			$patientLogged->userOption["clearFilter"] = $userOption["clearFilter"];
			if(!$optSaved) {
				$br = "";
				if($sMsg != "") $br = "</br>";
				$sMsg.= $br._s("Options personnelles enregistrées")."";
				$optSaved = true;
			}
		}
		
	} else {
		$sMsg="<font color=red>"._s("Erreur : session incorrecte")."</font>";
	}
}
unset($_SESSION["keyForm"]);

afficheMessage($sMsg);
$keyForm = uniqid(date("YmdHis"));
$_SESSION["keyForm"] = $keyForm;
echo "<form name=principal action=\"changePassword.php\" method=post>";
echo "<input type=hidden name=choix value=\"changePassword\">"
	."<input type=\"hidden\" name=\"keyFormForm\" value=\"".$keyForm."\">"
	."<input type=\"hidden\" name=\"sLogin\" value=\"".(($patientLogged->niveau=="patient")?($patientLogged->numPermanent):($patientLogged->numIdentification))."\">"
	."<input type=\"hidden\" name=\"sNiveau\" value=\"".$patientLogged->niveau."\">";
?>
	<table class="corps" align=center cellpadding="2" cellspacing="3" border="0" style="border:1px solid #bbb;" width=500>
		<tr class=titre><td align=center colspan=2><?echo _s("Options personnelles :")?></td></tr>
		<tr><td align=right><?echo _s("Identifiant")?> : </td><td><?=(($patientLogged->niveau=="patient")?($patientLogged->numPermanent):($patientLogged->numIdentification));?></td></tr>
		
		<?			
			if($patientLogged->niveau=="medecin" || $patientLogged->niveau=="correspondant") {			
				echo "<tr>
						<td align=right>"._s("Notification par mail pour chaque demande validée")." : </td>
						<td><label><input TYPE=\"checkbox\" id=\"kaliResOpt[kaliresMail]\" NAME=\"kaliResOpt[kaliresMail]\" VALUE=\"1\" ".(($patientLogged->getOptionUtilisateur("kaliresMail")>0)?("checked"):(""))." > "._s("Oui")."</label></td>
					</tr>";
			}
			if($patientLogged->niveau=="medecin" || $patientLogged->niveau=="correspondant" || $patientLogged->niveau=="preleveur") {			
				echo "<tr>
						<td align=right>"._s("Effacer les filtres de recherche à chaque connexion")." : </td>
						<td><label><input TYPE=\"checkbox\" id=\"userOption[clearFilter]\" NAME=\"userOption[clearFilter]\" VALUE=\"1\" ".(($patientLogged->userOption["clearFilter"]>0)?("checked"):(""))." > "._s("Oui")."</label></td>
					</tr>";
			}
		?>
				
		<tr class=titre><td align=center colspan=2><?echo _s("Changement de mot de passe :")?></td></tr>
		<tr><td align=right><?echo _s("Mot de passe actuel")?> : </td><td><input type="Password" value="" name="sPasswordOld" autocomplete="off" ></td></tr>
		<tr><td align=right><?echo _s("Nouveau mot de passe")?> : </td><td><input type="Password" value="" name="sPassword1" autocomplete="off" ></td></tr>
		<tr><td align=right><?echo _s("Nouveau mot de passe (vérification)")?> : </td><td><input type="Password" value="" name="sPassword2" autocomplete="off" ></td></tr>	
	</table>
	<br/>
	<center><input type="submit" name="send" value="<?echo _s("Enregistrer")?>"></center>
<?
echo "</form>";

afficheFoot();
?>