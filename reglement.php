<?                                  
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");  
header("Cache-Control: no-cache, must-revalidate");  
header("Pragma: no-cache");

define("KALILAB_SESSION_NO_UPDATE","1");

include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

unset($_SESSION['KALIRES_OPTION']);
unset($_SESSION['KALIRES_OPTION_SITE']);

$goToDeny = false;
$sMsg = '';

$type = getSrOption('interfacePaiement');
$urlPaiement = getSrOption('urlPaiement');

if((($type == "CBI" && $urlPaiement != "") || isTypePaiementOk($type)) && getSrOption("offlinePaiement") > 0) {

} else {
	afficheHead(_s("Serveur de résultats")." - ".getSrOption("laboNom"),"",false);
	entete();
	klRedir("denied.php",5,"<span style=\"color:red;\">"._s("Accès non autorisé")."</span>");
	afficheFoot();
	die();
}

unset($_SESSION["paiementOffline"]);

if($_POST['choix'] == 'regler') {
	if($_SESSION["keyForm"] != "" && $keyFormForm == $_SESSION["keyForm"]) {
		if($_POST['numDemande'] != '' && $_POST['dateNaissance'] != '' && $_POST['email'] != '') {
			$scd = new SoapClientKalires();
			$params = Array(
				 'numDemande' => $_POST['numDemande']
				,'dateNaissance' => $_POST['dateNaissance']
			);
			$ret = $scd->reglementDemandeOffline($params);
			if($ret !== false) {
				if($ret['status'] == 'valide') {
					$idSite = isset($ret["idSite"]) ? $ret["idSite"] : 0;
					$urlPaiement = getStrPaiement($ret['id'],$ret['numDemande'],$ret['reste'],$idSite,'redirect',$_POST['email'],$ret['idPatient']);
					if($urlPaiement != '') {
						$_SESSION["paiementOffline"] = 'ok';
						afficheHead(_s("Serveur de résultats")." - ".getSrOption("laboNom"),"",false);
						entete();
						if($type == "CBI") {
							klRedir($urlPaiement,1,_s('Redirection en cours...'));
						} else {
							echo $urlPaiement;
						}
						afficheFoot();
						die();
					} else {
						$sMsg = '<font color="red">'._s("Erreur : Impossible de trouver la demande correspondante. Merci de vérifier les informations saisies ou de contacter le laboratoire.").'</font>';
					}
				} else {
					$sMsg = '<font color="blue">'._s("Votre demande n'est pas encore validée, merci de ré-essayer ultérieurement.").'</font>';
				}
			} else {
				$sMsg = '<font color="red">'._s("Erreur : Impossible de trouver la demande correspondante. Merci de vérifier les informations saisies ou de contacter le laboratoire.").'</font>';
			}
		} else {
			$sMsg = '<font color="red">'._s("Erreur : Des informations sont manquantes. Merci de vérifier les informations saisies.").'</font>';
		}
	} else {
		$sMsg = '<font color="red">'._s("Erreur : Session expirée. Merci de renseigner à nouveau les informations saisies").'</font>';
	}
}

if($sMsg != '') {
	afficheHead(_s("Serveur de résultats")." - ".getSrOption("laboNom"),"",false);
	entete();
	klRedir("denied.php?origin=reglement.php",4,$sMsg);
	afficheFoot();
	die();
}

afficheHead(_s("Paiement en ligne")." - ".getSrOption("laboNom"),"",false);
entete();

unset($_SESSION["keyForm"]);

$keyForm = uniqid(date("YmdHis"));
$_SESSION["keyForm"] = $keyForm;

?>
<H1><?=_s("Paiement en ligne")." - ".getSrOption("laboNom");?></H1>
<form method="POST" action="reglement.php" name="principal">
<input type=hidden name=choix value='regler'>
<input type=hidden name=keyFormForm value='<?=$keyForm;?>'>
	<table class="corps" align=center cellpadding="8" cellspacing="0" border="0" style=" background-color:#F0F3F2;" width=95%>
		<TR>
			<TD width=100%>
				<table class="corps" align=left cellpadding="4" cellspacing="3" border="0" style="border:1px solid #bbb;width:450px;">
					<tr class=titre><td align=center colspan=2><b><?=_s("Renseignements à saisir");?></b></td></tr>
					<tr><td align=right><?=_s('Référence du dossier');?> : </td><td><input size=30 type="text" value="<?=$numDemande?>" name="numDemande" autocomplete="off" ><img src="<?=imagePath("help.gif");?>" title="<?=_s("Ce numéro peut être trouvé sur le compte-rendu de résultat ou sur la facture que vous avez reçu.");?>"/></td></tr>
					<tr><td align=right><?=_s("Date de naissance");?> : <br /><span style="font-style:italic;font-size:10px;"><?=_s('format JJ-MM-AAAA')?></span></td><td><?=navGetInputDate(Array("id" => "dateNaissance", "name" => "dateNaissance", "dataType" => "date","value" => ''),true,false,true,false,true)?></td></tr>
					<tr><td align=right><?=_s('Adresse e-mail');?> : </td><td ><input size=30 type="text" value="" name="email" autocomplete="off" ></td></tr>
					<tr><td align="center" colspan=2><input type="submit" name="send" value="<?=_s("Régler votre demande");?>"></td></tr>
				</table>
			</TD>
		</TR>
	</TABLE>
</form>
<?

afficheFoot();
?>
