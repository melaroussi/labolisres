<?php                                  
 /**                                
?><?php
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

$typeDestinataire = $patientLogged->niveau;

afficheHead(_s("Consultation d'une demande")." - ".getSrOption("laboNom"),"",true);
filtrageAcces("patient","index.php","index.php");

tooltip_init();

$optionRouge = getSrOption('afficheRouge');
$affichAnt = getSrOption('affichAnt');
$affichEncours = getSrOption('affichEncours');
$afficheDossierPaye = getSrOption('afficheDossierPaye');
$afficheDossierPayeMin = getSrOption('afficheDossierPayeMin');

if($patientLogged->niveau=="patient" && getSrOption('affichAnt') == 0 && $patientLogged->numDemande != $sNumDossier) {
	entete();
	afficheMessage(_s("Erreur de sélection : impossible de trouver la demande"));
	afficheFoot();
	die();
}

$scd = new SoapClientDemande();
$params = Array(
	"typeDestinataire"=>$typeDestinataire, 
	"patientId"=>$patientLogged->id(), 
	"numDemande"=>$sNumDossier,
	"idDemande"=>$sIdDossier,
	"patientNiveau"=>$patientLogged->niveau,
	"referer"=>$_SERVER["HTTP_REFERER"]
);
$dataPatient = $scd->getDataPatient($params);

entete($menuAdd);

if($dataPatient['numDemande'] == "") {
	afficheMessage(_s("Impossible de trouver la demande"));
	afficheFoot();
	die();
}

if($patientLogged->niveau == "patient") {
	afficheMessageSil();
}
$demandeListLink = _s("Liste des demandes");
if(!$_SESSION["accesPermalink"] || $_SESSION["accesPermalinkLevel"] == 1 || $_SESSION["accesPermalinkLevel"] == 2){
	$demandeListLink = "<a href='consultation.php' title='"._s("Liste des demandes")."'>".$demandeListLink."</a>";
}
echo "<h1>".$demandeListLink." >> "._s("Consultation")." : $sNumDossier</h1>";

if($sNumDossier != "" && is_array($_SESSION["listeDemandesSess"]) && in_array($sNumDossier,$_SESSION["listeDemandesSess"])) {
	$iPos = array_search($sNumDossier,$_SESSION["listeDemandesSess"]);
	$suivPrec = false;
	if($iPos > 0 && isset($_SESSION["listeDemandesSess"][$iPos-1])) {
		echo "<span style=\"float:left;\"><a href=\"afficheDossier.php?sNumDossier=".$_SESSION["listeDemandesSess"][$iPos-1]."&sIdDossier=".$_SESSION["listeDemandesIdSess"][$iPos-1]."\"><img border=0 src=\"images/navprevpetit.gif\"> "._s("Demande ultérieure")." (".$_SESSION["listeDemandesNomSess"][$iPos-1].")</a></span>";
		$suivPrec = true;
	}
	if($iPos < count($_SESSION["listeDemandesSess"]) && isset($_SESSION["listeDemandesSess"][$iPos+1])) {
		echo "<span style=\"float:right;\"><a href=\"afficheDossier.php?sNumDossier=".$_SESSION["listeDemandesSess"][$iPos+1]."&sIdDossier=".$_SESSION["listeDemandesIdSess"][$iPos+1]."\">"._s("Demande antérieure")." (".$_SESSION["listeDemandesNomSess"][$iPos+1].") <img border=0 src=\"images/navnextpetit.gif\"></a></span>";
		$suivPrec = true;
	}
	echo "<br />";
	if($suivPrec) {
		echo "<br />";
	}
}

echo '<FORM id="newPrescription" AUTOCOMPLETE=off NAME="principal" METHOD="POST" ACTION="prescription.php" >
		<input type="hidden" id="ipp" name="dataPatient[ipp]" value="'._secho($dataPatient["numPermanentExterne"],"input").'"/>
		<input type="hidden" id="numAdm" name="dataPatient[numAdm]" value="'._secho($dataPatient["numDemandeExterne"],"input").'"/>
		<input type="hidden" id="numPerm" name="dataPatient[numPerm]" value="'._secho($dataPatient["numPermanent"],"input").'"/>
		<input type="hidden" id="numSecu" name="dataPatient[numSecu]" value="'._secho($dataPatient["assureNumSecu"],"input").'"/>
		<input type="hidden" id="nomNaissance" name="dataPatient[nomNaissance]" value="'._secho($dataPatient["nomJeuneFille"],"input").'"/>
		<input type="hidden" id="dateN" name="dataPatient[dateN]" value="'._secho($dataPatient["dateNaissance"],"input").'"/>
		<input type="hidden" id="nomUsuel" name="dataPatient[nomUsuel]" value="'._secho($dataPatient["nom"],"input").'"/>
		<input type="hidden" id="sexe" name="dataPatient[sexe]" value="'._secho($dataPatient["sexe"],"input").'"/>
		<input type="hidden" id="prenom" name="dataPatient[prenom]" value="'._secho($dataPatient["prenom"],"input").'"/>
		<input type="hidden" id="rangG" name="dataPatient[rangG]" value="'._secho($dataPatient["rangGemellaire"],"input").'"/>
		<input type="hidden" id="civ" name="dataPatient[civ]" value="'._secho($dataPatient["civilite"],"input").'"/>
	</FORM>';

$btnAddFromVisu = "";
if($_SESSION["accesPC"] && (!$_SESSION["accesPermalink"] || $_SESSION["accesPermalinkLevel"] == 1)){
	$btnAddFromVisu = "<img src=\"".imagePath("icoaddpetit.gif")."\" title=\""._s("Saisir une nouvelle prescription connectée pour ce patient")."\" class=\"hand\" style=\"position:absolute; right:1px;bottom:3px;\" onClick=\"$('#newPrescription').submit();\"/></nobr>";
}
echo "<table class=titreBleu cellspacing=1 cellpadding=2 align=center width=650 style=\"margin-top:10px;margin-bottom:10px;\">";
echo "<tr class=corps height=\"20px\">"
		."<td width=\"14%\" class=corpsFonce>"._s("N° Demande")." :</td>"
		."<td width=\"34%\">".$dataPatient['numDemande']."</td>"
		."<td width=\"18%\" class=corpsFonce>"._s("Date saisie")." :</td>"
		."<td width=\"34%\">".afficheDate($dataPatient['saisieDate'])." ".$dataPatient['saisieHeure'].(($dataPatient['saisieDate']!=$dataPatient['demandeDate'])?(" (".afficheDate($dataPatient['demandeDate']).")"):(""))."</td>"
	."</tr>"
	."<tr class=corps height=\"20px\">"
		."<td class=corpsFonce>"._s("N° Admission")." :</td>"
		."<td>".$dataPatient['numDemandeExterne'].(($dataPatient['numPermanentExterne']!="")?(" (IPP ".$dataPatient['numPermanentExterne'].")"):(""))."</td>"
		."<td class=corpsFonce>"._s("Date prélèvement")." :</td>"
		."<td>".(($dataPatient['preleveDate']!="0000-00-00")?(afficheDate($dataPatient['preleveDate'])." ".$dataPatient['preleveHeure']):("")).(($dataPatient['saisieDate']!=$dataPatient['demandeDate'])?(" (".afficheDate($dataPatient['demandeDate']).")"):(""))."</td>"
	."</tr>"
	."<tr class=corps height=\"20px\">"
		."<td class=corpsFonce>"._s("Patient(e)")." :</td>"
		."<td style=\"position:relative\"><span style=\"position:absolute;left:1px;top:4px;\">".strtoupper($dataPatient['nom'])." ".ucfirst(strtolower($dataPatient['prenom']))."</span>&nbsp;".$btnAddFromVisu."</td>"
		."<td class=corpsFonce>"._s("Date naissance")." :</td>"
		."<td>".afficheDate($dataPatient['dateNaissance'])."</td>"
	."</tr>"
	."<tr class=corps height=\"20px\">"
		."<td class=corpsFonce>"._s("Médecin")." :</td>"
		."<td>".$dataPatient['nomMedecin']."<span style=\"font-size:10px;\"><br />".$dataPatient['medecinCoordonnee']."</span></td>"
		."<td class=corpsFonce>"._s("Correspondant")." :</td>"
		."<td>".$dataPatient['nomCorrespondant']."<span style=\"font-size:10px;\"><br />".$dataPatient['correspCoordonnee']."</span></td>"
	."</tr>";
echo "</table>";
echo "<br />";

if($patientLogged->niveau == "patient" && $dataPatient['restePatient'] > 0) {
	$strPaiement = getStrPaiement($dataPatient['id'],$dataPatient['numDemande'],$dataPatient['restePatient'], $dataPatient["idSite"]);
	if($strPaiement != "") $strPaiement = "<br />".$strPaiement;
	echo "<center><div style=\"color:#222;border:1px solid #aaa;padding:4px;width:450px;background-color:#fff;\">"._s("Cette demande n'a pas encore été réglée")." (".$dataPatient['restePatient']." ".getMonnaie("html").")".$strPaiement."</div><br /><br /></center>";
	if($afficheDossierPaye > 0 && $dataPatient['restePatient'] > $afficheDossierPayeMin) {
		echo "<center><b>"._s("Les résultats seront visibles après règlement de la demande auprès du laboratoire")."</b></center>";
		afficheFoot();
		die();
	}
} elseif($patientLogged->niveau == "patient" && $dataPatient['montantPatient'] > 0 && paiementAllowed($dataPatient["idSite"])) {
	/* SOAP: Récupération de la quittance */
	$scd = new SoapClientDemande();
	$nomFichier = $scd->getFichierQuittance($dataPatient['id']);
	if ($nomFichier != false) {
		$nomPdfDemande = str_replace(Array(" ", "-", "/"), Array("_"), $dataPatient['numDemande']) . "_Quittance.pdf";
		echo "<center><div style=\"color:#222;border:1px solid #aaa;padding:4px;width:450px;background-color:#fff;\"><span class=\"hand\" onClick=\"makeRemote('quittance','pjGet.php?src=quittance&file=" . basename($nomFichier) . "&nom=" . $nomPdfDemande . "',800,600);\">" . _s("Cette demande a été réglée, cliquez-ici pour récupérer la quittance") . "<img src=\"" . imagePath("icopdf2.gif") . "\" /><br /></span></div><br /><br /></center>";
	} else {
		echo "<center><div style=\"color:#222;border:1px solid #aaa;padding:4px;width:450px;background-color:#fff;\">"._s("Quittance non disponible pour le moment")."</div><br /><br /></center>";
	}
}

if(is_array($dataPatient['dataNC']) && count($dataPatient['dataNC']) > 0) {
	echo "<center><div style=\"color:#222;border:1px solid #aaa;padding:4px;width:650px;background-color:#fff;text-align:left;\"><u>"._s("Dysfonctionnement identifié sur cette demande")."</u><br /> &middot; ";
	echo implode("<br /> &middot; ",$dataPatient['dataNC']);
	echo "</div><br /><br /></center>";
}
	
//Insertion d'une trace
$cs = new SoapClientKalires();
$cs->trace($patientLogged->niveau, $patientLogged->id(), 'consultation', $dataPatient["id"], $patientLogged->traceUser);

if ($dataPatient["status"] != 'valide' && ($typeDestinataire == "preleveur"
											|| ($typeDestinataire == "patient" && $affichEncours == 0)
											|| (in_array($typeDestinataire,Array("medecin","correspondant")) && $dataPatient["dataInter"]["kaliresNonValide"] == 2))) {

	afficheMessage(_s("Demande non validée, veuillez vous reconnecter ultérieurement"));

} else if(($dataPatient["status"] == "valide" && ($typeDestinataire == "patient" || $typeDestinataire == "preleveur")) || ($typeDestinataire == "patient" && $affichEncours == 1) || $typeDestinataire == "medecin" || $typeDestinataire == "correspondant") {

	ob_start();

	/*********************************************** ONGLET RESULTATS *************************************************/

	$scd = new SoapClientDemande();
	$params = Array(
		"idDemande" => $dataPatient['id'],
		"numDemande" => $dataPatient['numDemande'],
		"patientId"=>$patientLogged->id(), 
		"patientNiveau"=>$patientLogged->niveau,
		"chapitreId"=>$chapitreId,
	);
	$dataAnalyseArray = $scd->getDataAnalyse($params);

	$anterioriteTableau = false;
	$trAnterio = "";
	$colspan = 6;
	if(is_array($dataAnalyseArray[0]["anterioriteTabReference"])) {
		$anterioriteTableau = true;
		foreach($dataAnalyseArray[0]["anterioriteTabReference"] as $keyAnterio) {
			$trAnterio .= "<TD>".substr($keyAnterio,8,2)."-".substr($keyAnterio,5,2)."-".substr($keyAnterio,2,2)."</TD>";
			$colspan++;
		}
	} else {
		$colspan++;
	}

	echo "<TABLE cellspacing=1 cellpadding=2 border=0 width=98% class=titre align=center>"
		."<TR class=titreBleu>"
			."<TD><NOBR> "._s("Nom")." </NOBR></TD>"
			."<TD><NOBR>&nbsp;"._s("Résultat")."&nbsp;</NOBR></TD>"
			."<TD><NOBR>&nbsp;"._s("Unité")."&nbsp;</NOBR></TD>"
			."<TD align=center><NOBR>&nbsp;"._s("Bornes")."&nbsp;</NOBR></TD>"
			."<TD title=\"Indicateur d'anormalité\" alt=\"Indicateur d'anormalité\"><NOBR> "._s("Ind")." </NOBR></TD>"
			."<TD title=\"Validation\" alt=\"Validation\"><NOBR> "._s("Val")." </NOBR></TD>"
			.(($anterioriteTableau)?($trAnterio):("<TD><NOBR> "._s("Antériorité")." </NOBR></TD>"))
		."</TR>";
	$tmp2="";

	$cacher = false;
	
	$anterioDateOld = "";
	$valideDateHeureDemande = "";
	$dernierValideurBioStr = "";
	$dernierValideurBioTime = 0;
	$tabExecutant = Array();
	$tabExecutantAsterisk = Array();
	if (is_array($dataAnalyseArray)) foreach($dataAnalyseArray as $key => $dataAnalyse) {

		if(isset($dataAnalyse["anterioriteTabReference"])) {
			continue;
		}

		//Affichage du chapitre
		if (isset($dataAnalyse["idChapitre"]) && $dataAnalyse["idChapitre"] != "") {
			$commentaireChapitre = "<span class=\"commentaireChapitre\">".$dataAnalyse["commentaireChapitre"]."</span>";
			echo "<TR class=titreChapitre align=center><TD colspan=\"".$colspan."\">".$dataAnalyse["nomChapitre"].($dataAnalyse["placeCommentaireChapitre"]==1?" ".$commentaireChapitre:"")."</TD></TR>";
			$oldChapitre=$dataAnalyse["idChapitre"];
			if ($dataAnalyse["commentaireChapitre"] != "" && $dataAnalyse["placeCommentaireChapitre"] == 0) {
				echo "<TR class=\"commentaireChapitre\"><TD colspan=\"".$colspan."\">".$dataAnalyse["commentaireChapitre"]."</TD></TR>";
			}
			continue;
		}

		$valideDateHeureDemande = $dataAnalyse["heureValideDemande"];
		$tabPolice = unserialize($dataAnalyse["typePolice"]);
		
		$classListe = "";
		if ($tabPolice["B"] == 1) $classListe .= " gras";
		if ($tabPolice["I"] == 1) $classListe .= " italique";
		if ($tabPolice["U"] == 1) $classListe .= " souligne";
		
		if ($typeDestinataire == "patient" || $typeDestinataire == "preleveur") {
			if(isset($tabPolice["supprImpressionPatientHprim"])) {
				if($tabPolice["supprImpressionPatientHprim"] == 1) continue;
			} else if($tabPolice["supprImpressionPatient"] == 1) continue;
		} else if ($typeDestinataire == "medecin") {
			if(isset($tabPolice["supprImpressionMedecinHprim"])) {
				if($tabPolice["supprImpressionMedecinHprim"] == 1) continue;
			} else if($tabPolice["supprImpressionHprim"] == 1) continue;
		} else if ($typeDestinataire == "correspondant") {
			if(isset($tabPolice["supprImpressionCorrespondantHprim"])) {
				if($tabPolice["supprImpressionCorrespondantHprim"] == 1) continue;
			} else if($tabPolice["supprImpressionHprim"] == 1) continue;		
		}
		
		if($dataAnalyse["titre"] != "") {
			echo "<TR>"
					."<TD colspan=\"".$colspan."\" class=\"titreAnalyse " . $classListe . "\" align=left><NOBR>&nbsp;&middot;&nbsp;".changeTxtAna($dataAnalyse["titre"])." </NOBR></TD>"
				."</TR>";
			continue;
		}
		
		if($dataAnalyse["commentaire"] != "") {
			echo "<TR>"
					."<TD colspan=\"".$colspan."\" class=\"commentaireAnalyse " . $classListe . "\" align=left><NOBR><img src=\"".imagePath("icocommentaire.gif")."\" /> ".tronquer(changeTxtAna(nl2br($dataAnalyse["commentaire"])),150,'...',true)." </NOBR></TD>"
				."</TR>";
			continue;
		}

		if($dataAnalyse["html"] != "") {
			echo "<TR>"
					."<TD colspan=\"".$colspan."\">".$dataAnalyse["html"]."</TD>"
				."</TR>";
			continue;
		}
		
		$remplacementPatient = $dataAnalyse["affichagePatient"];
		$remplacementPatientAnalyse = $dataAnalyse["affichagePatientAnalyse"];
		$remplacementMedecin = $dataAnalyse["affichageMedecin"];
		$remplacementMedecinAnalyse = $dataAnalyse["affichageMedecinAnalyse"];
		$remplacementCorrespondant = $dataAnalyse["affichageCorrespondant"];
		$remplacementCorrespondantAnalyse = $dataAnalyse["affichageCorrespondantAnalyse"];

		if($dataAnalyse["resultatType"] == "texteCodifie") {
			 if ($dataAnalyse["SremplacementPatient"] !="") $remplacementPatient=$dataAnalyse["SremplacementPatient"];
			 if ($dataAnalyse["SremplacementPatientAnalyse"] !="") $remplacementPatientAnalyse=$dataAnalyse["SremplacementPatientAnalyse"];
			 if ($dataAnalyse["SremplacementMedecin"] !="") $remplacementMedecin=$dataAnalyse["SremplacementMedecin"];
			 if ($dataAnalyse["SremplacementMedecinAnalyse"] !="") $remplacementMedecinAnalyse=$dataAnalyse["SremplacementMedecinAnalyse"];
			 if ($dataAnalyse["SremplacementCorrespondant"] !="") $remplacementCorrespondant=$dataAnalyse["SremplacementCorrespondant"];
			 if ($dataAnalyse["SremplacementCorrespondantAnalyse"] !="") $remplacementCorrespondantAnalyse=$dataAnalyse["SremplacementCorrespondantAnalyse"];
		}
		
		//Si le resultat n'est pas validé, on saute pour le patient et on remplace pour le médecin
		if ($typeDestinataire=="patient" && $dataAnalyse["valideBioPar"]==0) {
			$remplacementPatient="<I><B><SPAN style='color:orange'>"._s("En cours")."</SPAN><B></I>";
		}else{
			if($dataAnalyse["valideBioPar"] > 0 || ($dataAnalyse["valideTechPar"] > 0 && $dataPatient["dataInter"]["kaliresNonValide"] == 1 && $dataAnalyse["bloqueAffBio"] == 0)) {
			
			} else {
				$remplacementMedecin="<I><B><SPAN style='color:orange'>"._s("En cours")."</SPAN><B></I>";
				$remplacementCorrespondant="<I><B><SPAN style='color:orange'>"._s("En cours")."</SPAN><B></I>";
			}
		} 
		
		$monAnalyse = $dataAnalyse["nom"];
		$monResultat = $dataAnalyse["resultat"];
		$resReplaced = false;
		if($typeDestinataire == "medecin") {
			if($remplacementMedecin != "") {
				$resReplaced = true;
				$monResultat = $remplacementMedecin;
			}
			if($remplacementMedecinAnalyse != "") {
				$resReplaced = true;
				$monAnalyse = $remplacementMedecinAnalyse;
				$monResultat = $remplacementMedecinAnalyse;
			}
		} elseif($typeDestinataire == "correspondant") {
			if($remplacementCorrespondant != "") {
				$resReplaced = true;
				$monResultat = $remplacementCorrespondant;
			}
			if($remplacementCorrespondantAnalyse != "") {
				$resReplaced = true;
				$monAnalyse = $remplacementCorrespondantAnalyse;
				$monResultat = $remplacementCorrespondantAnalyse;
			}
		} else {
			if($remplacementPatient != "") {
				$resReplaced = true;
				$monResultat = $remplacementPatient;
			}
			if($remplacementPatientAnalyse != "") {
				$resReplaced = true;
				$monAnalyse = $remplacementPatientAnalyse;
				$monResultat = $remplacementPatientAnalyse;
			}
		}
		
		//Recherche des bornes
		$borne = "-";$img="";
		$borne = $dataAnalyse["borne"];
		$img = "<IMG SRC='images/".afficheBorneImg($dataAnalyse["borneBiologique"])."'>";
		
		//Choix de la couleur de la ligne (rouge ou normal)
		if ($optionRouge && $dataAnalyse["borneBiologique"]!=0) $class="rouge";
		else $class="corps";
		if ($dataAnalyse["borneBiologique"]!=0 && $typeDestinataire!="patient") $class="rouge";
		
		// Antériorité
		if($anterioriteTableau) {
			$anterio = "";
			$anterioConv = "";
			foreach($dataAnalyseArray[0]["anterioriteTabReference"] as $keyAnterio) {
				$anterio .= "<TD class=\"descrGrand\" align=right>";
				$anterio .= (($dataAnalyse["anterioTableauBrut"][$keyAnterio]['valTech']==1)?('*&nbsp;'):(''));
				$tooltip = sprintf(_s("Le %s"),afficheDate($dataAnalyse["anterioTableauBrut"][$keyAnterio]["valideTechDate"]))."\n".$dataAnalyse["anterioTableauBrut"][$keyAnterio]["valideTechHeure"];
				if($dataAnalyse["resultatType"] == "texteCodifie") {
					$anterio .= "<span style=\"".(($dataAnalyse["anterioTableauBrut"][$keyAnterio]["borne"]!=0)?("color:red;"):(""))."\">"
									.tronquer($dataAnalyse["anterioTableauBrut"][$keyAnterio]["anterio"],150,'...',true,$tooltip."<br/><br/>");
								"</span>";
				} else {
					$anterio .= "<NOBR><span style=\"".(($dataAnalyse["anterioTableauBrut"][$keyAnterio]["borne"]!=0)?("color:red;"):(""))."\" title=\"".$tooltip."\">"
									.$dataAnalyse["anterioTableauBrut"][$keyAnterio]["anterio"]
								."</span></NOBR>";
				}
				$anterio .= "</TD>";
				$anterioConv .= "<TD class=\"descrGrand\" align=right><NOBR>".$dataAnalyse["anterioConvTableau"][$keyAnterio]."</NOBR></TD>";
			}
		} else {
			$anterio = "-";
			$anterioDate = "";
			if ($dataAnalyse["SanterioriteBrut"]!="") {
				$tooltip = sprintf(_s("Le %s"),afficheDate($dataAnalyse["SanterioriteDate"]))."\n".$dataAnalyse["SanterioriteHeure"];
				if($dataAnalyse["resultatType"] == "texteCodifie") {
					$anterio = "<span style=\"".(($dataAnalyse["SanterioriteBorne"]!=0)?("color:red;"):(""))."\">"
									.tronquer($dataAnalyse["SanterioriteBrut"],200,'...',true,$tooltip."<br/><br/>")
								."</span>";
				} else {
					$anterio = "<NOBR><span style=\"".(($dataAnalyse["SanterioriteBorne"]!=0)?("color:red;"):(""))."\" title=\"".$tooltip."\">"
									.$dataAnalyse["SanterioriteBrut"]
								."</span></NOBR>";
				}
			}
			if ($dataAnalyse["SanterioriteDate"]!="") $anterioDate=$dataAnalyse["SanterioriteDate"];
			if ($anterioDate != "" && $anterioDate != $anterioDateOld) {
				echo "<TR class=corps>"
						."<TD colspan=\"".($colspan-1)."\"></TD>"
						."<TD class=corpsFonce style=\"text-align:right\">".afficheDate($anterioDate)."</TD>"
					."</TR>";	
				$anterioDateOld = $anterioDate;
			}
			$anterio = "<TD class=\"descrGrand\" align=right>".(($dataAnalyse["SanterioriteValTech"]==1)?('*&nbsp;'):('')).$anterio."</TD>";
		}

		if($dataAnalyse["valideBioPar"] > 0) {
			$bonusStyleResu = "";
			$strValPar = $dataAnalyse["initialBiologiste"];
			$strValParTitle = $dataAnalyse["nomBiologiste"]." ".$dataAnalyse["prenomBiologiste"]." / ".afficheDate($dataAnalyse["valideBioDate"])." ".substr($dataAnalyse["valideBioHeure"],0,5);
			$timestampValCur = strtotime($dataAnalyse["valideBioDate"]." ".$dataAnalyse["valideBioHeure"]);
			if($timestampValCur > $dernierValideurBioTime) {
				$dernierValideurBioTime = $timestampValCur;
				$dernierValideurBioStr = sprintf(_s("par %s le %s à %s"),$dataAnalyse["nomBiologiste"]." ".$dataAnalyse["prenomBiologiste"],afficheDate($dataAnalyse["valideBioDate"]),substr($dataAnalyse["valideBioHeure"],0,5));
			}
		} else {
			$bonusStyleResu = "font-style:italic;";
			$strValPar = "<img src=\"images/attentionPetit.gif\">";
			$strValParTitle = _s("Résultat non validé biologiquement");
		}
		
		$icoCommentaire = "";
		if($dataAnalyse["commentaire".ucfirst($patientLogged->niveau)]!= "") {
			$icoCommentaire = "<img src=\"images/icoWarning.gif\" title=\"".trim($dataAnalyse["commentaire".ucfirst($patientLogged->niveau)])."\"> ";
		}
		
		$strConversion = "";
		$rowspan = "1";
		if($dataAnalyse["resultatConversion"] != "" && !$resReplaced) {
			$strConversion = "<TR class=$class>"
								."<TD class=\"descrGrand\" align=right style=\"$bonusStyleResu\">".changeTxtAna($dataAnalyse["resultatConversion"])."</TD>"
								."<TD class=\"descrGrand\" align=left><NOBR>&nbsp;".$dataAnalyse["resultatConversionUnite"]."</NOBR></TD>"
								."<TD class=\"descrGrand\" align=center><NOBR>".$dataAnalyse["resultatConversionBorne"]."</NOBR> </TD>";
			if($anterioriteTableau) {
				$strConversion .= $anterioConv;
			} else {
				$strConversion .= "<TD class=\"descrGrand\" align=right><NOBR>".(($dataAnalyse["resultatConversionAnterio"]!="")?($dataAnalyse["resultatConversionAnterio"]):("-"))."</NOBR></TD>";
			}
			$rowspan = "2";
		}

		if($dataAnalyse["resultatType"] == "nombre") {
			if(preg_match("/^[0-9\<\>\ \.]+$/",$monResultat,$match) == 0) {
				$dataAnalyse["resultatType"] = "texteCodifie";
			}
		}
		
		$asterisk = "";
		if($dataAnalyse["correspId"] > 0) {
			if(!in_array($dataAnalyse["correspNom"],$tabExecutant)) {
				$tabExecutant[] = $dataAnalyse["correspNom"];
			}
			$asterisk = "<span style=\"float:right;font-size:9px;\">(".(array_search($dataAnalyse["correspNom"],$tabExecutant)+1).")</span>";
		}
		
		//Affichage d'une ligne de résultat
		if($dataAnalyse["resultatType"] == "texteCodifie") {
			echo "<TR class=$class>"
					."<TD class=\"descrGrand ".$classListe."\" align=left><NOBR>".$asterisk."&nbsp;".changeTxtAna($monAnalyse)." </NOBR></TD>"
					."<TD class=\"descrGrand\" colspan=\"3\" align=right style=\"$bonusStyleResu\">".changeTxtAna($monResultat)."</TD>"
					."<TD class=\"descrGrand\" align=center><NOBR>".$img."</NOBR></TD>"
					."<TD class=\"descrGrand\" align=center title=\"".$strValParTitle."\"><NOBR>".$strValPar."</NOBR></TD>"
					.$anterio
			."</TR>";				
		} else {
			echo "<TR class=$class>"
					."<TD class=\"descrGrand ".$classListe."\" rowspan=".$rowspan." align=left><NOBR>".$asterisk."&nbsp;".changeTxtAna($monAnalyse)." </NOBR></TD>"
					."<TD class=\"descrGrand\" align=right style=\"$bonusStyleResu\">".$icoCommentaire.changeTxtAna($monResultat)."</TD>"
					."<TD class=\"descrGrand\" align=left><NOBR>&nbsp;".$dataAnalyse["resultatUnite"]."</NOBR></TD>"
					."<TD class=\"descrGrand\" align=center><NOBR>".$borne["str"]."</NOBR> </TD>"
					."<TD class=\"descrGrand\" rowspan=".$rowspan." align=center><NOBR>".$img."</NOBR></TD>"
					."<TD class=\"descrGrand\" rowspan=".$rowspan." align=center title=\"".$strValParTitle."\"><NOBR>".$strValPar."</NOBR></TD>"
					.$anterio
			."</TR>".$strConversion;
		}
	}
	
	echo "</TABLE>";

	echo "<br /><SPAN class=descr><B>"._s("Indicateur Anormalité")." :</B><img src=\"images/borneDepasseBas.gif\"> : "._s("inférieur à la normale")." ; <img src=\"images/borneNormale.gif\"> : "._s("normal")." ; <img src=\"images/borneDepasseHaut.gif\"> : "._s("supérieur à la normale")."</SPAN>";
	echo "<br /><br /><SPAN class=descr><B> * </B>: "._s('Résultat validé techniquement uniquement')."</SPAN>";
	
	if(count($tabExecutant) > 0) {
		echo "<br /><span class=descr><br /><b>"._s("Laboratoire(s) exécutant(s)")." :</b> ";
		foreach($tabExecutant as $iExec => $nomExec) {
			echo "<span style=\"font-size:9px;\"> (".($iExec+1).") ".$nomExec."&nbsp;&nbsp;&nbsp;";
		}
	}
	echo "<br /><br />";
	$tmp = ob_end_get_contents();
	
	$strGarde = "";
	if($dataPatient["gardeBio"] != "") {
		$strGarde = " ".sprintf(_s("sous la responsabilité de %s"),$dataPatient["gardeBio"]);
	}
	$head = "<CENTER>"
				."<span class=corps style=\"font-size:14px;\"> "._s("Etat de la demande")." : "
					.(($dataPatient["status"]=="valide" && $dataPatient["garde"]>0)?("<span style=\"color:#009933;font-style:italic;\">"._s("Libérée en période de garde")."</span>"):(getDemandeStatusStr($dataPatient["status"])))
					.(($patientLogged->niveau=="patient" && $dataPatient["status"]!="valide")?"<br /><span style=\"font-size:11px;\">"._s("Toutes les analyses ne sont pas terminées, le compte rendu pdf sera disponible après la validation biologique complète.")."</span>":"")
					.(($dernierValideurBioStr!="" && ($dataPatient['status']=="valide" || $dataPatient['status']=="valideValab"))?("<br /><span style=\"font-size:10px;\">".$dernierValideurBioStr.$strGarde."</span>"):(""))
				."</span>"
			."</CENTER>"
			."<BR>";
	
	$tmp = $head.$tmp;
	
	/******************************************* FIN DE L'ONGLET RESULTAT **********************************************/
	
	
	/*********************************************** ONGLET IMPRESSION *************************************************/
	
	/* SOAP: Récupération du fichier de CR */

	if(in_array($typeDestinataire,Array("medecin","correspondant")) && in_array($dataPatient["status"], Array('enCours','complet')) && in_array($dataPatient["dataInter"]["kaliresNonValide"],Array(0,1)) && getSrOption("kaliResGeneratePartiel")) {
		if($_GET['showCRPartiel'] == '1') {
			$tmp2.="<IFRAME width=95% height=700 id='frameCrPartiel' src='showFileCRPartiel.php?id=".$sIdDossier."&numDemande=".$sNumDossier."'></IFRAME>";
		} else {
			$tmp2.="<br /><span class=\"corpsFonce\" style='font-size: 12px;'><b><a href=\"afficheDossier.php?sNumDossier=".$sNumDossier."&sIdDossier=".$sIdDossier."&showCRPartiel=1\">"
						._s("CLIQUEZ ICI pour afficher le compte-rendu partiel")."</b></a></span>";
		}
	} else {
		if(is_array($dataPatient['tabImpr']) && count($dataPatient['tabImpr']) > 0) {

			$iCr = 1;
			$selectCr = "<select name=\"crSelected\" onChange=\"$('#frameCr').attr('src','showFileCR.php/'+$('option:selected',this).attr('nom')+'?id=".$dataPatient['id']."&numDemande=".$dataPatient['numDemande']."&crSelected='+$('option:selected',this).attr('value')+'');\">";
			foreach($dataPatient['tabImpr'] as $key => $imprInfo) {
				if ($imprInfo["exportKalires"] == 1 && $dataPatient["status"]=="valide") {
					$crSelected = $imprInfo['id'];
				}
				if(!isset($crSelected) && count($dataPatient['tabImpr']) == $iCr) {
					$crSelected = $imprInfo['id'];
				}
				$dataPatient['tabImpr'][$key]['nomFichier'] = $nomPdfDemande = str_replace(Array(" ","-","/"),Array("_"),$dataPatient['numDemande'].' '.$imprInfo['nom'].".pdf");
				$selectCr .= "<option value=\"".$imprInfo['id']."\" nom=\""._secho($dataPatient['tabImpr'][$key]['nomFichier'])."\" ".(($crSelected==$imprInfo['id'])?("SELECTED"):("")).">".afficheDate($imprInfo['date'])." ".substr($imprInfo['heure'],0,5)." - ".$imprInfo['nom']."</option>";
				$iCr++;
			}
			$selectCr .= "</select>";

			$tmp2 .= "<br /><center>".$selectCr;

			if($valideDateHeureDemande != "") {
				$tmp2 .= "<br /><br /><span style=\"font-size:11px;\"><b>".sprintf(_s("Demande validée biologiquement le %s à %s"),afficheDate($valideDateHeureDemande),substr($valideDateHeureDemande,-8))."</b></span></center><br />";
			}

			if($crSelected > 0) {
				$tmp2.="<IFRAME width=95% height=700 id='frameCr' src='showFileCR.php/".$dataPatient['tabImpr'][$crSelected]['nomFichier']."?id=".$dataPatient['id']."&numDemande=".$dataPatient['numDemande']."&crSelected=".$crSelected."'></IFRAME>";
			} else {
				$tmp2.="<br /><br /><span class=corpsFonce><b>"._s("Veuillez sélectionner un compte-rendu à afficher")."</b></span><br /><br />";
			}

		} else {
			$tmp2.="<br /><br /><span class=corpsFonce><b>"._s("Aucun fichier de compte-rendu accessible pour le moment")."</b></span><br /><br />";
		}
	}
	
	/******************************************* FIN DE L'ONGLET IMPRESSION *********************************************/
	
	/******************************************* ONGLET ANTERIORITE *********************************************/
	
	$scd = new SoapClientDemande();
	$params = Array(
		"typeDestinataire"=>$typeDestinataire,
		"numPermanent" => $dataPatient["numPermanent"],
		"patientId"=>$patientLogged->id(),
		"patientNiveau"=>$patientLogged->niveau,
	);
	$dataAnteriorite = $scd->getDataAnteriorite($params);
	
	
	$tmp3="<table align=center cellpadding=1 cellspacing=1 border=0 width=98% style=\"border:1px solid #ccc;\">";
	$tmp3.="<tr class=titreBleu><td colspan=3>"._s("Numéro de demande")."</td><td>"._s("Date de la demande")."</td><td>Etat</td></tr>";	
	if (is_array($dataAnteriorite)) foreach($dataAnteriorite as $key => $dataAnt) {
		if ($typeDestinataire!="patient") {
			$str=$dataAnt["strPatient"];
		}
		$tmp3.=
				"<tr style=\"height:25px\" class=\"corpsFonce\">"
					."<td align=center>"
						."<a class=img href=\"afficheDossier.php?sNumDossier=".$dataAnt["numDemande"]."&sIdDossier=".$dataAnt["id"]."\">"
							."<img border=0 src=\"".imagePath("icoloupe.gif")."\">"
						."</a>"
						."</td>" 
					."<td ".( ($typeDestinataire=="patient")?"colspan=2":"")." align=\"center\">"
						."<a href=\"afficheDossier.php?sNumDossier=".$dataAnt["numDemande"]."&sIdDossier=".$dataAnt["id"]."\">"
						.$dataAnt["numDemande"]
						."</a>"
					."</td>"
					.( ($typeDestinataire!="patient")?"<td class=descr>$str</td>":"")
					."<td align=\"center\">"
						.afficheDate($dataAnt["saisieDate"])
					."</td>"
					."<td align=\"center\">"
						.getDemandeStatusStr($dataAnt["status"])
					."</td>"
			."</tr>";
	}
	$tmp3.="</TABLE>";
	
	$ongletCr = ($typeDestinataire == "patient" && getSrOption("patientOngletCr") || $_GET['showCRPartiel'] == '1') && $nomFichier !== false;
	
<?php
	$tab[] = Array('selected'=>($ongletCr),'name'=>"<NOBR>"._s("Compte rendu")."</NOBR>",'data'=>$tmp2);
	if($typeDestinataire == "medecin" || $typeDestinataire == "correspondant" || $typeDestinataire == "preleveur" || ($typeDestinataire=="patient" && $affichAnt)) {
		$tab[] = Array('selected'=>false,'name'=>"<NOBR>"._s("Résultats antérieurs")."</NOBR>",'data'=>$tmp3);
	}
	echo "<center>".navGetTab('monTableau',$tab,"width=95%")."</center>";
}

?>
<!-- /phpmyvisites -->
<?
	afficheFoot();
?>