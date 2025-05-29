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
  **/                               
?><?

include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

afficheHead(_s("Référentiel d'analyses")." - ".getSrOption("laboNom"),"",true);
filtrageAcces("patient","index.php","index.php");

entete();

if ($_SESSION["refAnalyse"] == 0) {
	klredir("consultation.php", 3, _s("Vous n'avez pas accès à cette page."));
	die;
}

echo "<H1>Liste des référentiels d'analyses</H1>";

switch($patientLogged->niveau) {
	case "patient" : $type="Patient"; break;
	case "medecin" : $type="Medecin";  break;
	case "correspondant" : $type="Corres";  break;
	case "preleveur" : $type="Preleveur";  break;
}

$scd = new SoapClientKalires();

//Maj des référentiels
if (!is_dir($conf['dataDir'].'referentiel/'.$patientLogged->niveau)) mkdir($conf['dataDir'].'referentiel/'.$patientLogged->niveau);
$dir = opendir($conf["dataDir"]."referentiel/".$patientLogged->niveau.'/');
$arrayNomReferentiel = array();
while($file = readdir($dir)) {
	$extension = pathinfo($file);
	if($extension['extension'] == "pdf") {			
		$infoFile = explode("_",substr($file,0,-4));
		$arrayNomReferentiel[$infoFile[1]] = $infoFile[0];
	}
}

$scd->majKaliresReferentiel($type,$arrayNomReferentiel);

//Liste des referentiels
$listeRef = $scd->getKaliresReferentiel($type);	
if(is_array($listeRef) && count($listeRef)>0) {

	echo "<br /><div id=\"div_content\"><table align=center cellpadding=1 cellspacing=1 border=0 width=50% style=\"border:1px solid #ccc;\">
			<tr class=titreBleu>
				<td width=60%>"._s("Titre")."&nbsp;</td>
				<td widrh=40%>"._s("Téléchargement")."&nbsp;</td>
			</tr>";

	foreach($listeRef as $key => $value) {
		if(file_exists($conf["dataDir"]."referentiel/".$patientLogged->niveau.'/'.$value["modifDate"]."_".$value["id"].".pdf")) {
			echo "<tr class=\"corps\">
					<td>".$value["nom"]."</td>
					<td align=center>
						<img src=\"".imagePath("icopdf2.gif")."\" title=\""._s("Afficher le référentiel")."\" onClick=\"makeRemote('dico','pjGet.php?src=referentiel&file=".$value["modifDate"]."_".$value["id"].".pdf',800,600);\" class=hand>
					</td>
				</tr>";
		}
	}
	
	echo "</table></div>";
	
} else {
	echo _s("Aucun référentiel disponible.");
}

afficheFoot();
?>
