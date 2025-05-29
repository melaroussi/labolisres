<?php
include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

afficheHead(_s("Liste des prescriptions")." - ".getSrOption("laboNom"),"",true);
filtrageAcces("patient","index.php","index.php");

entete();

echo "<SCRIPT LANGUAGE='Javascript' src=\"".$conf["baseURL"]."include/lib.js\" ></script>";

echo "<H1>"._s("Liste des prescriptions")."</H1>";


if (!$_SESSION["accesPC"]) {
	klredir("consultation.php", 3, _s("Vous n'avez pas accès à cette page."));
	die;
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



	function clickRecherche() {
		getById('div_content').style.display = "none";
		getById('div_wait').style.display = "block";
	}
	function setSort(type) {
		getById('filterLP_sort').value = type;
		clickRecherche();
		getById('form_filterLP').submit();
	}
</script>

<?

	if ($_POST['choix'] == 'reset') {
		unset($filterLP);
		unset($_SESSION['filterLP']);
	}
	if ($_POST['choix'] == 'filtrer') {
		$_SESSION['filterLP'] = $filterLP = $_POST['filterLP'];
	}
	if(!isset($filterLP)) {
		if (isset($_SESSION['filterLP']))
			$filterLP = $_SESSION['filterLP'];
		else
			/* Filtre par défaut */
			$filterLP = Array(
				'nomPatient'=>'', 
				'prenomPatient'=>'', 
				'numDemande'=>'',
				'nomNaissancePatient'=>'',
				'dateNaissance'=>'',
				'dateDebut'=>date('d-m-Y', time() - 2592000), // 1 mois avant 
				'dateFin'=>date('d-m-Y'), 
				'etat'=>'', 
			);
	}

	$scp = new SoapClientPrescription();	
	$params = Array("idDemandeur"=>$patientLogged->id(), "typeDemandeur"=>$patientLogged->niveau, "filtre"=>$filterLP);
	$listePrescription = $scp->getListePrescriptionConnectee($params);
?>


<FORM id="form_filterLP" NAME="principal" METHOD="POST" ACTION="listePrescription.php">
	<input id="form_choix" type="hidden" name="choix" value="filtrer" />
	<input id="filterLP_sort" type="hidden" name="filterLP[sort]" value="<?=$filterLP['sort']?>" />
	<table align="center" width="98%">
		<tr class=corps>
			<td class="corpsFonce" align="right"><?= _s("Nom usuel") ?></td>
			<td><INPUT size="14" style="font-size:11px;" TYPE="text" NAME="filterLP[nomPatient]" value="<?=$filterLP['nomPatient']?>" /></td>
			<td class="corpsFonce" align="right"><?= _s("Prénom") ?></td>
			<td><INPUT size="14" style="font-size:11px;" TYPE="text" NAME="filterLP[prenomPatient]" value="<?=$filterLP['prenomPatient']?>"></td>
			<td class="corpsFonce" align="right"><?= _s("Date naissance") ?></td>
			<td><?=navGetInputDate(Array("style"=>"font-size:11px;","id" => "dateNaissance", "name" => "filterLP[dateNaissance]", "dataType" => "date", "value" => $filterLP['dateNaissance']),true,false,true,false,true,"16")?></td>
		</tr>
		<tr class=corps>
			<td class="corpsFonce" align="right"><?= _s("N°Dem/ADM/IPP") ?></td>
			<td><INPUT size="14" style="font-size:11px;" TYPE="text" NAME="filterLP[numDemande]" value="<?=$filterLP['numDemande']?>"></td>
			<td class="corpsFonce" align="right"><?= _s("Période du") ?></td>
			<td><?=navGetInputDate(Array("style"=>"font-size:11px;","id" => "dateDebut", "name" => "filterLP[dateDebut]", "dataType" => "date", "value" => $filterLP['dateDebut']),true,false,true,false,true,"16")?></td>
			<td class="corpsFonce" align="right">&nbsp;<?= _s("au") ?></td>
			<td><?=navGetInputDate(Array("style"=>"font-size:11px;","id" => "dateFin", "name" => "filterLP[dateFin]", "dataType" => "date", "value" => $filterLP['dateFin']),true,false,true,false,true,"16")?></td>
		</tr>
		<tr class=corps>
			<td class="corpsFonce" align="right"><?= _s("Etat de prescription") ?></td>
			<td>
				<select name="filterLP[etat]" style="width:120px;font-size:11px;">
					<option value="tout" <?=(($filterLP['etat']!="enCours" && $filterLP['etat']!="valide")?"selected":"") ?>><?= _s("Tout") ?></option>
					<option value="saisie" <?=(($filterLP['etat']=="saisie")?"selected":"") ?>><?= _s("Enregistrée") ?></option>
					<option value="valide" <?=(($filterLP['etat']=="valide")?"selected":"") ?>><?= _s("Validée") ?></option>
					<option value="cloture" <?=(($filterLP['etat']=="cloture")?"selected":"") ?>><?= _s("Saisie") ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<INPUT TYPE="submit" VALUE='<?=_s("Rechercher");?>' onclick="clickRecherche();" />
				<INPUT TYPE="submit" VALUE='<?=_s("Effacer le filtre");?>' onclick="getById('form_choix').value='reset'; clickRecherche();" />
			</td>
		</tr>
	</table>
</FORM>


<?

	$nbPC = count($listePrescription["dossierSaisi"]["cloture"]) + count($listePrescription["dossierNonSaisi"]["pcval"]) + count($listePrescription["dossierNonSaisi"]["pcsaisie"]);

?>


<div id="div_content">
	<div align="center">
		<?= sprintf(_s("%s prescription(s) trouvée(s)"),$nbPC);?>
	</div>		
		

	<table align=center cellpadding=1 cellspacing=1 border=0 width=98% style="border:1px solid #ccc;">
		<tr class=titreBleu>
			<td colspan=2 width=20%><div ><?=_s("Numéro") ?>&nbsp;
			</td>
			<td width=30%><div ><?= _s("Patient") ?>&nbsp;</div>
			</td>
			<td width=30%><?= _s("Analyses") ?></td>
			<td width=20%><?= _s("Date") ?><br /><?= _s("Etat labo.") ?>
			</td>
		</tr>
		<? 
			$iD = 0;
			$_SESSION["listeDemandesSess"] = Array();
			$_SESSION["listeDemandesNomSess"] = Array();
				foreach($listePrescription as $etatDossier => $listePC) {
				?>
					<tr class="titre">
						<td colspan=5 align=center><b>
							<? if ($etatDossier == "dossierNonSaisi") { ?>
								<?=_s("Demandes non saisies");?>
							<? } else if  ($etatDossier == "dossierSaisi") { ?>
								<?=_s("Demandes saisies");?>
							<?} ?>
						</b></td>
					</tr>
				<?
				if (is_array($listePC)) { 
					foreach ($listePC as $PCEtat => $dossier) 
					{
						if ($etatDossier == "dossierNonSaisi") {
							?> <tr class="sousTitrePC">
								<td colspan=5 align=center><b>
									<? if ($PCEtat == "pcsaisie") { 
										echo _s("Prescriptions enregistrées (à valider)");
									 } else if  ($PCEtat == "pcval") { 
										echo _s("Prescriptions validées");
									} ?>
								</b></td>
							</tr> <?
						}
						$qtipNS = (isSrOption("typeLabo","maroc") ? _s("N° CIN du patient : ") : _s("NSS du patient : ") );
						$labelNS = (isSrOption("typeLabo","maroc") ? _s("N° CIN") : _s("NSS") );
						foreach($dossier as $key=>$data) {
							
							$donneeHprim = unserialize($data["donneeHPRIM"]);
							$nomPatient = strtoupper($data['nom'])." ".ucfirst(strtolower($data['prenom']));
							$_SESSION["listeDemandesSess"][$iD] = $data['numDemande'];
							$_SESSION["listeDemandesNomSess"][$iD] = $nomPatient;
							$iD++;
							
							$analysePresc = $donneeHprim["analyses"];
							$tabAnalyse = listeCodePrescription($analysePresc, true);

						    $qtipInfo = "";
							if ($data["numDemande"]) { 
					 			$qtipInfo .= _s("N° de demande Kalisil : ").$data["numDemande"]."<br />";
							} if ($donneeHprim["numPermanent"]) { 
								$qtipInfo .= _s("N° patient KaliSil : "). $donneeHprim["numPermanent"]."<br />";
						 	} if ($donneeHprim["numPatientExterne"]) {
								$qtipInfo .= _s("IPP du patient : "). $donneeHprim["numPatientExterne"]."<br />";
							} if ($donneeHprim["numDemandeExterne"]) {
								$qtipInfo .= _s("N° d'admission : "). $donneeHprim["numDemandeExterne"]."<br />";
						 	} if ($donneeHprim["caisse"]["numeroSecu"]) {
								$qtipInfo .= $qtipNS.$donneeHprim["caisse"]["numeroSecu"]."<br />";
							}

							if ($data["saisieDate"] && $data["saisieDate"]) {
								$qtipInfoDmd = _s("Demande saisie le ") . afficheDate($data["saisieDate"]) . _s(" à ") . $data["saisieHeure"];
							}
						?>
							<tr style="height:25px" class="corpsFonce" >
								<td style="marging-right: 0; padding-right: 0; " width="4%" align=center>
									<? if ($data['status']) {  ?>
										<a class="img" href="afficheDossier.php?sNumDossier=<?= $data["numDemande"] ?>&sIdDossier=<?= $data["idDemande"] ?>" ><img border=0 src="<?=imagePath("icoloupe.gif")?>" /></a>
									<? } else { ?>
										<a class="img" href="prescription.php?idPrescription=<?=$data['id']?>" ><img border=0 src="<?=imagePath("icoloupe.gif")?>" /></a>
									<? }	?>
								</td>
								<td align="center" style="border: 30px; marging-left: 0; padding-left: 0; " class="defaultCursor qtipOn" help="<?= $qtipInfo ?>" width="16%" >
								 	<nobr>
								 		<? if ($data["numDemande"]) { 
								 			echo _s("N°Demande")." ".$data["numDemande"]; ?>
									</nobr><nobr>
										<? } else if ($donneeHprim["numPermanent"]) { 
											echo  _s("N°Patient")." ". $donneeHprim["numPermanent"]; ?>
									</nobr><nobr>
										<? } else if ($donneeHprim["numPatientExterne"]) {
											echo  _s("IPP")." ". $donneeHprim["numPatientExterne"]; ?>
									</nobr><nobr>
										<? } else if ($donneeHprim["numDemandeExterne"]) {
											 echo  _s("ADM")." ". $donneeHprim["numDemandeExterne"];
									?></nobr><nobr>
										<? } else if ($donneeHprim["caisse"]["numeroSecu"]) {
											 echo $labelNS." ". $donneeHprim["caisse"]["numeroSecu"];?>
										<? } ?>
									</nobr>
								</td>
								<td  align="center">
									<?=$nomPatient?><BR><span class=descrFonce><?=afficheDate($data['dateNaissance'])?></span>
								</td>
								<td align="center"><?= (is_array($tabAnalyse)) ? (afficheAnalyses($tabAnalyse, $filter)) : ("")?></td>
								<td align="center" class="defaultCursor qtipOn" help="<?= $qtipInfoDmd ?>"><NOBR>
									<?= afficheDate($data["dateAdmission"])." ". $data["heureAdmission"]?>
									<br />
									<? if ($data['status']) {  ?>
										<? echo getDemandeStatusStr($data['status']); ?>
									<? } else { ?>
										<? echo "<i>"._s("Non saisie")."</i>"; ?>
									<? }?>
									</NOBR>
								</td>
							</tr>
						<? }
						}
					}
				}
			?>
	</table>
</div>

<div id="div_wait" style="display:none;">
		<center><img src="<?=imagePath('wait16.gif')?>" /></center>
</div>

<?
afficheFoot();
?>