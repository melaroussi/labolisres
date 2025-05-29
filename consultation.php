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
  * @tables options patientDossierFichier patient
  **/                               
?><?

include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

session_start();
$patientLogged = $_SESSION["patientLogged"];

afficheHead(_s("Liste des demandes")." - ".getSrOption("laboNom"),"",true);
filtrageAcces("patient","index.php","index.php");

tooltip_init();

entete();

$affichAnt = getSrOption('affichAnt');
$afficheDossierPaye = getSrOption('afficheDossierPaye');
$afficheDossierPayeMin = getSrOption('afficheDossierPayeMin');
$optionJours = getSrOption('validPass');

function getStrDate($data) {
	if($data["preleveHeure"]!="00:00:00" && $data["preleveDate"]!="0000-00-00") {
		$dateReference = $data['preleveDate'];
		$heureReference = substr($data["preleveHeure"],0,-3);
		$strTooltip = sprintf(_s("Date de prélèvement : %s"),afficheDate($dateReference)." ".$heureReference);
	} else {
		$dateReference = $data['saisieDate'];
		$heureReference = substr($data['saisieHeure'],0,-3);
		$strTooltip = sprintf(_s("Date de saisie : %s"),afficheDate($dateReference)." ".$heureReference);
	}
	
	if($data['demandeDate'] != $dateReference) {
		return "<span ".tooltip_show(sprintf(_s("Date de demande : %s"),afficheDate($data['demandeDate']))."<br/>".$strTooltip).">"
			.afficheDate($data['demandeDate']).'<br/>'.afficheDate($dateReference)." ".$heureReference."</span>";
	} else {
		return afficheDate($dateReference).'<br/>'.$heureReference;
	}
}

if ($patientLogged->niveau=="preleveur" || $patientLogged->niveau=="medecin" || $patientLogged->niveau=="correspondant") {
	?>
	<script type="text/javascript">
		function clickRecherche() {
			getById('div_content').style.display = "none";
			getById('div_wait').style.display = "block";
		}
		function setSort(type) {
			getById('filter_sort').value = type;
			clickRecherche();
			getById('form_filter').submit();
		}
	</script>
	<style type="text/css">
		.analyse { font-style: italic; color:#666666; font-size: 10px; }
		.analysenew { font-style: normal; font-weight: bold; text-decoration: underline; color:#000000; }
		.analysevalide { font-style: normal; color:#005020; }
		.analysehorsborne { background-color: #F7BC8C; padding:2px; }
	</style>
	<?
}

if ($patientLogged->niveau=="patient") {
	
	// Recherche des demandes patient
	if($patientLogged->niveau=="patient" && !isset($sNumDoss) && getSrOption('affichAnt') == 0) {
		if($patientLogged->numDemande != "") {
			$sNumDoss = $patientLogged->numDemande;
		} else {
			afficheMessage(_s("Erreur de sélection : impossible de trouver la demande"));
			afficheFoot();
			die();
		}
	}
	$scd = new SoapClientDemande();
	$params = Array("patientId"=>$patientLogged->id(), "numDemande"=>$sNumDoss);
	$retourSoap = $scd->getListeDemandePatient($params);
	$dataListe = $retourSoap["data"];
	afficheMessageSil($retourSoap["message"]);
	echo "<H1>"._s("Liste des demandes")."</H1>";
	
	if(is_array($dataListe) && count($dataListe) == 1) {
		klRedir("afficheDossier.php?sNumDossier=".$dataListe[0]["numDemande"]."&sIdDossier=".$dataListe[0]["id"],0,_s("Redirection en cours ..."));
		afficheFoot();
		die();
	}
	
	$uneDemandeRegler = false;
	$strTable = "<table align=center cellpadding=1 cellspacing=1 border=0 width=600 style=\"border:1px solid #ccc;\">";
	$strTable .= "<tr class=titreBleu><td colspan=2>"._s("Numéro de demande")."</td><td>"._s("Date de la demande")."</td></tr>";
    if (is_array($dataListe)) {
    	foreach ($dataListe as $key => $data) {
			$idSite = isset($data["idSite"]) ? $data["idSite"] : 0;
			if($data["restePatient"] > 0 && $afficheDossierPaye > 0 && $data["restePatient"] > $afficheDossierPayeMin){
				$strPaiement = getStrPaiement($data['id'],$data['numDemande'],$data["restePatient"],$idSite,'reduit');
				$bonusLienImg = _s("Demande à régler")." (".$data['restePatient']." ".getMonnaie("html").") ".$strPaiement;
				$bonusLienDmd = $data['numDemande'];
				$uneDemandeRegler = true;
			} else {
				$bonusLienImg = "<a class=img href=\"afficheDossier.php?sNumDossier=".$data["numDemande"]."&sIdDossier=".$data["id"]."\"><img border=0 src=\"".imagePath("icoloupe.gif")."\"></a>";
				$bonusLienDmd = "<nobr><a href=\"afficheDossier.php?sNumDossier=".$data["numDemande"]."&sIdDossier=".$data["id"]."\">".$data['numDemande']."</a></nobr>";
			}
			$strTable .= "";
			$strTable .=
				"<tr style=\"height:25px\" class=\"corpsFonce\">"
					."<td width=250 align=center>"
						.$bonusLienImg
					."</td>" 
					."<td width=200 align=\"center\">"
						.$bonusLienDmd
					."</td>"
					."<td width=150 align=\"center\">"
						.afficheDate($data['saisieDate'])
					."</td>"
			."</tr>";
		}
	} else if ($affichAnt==0) {
		$strTable .= "<TR><TD colspan=3>"._s("Aucune demande trouvée. Pensez à saisir le numéro de demande lors de l'identification !")."</TD></TR>";
	}
	$strTable .= "</table>";
	
	echo "<H2>"._s("Cliquez sur votre numéro de demande pour la consulter")."</h2>";
	if($uneDemandeRegler && getSrOption('urlPaiement') != '') {
		echo "<div style=\"padding-left:20px;\">".sprintf(_s("Pour régler le reste à payer sur votre demande, veuillez cliquer sur l'icône %s Vous pourrez ensuite accéder à vos résultats."),"<img src=\"images/facture.gif\" border=\"0\"><br />")."</div><br />";
	}
	
	echo $strTable;

}
elseif ($patientLogged->niveau=="preleveur") {

	/** Formulaire de recherche **/
	if ($_POST['choix'] == 'reset') {
		unset($filter);
		unset($_SESSION['filter']);
	}
	if ($_POST['choix'] == 'filtrer') {
		$_SESSION['filter'] = $filter = $_POST['filter'];
	}
	if((isset($_GET["numId"]) && $_GET["numId"] != "") || (isset($_GET["numIPP"]) && $_GET["numIPP"] != "")){
		$filter = Array(
			'nomPatient'=>'', 
			'prenomPatient'=>'', 
			'numDemande'=> $_GET["numId"],
			'numIPP'=> $_GET["numIPP"],
			'nomNaissancePatient'=>'',
			'dateNaissance'=>'',
			'dateDebut'=>'',
			'dateFin'=>'',  
			'sort' => '',
			'orderIntervenant' => ''
		);
	}
	
	if(!isset($filter)) {
	
		if (isset($_SESSION['filter'])){
			$filter = $_SESSION['filter'];
		}else{
			/* Filtre par défaut */
			$filter = Array(
				'nomPatient'=>'', 
				'prenomPatient'=>'', 
				'numDemande'=>'',
				'nomNaissancePatient'=>'',
				'dateNaissance'=>'',
				'dateDebut'=>date('d-m-Y', time() - 2592000), // 1 mois avant afficher la date maximale que les préleveurs peuvent voir
				'dateFin'=>date('d-m-Y'),  
				'sort' => '',
				'orderIntervenant' => ''
			);
		}	
	} 

	/** Requete SOAP **/
	$scd = new SoapClientDemande();
	$params = Array("preleveurId"=>$patientLogged->id(), "filtre"=>$filter);
	$retourSoap = $scd->getListeDemandePreleveur($params);
	$listeDemandes = $retourSoap["data"];

	if((isset($_GET["numId"]) || isset($_GET["numIPP"])) && count($listeDemandes) == 1) {
		klRedir("afficheDossier.php?sNumDossier=".$listeDemandes[0]["numDemande"]."&sIdDossier=".$listeDemandes[0]["idDemande"],0,_s("Redirection en cours ..."));
		afficheFoot();
		die();
	} else if($_SESSION["accesPermalink"] && $_SESSION["accesPermalinkLevel"] == 0){
		$_SESSION["loginError"] = 6;
		if($patientLogged->isAuth()) {
			$patientLogged->logout();
		}
		klRedir("denied.php?type=".$_SESSION["loginError"],1,"<span style=\"color:red;\">"._s("L'authentification a échoué")."</span>");
		afficheFoot();
		die();
	}

	afficheMessageSil($retourSoap["message"]);
	echo "<H1>"._s("Liste des demandes")."</H1>";
	
	if(is_array($listeDemandes) && count($listeDemandes)>0) {	
		if ($filter["orderIntervenant"] == 1) {
			$newListeDemandes[$patientLogged->nom()." ".$patientLogged->prenom] = array();
			foreach ($listeDemandes as $dem) {
				$newListeDemandes[$dem['nomPreleveur']." ".$dem['prenomPreleveur']][] = $dem;
			}
		} else {
			foreach ($listeDemandes as $dem) {
				$newListeDemandes[0][] = $dem;
			}
		}
	}

?>
<FORM id="form_filter" NAME="principal" METHOD="POST" ACTION="consultation.php">
	<input id="form_choix" type="hidden" name="choix" value="filtrer" />
	<input id="filter_sort" type="hidden" name="filter[sort]" value="<?=$filter['sort']?>" />
	<table align="center" width="98%">
		<tr class=corps>
			<td class="corpsFonce" align="right"><?=_s("Nom usuel");?></td>
			<td><INPUT size="14" style="font-size:11px;" TYPE="text" NAME="filter[nomPatient]" value="<?=$filter['nomPatient']?>" /></td>
			<td class="corpsFonce" align="right"><?=_s("Prénom");?></td>
			<td><INPUT size="14" style="font-size:11px;" TYPE="text" NAME="filter[prenomPatient]" value="<?=$filter['prenomPatient']?>"></td>
			<td class="corpsFonce" align="right"><?=_s("N°Demande");?></td>
			<td><INPUT size="14" style="font-size:11px;" TYPE="text" NAME="filter[numDemande]" value="<?=$filter['numDemande']?>"></td>
		</tr>
		<tr class=corps>
			<td class="corpsFonce" align="right"><?=_s("Nom naissance");?></td>
			<td><INPUT size="14" style="font-size:11px;" TYPE="text" NAME="filter[nomNaissancePatient]" value="<?=$filter['nomNaissancePatient']?>" /></td>
			<td class="corpsFonce" align="right"><?=_s("Date naissance");?></td>
			<td><?=navGetInputDate(Array("id" => "dateNaissance", "name" => "filter[dateNaissance]", "dataType" => "date", "value" => $filter['dateNaissance']),true,false,true,false,true)?></td>
			<td class="corpsFonce" align="right"><label for="orderIntervenant"><?=_s("Grouper");?></label></td>
			<td><label><input type="checkbox" name="filter[orderIntervenant]" id="orderIntervenant" value="1"  <?=(($filter['orderIntervenant']==1)?("CHECKED"):(""))?>> <?=_s("par préleveur");?></label></td>
		</tr>
		<tr class=corps>
			<td class="corpsFonce" align="right"><?=_s("Période du")?></td>
			<td><?=navGetInputDate(Array("id" => "dateDebut", "name" => "filter[dateDebut]", "dataType" => "date", "value" => $filter['dateDebut']),true,false,true,false,true)?></td>
			<td class="corpsFonce" align="right">&nbsp;<?=_s("au");?></td>
			<td><?=navGetInputDate(Array("id" => "dateFin", "name" => "filter[dateFin]", "dataType" => "date", "value" => $filter['dateFin']),true,false,true,false,true)?></td>
		</tr>
		<tr>
			<td colspan="6">
				<INPUT TYPE="submit" VALUE='<?=_s("Rechercher");?>' onclick="clickRecherche();" />
				<INPUT TYPE="submit" VALUE='<?=_s("Effacer le filtre");?>' onclick="getById('form_choix').value='reset'; clickRecherche();" />
			</td>
		</tr>
	</table>
</FORM>

<?	
	/* Affichage de la liste */
	$nbDemandes = 0;
	if (is_array($newListeDemandes)) {
		$nbDemandes = filterDemandes($newListeDemandes, $filter);
	}
?>
<div id="div_content">
	<? if ($nbDemandes>=100) {
		echo "<div align='center'>"._s("L'affichage est limité à 100 demandes.")."</div>";
	 } ?>

	<div align="center">
		<?=sprintf(_s("%s demande(s) trouvée(s)"),$nbDemandes);?>&nbsp;
	</div>		
		
	<table align=center cellpadding=1 cellspacing=1 border=0 width=98% style="border:1px solid #ccc;">
		<tr class=titreBleu>
			<td colspan=2 width=23%><div onClick="setSort('dossier');" class=hand><?=_s("Demande");?>&nbsp;
				<? if ($filter['sort']=="dossier"): ?>
					<img src="images/flecheBasHover.gif">
				<? else: ?>
					<img src="images/flecheBas.gif">
				<? endif; ?>
				</div>
			</td>
			<td width=25%><div onClick="setSort('patient');" class=hand><?=_s("Patient");?>&nbsp;
				<? if ($filter['sort']=="patient"): ?>
					<img src="images/flecheBasHover.gif">
				<? else: ?>
					<img src="images/flecheBas.gif">
				<? endif; ?>
				</div>
			</td>
			<td width=40%><?=_s("Analyses");?></td>
			<td width=12%><div onClick="setSort('date');" class=hand><?=_s("Date");?> 
				<? if ($filter['sort']=="date" || empty($filter['sort'])|| $filter['sort'] == ''): ?>
					<img src="images/flecheBasHover.gif">
				<? else: ?>
					<img src="images/flecheBas.gif">
				<? endif; ?></div>
			</td>
		</tr>
		<? 
			$iD = 0;
			$_SESSION["listeDemandesSess"] = Array();
			$_SESSION["listeDemandesNomSess"] = Array();
			$_SESSION["listeDemandesIdSess"] = Array();
			
			if (is_array($newListeDemandes)) { foreach($newListeDemandes as $nomInt => $listeDemandes) {
				if ($filter['orderIntervenant'] == 1 && !empty($listeDemandes)) { 
					echo "<tr class=titre>
						<td colspan=5 align=center><b>"._s("Préleveur")." : "._secho($nomInt, "hde")."</b></td>
					</tr>";
				}
				foreach ($listeDemandes as $key => $data) { ?>
				<?
					$nomPatient = strtoupper($data['nomPatient'])." ".ucfirst(strtolower($data['prenomPatient']));
					$_SESSION["listeDemandesSess"][$iD] = $data['numDemande'];
					$_SESSION["listeDemandesNomSess"][$iD] = $nomPatient;
					$_SESSION["listeDemandesIdSess"][$iD] = $data['idDemande'];
					$iD++;
				?>
				<tr style="height:25px" class="<?=(($data['dateVisu']!="")?("corps"):("corpsFonce"))?>">
					<?
					if ($data['urgent']>0) {
						$styleColor="color:red";
					} else {
						$styleColor="";
					}
					?>
					
					<td >			
						<nobr><a class='img' href='afficheDossier.php?sNumDossier=<?=$data['numDemande'];?>&sIdDossier=<?=$data['idDemande'];?>'><img border=0 src='<?=imagePath("icoloupe.gif");?>' /></a>
						<?
							if (is_array($data["derniereVisu"])) {
								echo "<img border=0 src=\"".imagePath("icoInfo.gif")."\" title=\"".sprintf(_s("Dernier accès le %s par %s"), $data["derniereVisu"]["date"], $data["derniereVisu"]["nom"])."\" />";
							}
						?></nobr>
					</td> 
					<td align="center">
						<nobr><a style='<?=$styleColor?>' href='afficheDossier.php?sNumDossier=<?=$data["numDemande"];?>&sIdDossier=<?=$data['idDemande'];?>'><?=$data['numDemande'];?></a></nobr>
					</td>
					<td align="center">
						<?=$nomPatient?><BR><span class=descrFonce><?=afficheDate($data['dateNaissance'])?></span>
					</td>
					<td align="center" class="descr"><?=afficheAnalyses($data['analyses'], $filter)?></td>
					<td align="center"><NOBR>
						<?=getStrDate($data)?>
						<br />
						</NOBR>
					</td>
				</tr>
				<? } } } ?>
	</table>

</div>

<div id="div_wait" style="display:none;">
		<center><img src="<?=imagePath('wait16.gif')?>" /></center>
</div>	
<?	

}
elseif ($patientLogged->niveau=="medecin" || $patientLogged->niveau=="correspondant") {

	/** Formulaire de recherche **/
	if ($_POST['choix'] == 'reset') {
		unset($filter);
		$numDemRecherche = $numIPPRecherche = "";
		if (isset($_SESSION['accesPermalinkLevel']) && $_SESSION['accesPermalinkLevel'] == 2) {
			$numDemRecherche = $_SESSION["accesPermalinkNum"];
			$numIPPRecherche = $_SESSION["accesPermalinkNumIPP"];
		}
		unset($_SESSION['filter']);
		$_SESSION['filter']['numDemande'] = $numDemRecherche;
		$_SESSION['filter']['numIPP'] = $numIPPRecherche;
		
	}
	
	if ($_POST['choix'] == 'filtrer') {
		$_SESSION['filter'] = $filter = $_POST['filter'];
	}
	if((isset($_GET["numId"]) && $_GET["numId"] != "") || (isset($_GET["numIPP"]) && $_GET["numIPP"] != "")){
		$filter = Array(
			'nomPatient'=>'', 
			'prenomPatient'=>'', 
			'numDemande'=> $_GET["numId"],
			'numIPP'=> $_GET["numIPP"],
			'nomNaissancePatient'=>'',
			'dateNaissance'=>'',
			'dateDebut'=>'',
			'dateFin'=>'',  
			'sort' => '',
			'orderIntervenant' => ''
		);
	}
	if(!isset($filter)) {
		if (isset($_SESSION['filter'])){
			if (isset($_SESSION['accesPermalinkLevel']) && $_SESSION['accesPermalinkLevel'] == 2) {
				$_SESSION['filter']['numDemande'] = $_SESSION["accesPermalinkNum"];
				$_SESSION['filter']['numIPP'] = $_SESSION["accesPermalinkNumIPP"];
			}
			$filter = $_SESSION['filter'];
		}else{
			/* Filtre par défaut */
			$filter = Array(
				'nomPatient'=>'', 
				'prenomPatient'=>'', 
				'numDemande'=>'',
				'nomNaissancePatient'=>'',
				'dateNaissance'=>'',
				'dateDebut'=>date('d-m-Y', time() - 2592000), // 1 mois avant 
				'dateFin'=>date('d-m-Y'), 
				'etat'=>'', 
				'codeChapitre'=>'',
				'idMedecin'=>'', 
				'idMedecin'=>'', 
				'idsCorresp'=>'', 
				'sort' => '',
				'orderIntervenant' => ''
			);
		}
	} else if (isset($_SESSION['accesPermalinkLevel']) && $_SESSION['accesPermalinkLevel'] == 2) {
		$filter["numDemande"] = $_SESSION["accesPermalinkNum"];
		$filter["numIPP"] = $_SESSION["accesPermalinkNumIPP"];
 	}
	
	/** Requete SOAP **/
	$scd = new SoapClientDemande();
	$params = Array("idDemandeur"=>$patientLogged->id(), "typeDemandeur"=>$patientLogged->niveau, "filtre"=>$filter);
	list($listeDemandes, $listeMedecins, $listeCorrespondants, $listeChapitres, $retourSoap) = $scd->getListeDemandeDemandeur($params);
	
	if((isset($_GET["numId"]) || isset($_GET["numIPP"])) && count($listeDemandes) == 1) {
		klRedir("afficheDossier.php?sNumDossier=".$listeDemandes[0]["numDemande"]."&sIdDossier=".$listeDemandes[0]["idDemande"],0,_s("Redirection en cours ..."));
		afficheFoot();
		die();
	} else if($_SESSION["accesPermalink"] && $_SESSION["accesPermalinkLevel"] == 0){
		$_SESSION["loginError"] = 6;
		if($patientLogged->isAuth()) {
			$patientLogged->logout();
		}
		klRedir("denied.php?type=".$_SESSION["loginError"],1,"<span style=\"color:red;\">"._s("L'authentification a échoué")."</span>");
		afficheFoot();
		die();
	}
	
	afficheMessageSil($retourSoap["message"]);
	echo "<H1>"._s("Liste des demandes")."</H1>";
	
	$newListeDemandes = array();
	if ($filter["orderIntervenant"] == 1) {
		$listeDemandeMedecin = array();
		$listeDemandeCorrespondant = array();
		if ($patientLogged->niveau=="medecin") $listeDemandeMedecin["M:".$patientLogged->nom] = array();
		else if ($patientLogged->niveau=="correspondant") $listeDemandeCorrespondant["C:".$patientLogged->nom] = array();
		foreach ($listeDemandes as $dem) {
			if (!empty($dem['medecins'])) foreach ($dem['medecins'] as $med) {
				$listeDemandeMedecin["M:".trim($med['identification'])][] = $dem;
			}
			if (!empty($dem['correspondants'])) foreach ($dem['correspondants'] as $cor) {
				$listeDemandeCorrespondant["C:".trim($cor['identification'])][] = $dem;
			}
		}
		if ($patientLogged->niveau=="medecin") $newListeDemandes = array_merge($listeDemandeMedecin, $listeDemandeCorrespondant);
		else if ($patientLogged->niveau=="correspondant") $newListeDemandes = array_merge($listeDemandeCorrespondant, $listeDemandeMedecin);
	} else {
		foreach ($listeDemandes as $dem) {
			$newListeDemandes[0][] = $dem;
		}
	}
	
	aasort($listeMedecins,"identification");
	aasort($listeCorrespondants,"identification");
	
	$labelNumRecherche = "";
	$valueNumRecherche = "";
	if ($_SESSION['accesPermalinkLevel']==2) {
		if ($filter['numDemande'] != "") {
			$labelNumRecherche = _s("ADM");
			$valueNumRecherche = $filter['numDemande'];
		}
		if ($filter['numIPP'] != "") {
			if ($labelNumRecherche != "") {
				$labelNumRecherche .= " / ";
				$valueNumRecherche .= " / ";
			}
			$labelNumRecherche .= _s("IPP");
			$valueNumRecherche .=  $filter['numIPP'];
		}
	} else {
		$labelNumRecherche = _s("N°Dem/ADM/IPP");
	}
?>
<FORM id="form_filter" NAME="principal" METHOD="POST" ACTION="consultation.php">
	<input id="form_choix" type="hidden" name="choix" value="filtrer" />
	<input id="filter_sort" type="hidden" name="filter[sort]" value="<?=$filter['sort']?>" />
	<? if ($_SESSION['accesPermalinkLevel']==2) { ?> 
	<INPUT NAME="filter[numIPP]" value="<?=$filter['numIPP']?>" TYPE="hidden" />
	<? } ?>
	<table align="center" width="98%">
		<tr class=corps>
			<td class="corpsFonce" align="right"><?=_s("Nom usuel");?></td>
			<td><INPUT size="14" style="font-size:11px;" TYPE="text" NAME="filter[nomPatient]" value="<?=$filter['nomPatient']?>" <?=($_SESSION['accesPermalinkLevel']==2?"disabled":"");?> /></td>
			<td class="corpsFonce" align="right"><?=_s("Prénom");?></td>
			<td><INPUT size="14" style="font-size:11px;" TYPE="text" NAME="filter[prenomPatient]" value="<?=$filter['prenomPatient']?>" <?=($_SESSION['accesPermalinkLevel']==2?"disabled":"");?>></td>
			<td class="corpsFonce" align="right"><?=$labelNumRecherche;?></td>
			<td><INPUT style="font-size:11px;width:150px;" NAME="filter[numDemande]" value="<?=$filter['numDemande']?>" <?=($_SESSION['accesPermalinkLevel']==2?"TYPE=\"hidden\">".$valueNumRecherche:"TYPE=\"text\">");?></td>	
		</tr>
		<tr class=corps>
			<td class="corpsFonce" align="right"><?=_s("Nom naissance");?></td>
			<td><INPUT size="14" style="font-size:11px;" TYPE="text" NAME="filter[nomNaissancePatient]" value="<?=$filter['nomNaissancePatient']?>" <?=($_SESSION['accesPermalinkLevel']==2?"disabled":"");?> /></td>
			<td class="corpsFonce" align="right"><?=_s("Date naissance");?></td>
			<td><? $argsDateNaissance = Array("style"=>"font-size:11px;","id" => "dateNaissance", "name" => "filter[dateNaissance]", "dataType" => "date", "value" => $filter['dateNaissance']);
				$afficheIconeDate = true;
				if ($_SESSION['accesPermalinkLevel']==2) { $argsDateNaissance["disabled"] = "disabled"; $afficheIconeDate = false; }
				echo navGetInputDate($argsDateNaissance,true,false,$afficheIconeDate,false,true,"16")?>
			</td>
			<td class="corpsFonce" align="right"><?=_s("Etat de demande");?></td>
			<td>
				<select name="filter[etat]" style="width:150px;font-size:11px;">
					<option value="tout" <?=(($filter['etat']!="enCours" && $filter['etat']!="valide")?"selected":"") ?>><?=_s("Tout");?></option>
					<option value="enCours" <?=(($filter['etat']=="enCours")?"selected":"") ?>><?=_s("En cours");?></option>
					<option value="valide" <?=(($filter['etat']=="valide")?"selected":"") ?>><?=_s("Validée");?></option>
				</select>
			</td>
		</tr>
		<tr class=corps>
			<td class="corpsFonce" align="right"><?=_s("Période du");?></td>
			<td><?=navGetInputDate(Array("style"=>"font-size:11px;","id" => "dateDebut", "name" => "filter[dateDebut]", "dataType" => "date", "value" => $filter['dateDebut']),true,false,true,false,true,"16")?></td>
			<td class="corpsFonce" align="right">&nbsp;<?=_s("au");?></td>
			<td><?=navGetInputDate(Array("style"=>"font-size:11px;","id" => "dateFin", "name" => "filter[dateFin]", "dataType" => "date", "value" => $filter['dateFin']),true,false,true,false,true,"16")?></td>
			<td class="corpsFonce" align="right"><?=_s("Consultation");?></td>
			<td>
				<select name="filter[consulte]" style="width:150px;font-size:11px;">
					<option value="tout" <?=(($filter['consulte']!="pasVu" && $filter['consulte']!="dejaVu")?"selected":"")?>>Tout</option>
					<option value="pasVu" <?=(($filter['consulte']=="pasVu")?"selected":"")?>>Non consulté</option>
					<option value="dejaVu" <?=(($filter['consulte']=="dejaVu")?"selected":"")?>>Déjà consulté</option>
				</select>
			</td>
		</tr>
		<tr class=corps>
			<td class="corpsFonce" align="right"><?=_s("Chapitre");?></td>
			<td colspan="3">
				<select name="filter[codeChapitre]" style="width:250px;font-size:11px;">
					<option value=""><?=_s("Choisissez un chapitre")?></option>
					<? 
						if(is_array($patientLogged->tabChapitre)) {
							foreach( $patientLogged->tabChapitre as $codeChapitre=>$nomChapitre) {
								echo "<option value=\"".$codeChapitre."\" ".((isset($filter['codeChapitre']) && $filter['codeChapitre']==$codeChapitre)?"selected":"").">"._secho($nomChapitre)."</option>";
							}
						} else {
							foreach( $listeChapitres as $idChapitre=>$dataChapitre) {
								if (!empty($dataChapitre['codeChapitre'])) {
									echo "<option value=\"".$dataChapitre['codeChapitre']."\" ".((isset($filter['codeChapitre']) && $filter['codeChapitre']==$dataChapitre['codeChapitre'])?"selected":"").">"._secho($dataChapitre['nomChapitre'])."</option>";
								}
							}
						}
					?>
				</select>
			</td>
			<td class="corpsFonce" align="right"><label for="orderIntervenant"><?=_s("Grouper");?></label></td>
			<td>
				<label><input type="checkbox" name="filter[orderIntervenant]" id="orderIntervenant" value="1"  <?=(($filter['orderIntervenant']==1)?("CHECKED"):(""))?>> <?=_s("par prescripteur")?></label>
			</td>
		</tr>
		<? if($patientLogged->niveau=="correspondant"): ?>
		<tr class=corps>
			<td class="corpsFonce" align="right"><?=_s("Médecin");?></td>
			<td colspan="3">
				<select name="filter[idMedecin]" style="width:250px;font-size:11px;">
					<option value=""><?=_s("Filtrer")?></option>
					
					<? foreach ($listeMedecins as $id => $data): ?>
						<option value="<?=$id?>" <?=((isset($filter['idMedecin']) && $filter['idMedecin']==$id)?"selected":"")?>><?=_secho($data['identification'])?></option>
					<? endforeach; ?>
				</select>
			</td>
			<td class="corpsFonce" align="right"><?=_s("Correspondant");?></td>
			<td>
				<select name="filter[idCorrespondant]" style="width:150px;font-size:11px;">
					<option value=""><?=_s("Filtrer")?></option>
					<? foreach ($listeCorrespondants as $id => $data): ?>
						<option value="<?=$id?>" <?=((isset($filter['idCorrespondant']) && $filter['idCorrespondant']==$id)?"selected":"")?>><?=_secho($data['identification'])?></option>
					<? endforeach; ?>
				</select>				
			</td> 
		</tr>
		<? endif; ?>
		<tr>
			<td colspan="4">
				<INPUT TYPE="submit" VALUE='<?=_s("Rechercher");?>' onclick="clickRecherche();" />
				<INPUT TYPE="submit" VALUE='<?=_s("Effacer le filtre");?>' onclick="getById('form_choix').value='reset'; clickRecherche();" />
			</td>
		</tr>
	</table>
</FORM>

	
<?

	/* Affichage de la liste */
	$nbDemandes = 0;
	if (is_array($newListeDemandes)) {
		$nbDemandes = filterDemandes($newListeDemandes, $filter);
	}
?>

<div id="div_content">
	<? if ($nbDemandes>=100) {
		echo "<div align='center'>"._s("L'affichage est limité à 100 demandes.")."</div>";
	 } ?>	

	<div align="center">
		<?=sprintf(_s("%s demande(s) trouvée(s)"),$nbDemandes);?>&nbsp;
		<span class="analyse"><?=_s("CODE");?></span> : <?=_s("en cours");?> &nbsp;
		<span class="analysevalide"><?=_s("CODE");?></span> : <?=_s("validé");?> &nbsp;
		<span class="analysenew"><?=_s("CODE");?></span> : <?=_s("nouveaux résultats");?> &nbsp;
		<span class="analysehorsborne"><?=_s("CODE");?></span> : <?=_s("hors-borne");?> &nbsp;
	</div>		
		
	<table align=center cellpadding=1 cellspacing=1 border=0 width=98% style="border:1px solid #ccc;">
		<tr class=titreBleu height=22>
			<td colspan=2 width=23%><div onClick="setSort('dossier');" class=hand><?=_s("Demande / ADM");?>&nbsp;
				<? if ($filter['sort']=="dossier"): ?>
					<img src="images/flecheBasHover.gif">
				<? else: ?>
					<img src="images/flecheBas.gif">
				<? endif; ?>
				</div>
			</td>
			<td width=25%><div onClick="setSort('patient');" class=hand><?=_s("Patient");?>&nbsp;
				<? if ($filter['sort']=="patient"): ?>
					<img src="images/flecheBasHover.gif">
				<? else: ?>
					<img src="images/flecheBas.gif">
				<? endif; ?>
				</div>
			</td>
			<td width=40%><?=_s("Analyses");?></td>
			<td width=12%><div onClick="setSort('date');" class=hand><?=_s("Date");?> 
				<? if ($filter['sort']=="date" || empty($filter['sort'])|| $filter['sort'] == ''): ?>
					<img src="images/flecheBasHover.gif">
				<? else: ?>
					<img src="images/flecheBas.gif">
				<? endif; ?></div>
			</td>
		</tr>


		<? 
			$iD = 0;
			$_SESSION["listeDemandesSess"] = Array();
			$_SESSION["listeDemandesNomSess"] = Array();
			$_SESSION["listeDemandesIdSess"] = Array();
				foreach($newListeDemandes as $nomInt => $listeDemandes) {
					if ($filter['orderIntervenant'] == 1 && !empty($listeDemandes)) { ?>
						<tr class=titre>
							<td colspan=5 align=center><b>
								<? if ($nomInt[0] == "M") { ?>
									<?=_s("Médecin");?> : <?= _secho(substr($nomInt, 2)); ?>
								<? } else if ($nomInt[0] == "C") { ?>
									<?=_s("Correspondant");?> : <?= _secho(substr($nomInt, 2)); ?>
								<?} ?>
							</b></td>
						</tr>
					<? }
					if (is_array($listeDemandes)) { 
						foreach ($listeDemandes as $key => $data) {
							$nomPatient = strtoupper($data['nomPatient'])." ".ucfirst(strtolower($data['prenomPatient']));
							$_SESSION["listeDemandesSess"][$iD] = $data['numDemande'];
							$_SESSION["listeDemandesNomSess"][$iD] = $nomPatient;
							$_SESSION["listeDemandesIdSess"][$iD] = $data['idDemande'];
							$iD++;
							
							$afficheDossierLien = false;
							if($data['status'] == "valide" || $data['status'] == "valideValab" || $patientLogged->getOptionUtilisateur("kaliresNonValide") == 0 || $patientLogged->getOptionUtilisateur("kaliresNonValide") == 1) {
								$afficheDossierLien = true;
							}
						?>
						<tr style="height:25px" class="<?=(($data['dateVisu']!="")?("corps"):("corpsFonce"))?>">
							<td><? if($afficheDossierLien) { ?>
									<nobr><a class="img" href="afficheDossier.php?sNumDossier=<?=$data['numDemande']?>&sIdDossier=<?=$data['idDemande']?>"><img border=0 src="<?=imagePath("icoloupe.gif")?>" /></a>
									<?
										if (is_array($data["derniereVisu"])) {
											$strVisuTitle = sprintf(_s("Dernier accès le %s par %s"), $data["derniereVisu"]["date"], $data["derniereVisu"]["nom"]);
											echo "<img border=0 src=\"".imagePath("icoInfo.gif")."\" title=\"".$strVisuTitle."\" />";
										}
									?></nobr>
								<? } else { 
										echo "<img border=0 src=\"".imagePath("icosablier.gif")."\" title=\""._s("Demande non validée")."\" />";
								} ?>
							</td> 
							<td align="center"><nobr>
								<? if($afficheDossierLien) { ?>
									<a style="<?=(($data['urgent']>0)?("color:red"):(""))?>" href="afficheDossier.php?sNumDossier=<?=$data['numDemande']?>&sIdDossier=<?=$data['idDemande']?>&chapitreId=<?=$data['chapitreId']?>">
										<?=$data['numDemande']?>
									</a>
								<? } else {
									echo "<span style=\"color:#444;\">".$data['numDemande']."</span>";
								}?>
								</nobr>
								<br /><nobr>
								<?=(($data['numDemandeExterne']!="")?("<span class=descr>".$data['numDemandeExterne']."</span>&nbsp;-&nbsp;"):(""))?>
								<?=getDemandeStatusStr($data['status'])?></nobr>
							</td>
							<td align="center">
								<?=$nomPatient.(($data['numChambreExterne']!="")?(" (ch.".$data['numChambreExterne'].")"):(""));?><BR><span class=descrFonce><?=afficheDate($data['dateNaissance'])?></span>
							</td>
							<td align="center" class="descr"><?=afficheAnalyses($data['analyses'], $filter)?></td>
							<td align="center"><NOBR>
								<?=getStrDate($data)?>
								</NOBR>
							</td>
						</tr>
					<? }
					}
				}
			?>
	</table>

</div>


<div id="div_wait" style="display:none;">
		<center><img src="<?=imagePath('wait16.gif')?>" /></center>
</div>

<?

}
?>	

<?	
afficheFoot();
?>
