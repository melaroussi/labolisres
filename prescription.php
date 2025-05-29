<?php
include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

afficheHead(_s("Saisie d'une prescription")." - ".getSrOption("laboNom"),"",true);
filtrageAcces("patient","index.php","index.php");

entete();

if (!$_SESSION["accesPC"]) {
	klredir("consultation.php",3,"Vous n'avez pas accés à cette page.");
	die();
}

echo "<SCRIPT LANGUAGE='Javascript' src=\"".$conf["baseURL"]."include/sprintf.js\" ></script>";
echo "<SCRIPT LANGUAGE='Javascript' src=\"".$conf["baseURL"]."include/lib.js\" ></script>";
echo "<H1>"._s("Saisie d'une prescription")."</H1>";

if($keyFormForm == $_SESSION["keyFormSession"]) {
	if($_POST['choix'] == "enregistrerPresc") {
		unset($_SESSION["keyFormSession"]);
		$scd = new SoapClientPrescription();
		$data["statusPrescription"] = "saisie";
		$data["kaliresType"] = $patientLogged->niveau;
		$envoiDemande = $scd->envoiDemandePresc($data);
		if($data["idPrescription"] > 0) {
			if($envoiDemande > 0) {
				klRedir ("prescription.php?idPrescription=".$data["idPrescription"]."", 3,_s("Prescription enregistrée"));
			} else {
				klRedir ("listePrescription.php", 3,_s("La presctiption n'a pas pu être enregistrée car elle a déjà été saisie sur KaliSil"));
			}
		} else {
			klRedir ("prescription.php", 3,_s("Prescription enregistrée"));
		}	
		die();
	} else if ($_POST['choix'] == "validerPresc") {
		unset($_SESSION["keyFormSession"]);
		$scd = new SoapClientPrescription();
		$data["statusPrescription"] = "valide";
		$data["kaliresType"] = $patientLogged->niveau;
		$envoiDemande = $scd->envoiDemandePresc($data);
		if($envoiDemande > 0) {
			klRedir ("listePrescription.php", 3,_s("Prescription validée"));
		} else {
			klRedir ("listePrescription.php", 3,_s("La prescription n'a pas pu être validée car elle a déjà été saisie sur KaliSil"));
		}
		die();
	}
	
} elseif($_POST['choix'] == "enregistrerPresc" || $_POST['choix'] == "validerPresc") {
	afficheMessage(_s("Session incorrecte : aucune donnée enregistrée"));
}


if(!isset($dataPrescription)) {
	if (isset($_SESSION['dataPrescription']))
		$dataPrescription = $_SESSION['dataPrescription'];
	else {
		/** Requete SOAP **/
		$scp = new SoapClientPrescription();
		$params = Array("idReference"=>$patientLogged->id, "idType"=>$patientLogged->niveau);
		$dataPrescription = $scp->getDataPrescription($params);
	}
} 

?>
<script type="text/javascript">

var retNum = true;
var erreurMessage = "";
var msgNum ="";
var isMaroc = <?=(isSrOption("typeLabo","maroc") ? 1 : 0)?>;

function changeCouleur(box, id) {
	if($(box).prop("checked")==true) {
		$("#"+id).removeClass("bg-light").addClass("bg-primary text-white");
	} else {
		$("#"+id).removeClass("bg-primary text-white").addClass("bg-light");
	}
}

function toHide(nom){
	var ret = true;
	$("input:checked").each(function(){
		if($(this).attr("data-code") && $(this).attr("data-code").indexOf(",")>0){
			tabCode = $(this).attr("data-code").split(',');
			if($.inArray(nom, tabCode)>=0){
				ret = false;
			}
		}else{
			if($(this).attr("data-code") == nom){
				ret = false;
			}
		}	
	});
	return ret;
}

function showTR(nom){

	var tabAna = nom.split(",");
	for(var i=0 ; i<tabAna.length ; i++){
		$("tr[dataType='data["+tabAna[i]+"]']").each(function() {
			if($( this ).css("display") == "none"){
	  			$( this ).show();
	  			$(this).prop('disabled', false);
	  		}else{
	  			if(toHide(tabAna[i]) === true){
					$( this ).hide();
					$(this).prop('disabled', true);
				}
			}		
		});
	}
}

function checkNum(elm){
	erreurMessage = "";
	var nom = elm.getAttribute("nom");
	var msg = "- La valeur de \""+nom+"\" doit être numérique.\r\n";
	var valSaisie = elm.value;
	
	if(elm.value.length != 0 && !valSaisie.match(/^[<>]?\s?[0-9]+\.?[0-9]*$/g)){
		alert("La valeur doit être numérique.\r\nCaractères autorisés : de 0 à 9, <, >, espace et point.");
		if(msgNum.indexOf(msg)){
			msgNum += msg;
		}	
		retNum = false;
	}else{
		msgNum = msgNum.replace(msg,"") 
		retNum=true;
	}	
}

var cleCalcule= '';

function verifNumSecu(numSecu) {
	cleCalcule= '';
	var cleValidation=97;
	var indiceCorse=6;
	numSecu = numSecu.toUpperCase();
	
	if (numSecu.length==0) return true;
	else {
    	if (numSecu.length!=15 && numSecu.length!=14 && numSecu.length!=13) return false;
    	else {
    		numSecu = numSecu.replace("2A","19");
    		numSecu = numSecu.replace("2B","18");
    		if (!lib_isInt(numSecu)) return false;
    		var num = numSecu.substr(0, 13);
    		var cle = numSecu.substr(13, 2);
    		tmp = cleValidation - (num % cleValidation);
    		cleCalcule= tmp;
		if (cleCalcule<10) cleCalcule="0"+cleCalcule;

    		if (tmp==cle) return true;
    		else return false;
    	
    	}
	}
}

function validePrescription(event,leForm){
	var ret = true;
 	var erreurAccueil = false;
 	var labelNS = "le numéro de sécurité sociale";
	erreurMessage = "";
	
	if(isMaroc == 1) {
		labelNS = "le numéro CIN";
	}
	
	if(getById("numPermanent").value == "" && getById("numPatientExterne").value == "" && getById("numeroSecu").value == ""){
		erreurMessage += "- Vous devez saisir le NKaliSil, l'IPP ou " + labelNS + ".\r\n";
		ret=false;
	}
	$('#nom').val($('#inputNom').val());
	
	if (isMaroc == 0 && getById("numeroSecu").value != "") {
		if (verifNumSecu(getById("numeroSecu").value)===false) {
			if (cleCalcule=='') {
				erreurMessage += "- Le numéro de sécurité sociale doit contenir 15 caractères.\r\n";
				ret=false;
			} else {
				erreurMessage += "- Le numéro de sécurité sociale est faux (clé calculé = "+cleCalcule+")\r\n";
				ret=false;
			}
		}

	}
	
	if(getById("nomJeuneFille").value == "" || getById("prenom").value == ""){
		erreurMessage += "- Vous devez saisir le nom et le prénom du patient.\r\n";
		ret=false;
	}
	
	if(getById("civilite").value == ""){
		erreurMessage += "- Vous devez choisir la civilité du patient.\r\n";
		ret=false;
	}
	
	if(getById("dateOrdonnance").value == ""){
		erreurMessage += "- Vous devez saisir la date d'ordonnance.\r\n";
		ret=false;
	}
	
	if (getById("rangNaissance").value != "" && !((getById("rangNaissance").value * 1) >= 0)) {
		erreurMessage += "- Le rang gémellaire doit être un chiffre";
		ret=false;
	}
	
	$.each($("input[name^='data[infoAccueil]']"), function() {
	  if($(this).is(":visible") && $(this).data("valVide") == 0 && $(this).val() == ""){
	  	erreurAccueil = true;
	  	ret=false;
	  	$(this).css("border","1px solid red");
	  }
	});
	
	$.each($("select[name^='data[infoAccueil]']"), function() {
	  if($(this).is(":visible") && $(this).data("valVide") == 0 && $(this).val() == ""){
	  	erreurAccueil = true;
	  	ret=false;
	  	$(this).css("color","red");
	  }
	});
	
	
	erreurMessage += msgNum;
	if(erreurAccueil) erreurMessage += "- Vous devez renseigner les informations accueil obligatoires.\r\n";
	
	if (erreurMessage != "") {
		alert(erreurMessage);
	}
	
	ret = ret && retNum && validator_verifie_date(event,getById('dateNaissance'),false,true); 

	return ret;
}

function completionForm(numIPP) {
	$('#imageWait').show(); 
	$("#numPatientExterne").prop('disabled', true);
	if(isMaroc == 1) {
		labelNS = "CIN";
	} else {
		labelNS = "NSS"
	}

	$.getJSON('prescription.ajax.php',{'numIPP':numIPP}, function(data) {
		if (data != false) {
			var strConfirm = "Ce numéro IPP correspond aux informations suivantes : \n"
							 +(data["civilite"] != "" ? "- Civilité : "+data["civilite"]+"\n" : "")
							 +(data["nom"] != "" ? "- Nom usuel : "+data["nom"]+"\n" : "")
							 +(data["nomJeuneFille"] != "" ? "- Nom de naissance : "+data["nomJeuneFille"]+"\n" : "")
							 +(data["prenom"] != "" ? "- Prénom : "+data["prenom"]+"\n" : "")
							 +(data["dateNaissance"] != "" ? "- Date de naissance : "+afficheDate(data["dateNaissance"])+"\n" : "")
							 +(data["numSecu"] != "" ? "- " + labelNS + " : "+data["numSecu"]+"\n" : "")
							 +(data["numPermanent"] != ""  && data["numPermanent"] != null ? "- Numéro patient KaliSil : "+data["numPermanent"]+"\n" : "")
							 +(data["rangNaissance"] != null ? "- Rang gémellaire : "+data["rangNaissance"]+"\n" : "")
							 +"\n Confirmez-vous ces informations ?"
		
			if (confirm(strConfirm)) {
				$("#civilite").val(data["civilite"]);				
				$("#inputNom").val(data["nom"]);
				if (data["nomJeuneFille"] != "") $("#nomJeuneFille").val(data["nomJeuneFille"]);
				else $("#nomJeuneFille").val(data["nom"])
				$("#prenom").val(data["prenom"]);
				$("#dateNaissance").val(afficheDate(data["dateNaissance"]));
				$("#rangNaissance").val(data["rangNaissance"]);
				$("#numPermanent").val(data["numPermanent"]);
				$("#numeroSecu").val(data["numSecu"]);
			}
			$('#imageWait').hide();
			$("#numPatientExterne").prop('disabled', false);
		}
		else {
			alert("Aucun patient ne correspond à ce numéro IPP.");
			$('#imageWait').hide();		
			$("#numPatientExterne").prop('disabled', false);
		}
	});
}

</script>
<?php
$keyForm = uniqid(date("YmdHis"));
$_SESSION["keyFormSession"] = $keyForm;

$dateAdmission = "";
$heureAdmission = "";
$dateOrdonnance = date("d-m-Y");
$ipp = "";
$demandeUrgente = 0;
$numeroAdmission = "";
$numPermanent = "";
$numSecu = "";
$nomNaissance = "";	
$dateNaissance = "";
$nomUsuel = "";
$sexe = "";
$prenom = "";
$rangGemellaire = "1";
$commentaire = "";
$analyseSupp = "";
$analysePresc = array();
$infoAcceuil = array();
$typeRequete = "insert";
$statusPrescription = "saisie";
$nomJF = 1;
$datePrelevement = date("d-m-Y");
$heurePrelevement = date("H:i");
$prelevePar = "";

if(isset($idPrescription) && $idPrescription > 0) {
	$scd = new SoapClientPrescription();	 	
	$info = $scd->getPrescription(array("idPrescription"=>$idPrescription,"idIntervenant"=>$patientLogged->id,"typeIntervenant"=>$patientLogged->niveau));
	
	
	if($info != false) {
		$infoPrescription = stripslashesRecurse(unserialize($info["donneeHPRIM"]));
	
		$dateAdmission = $infoPrescription["dateAdmission"];
		$heureAdmission = $infoPrescription["heureAdmission"];
		$dateOrdonnance = $infoPrescription["dateOrdonnance"];
		$ipp = $infoPrescription["numPatientExterne"];
		$civilitePresc = $infoPrescription["civilite"];
		$numeroAdmission = $infoPrescription["numDemandeExterne"];
		$numPermanent = $infoPrescription["numPermanent"];
		$numSecu = $infoPrescription["caisse"]["numeroSecu"];
		$nomNaissance = $infoPrescription["nomJeuneFille"];
		$dateNaissance = $infoPrescription["dateNaissance"];
		$nomUsuel = $infoPrescription["nom"][0];
		$sexe = $infoPrescription["sexe"];
		$demandeUrgente = $infoPrescription["urgent"];
		$prenom = $infoPrescription["nom"][1];
		$rangGemellaire = $infoPrescription["caisse"]["rangNaissance"];
		$commentaire = $infoPrescription["commentaires"]["autre"];
		$analyseSupp = $infoPrescription["commentaires"]["analyseSup"];
		$analysePresc = $infoPrescription["analyses"];
		$infoAcceuil = $infoPrescription["infoAccueil"];
		$typeRequete = "update";
		$statusPrescription = $info["statusPrescription"];
		$idPrescription = $info["id"];
		$datePrelevement = $infoPrescription["datePrelevement"];
		$heurePrelevement = $infoPrescription["heurePrelevement"];
		$prelevePar = $infoPrescription["prelevePar"];
		
		//Recuperation civilite
		$nomJF = 0;
		foreach($dataPrescription["tabCivilite"] as $keyCiv => $valueCiv) {
			if(is_array($valueCiv) && in_array($civilitePresc,$valueCiv)) {
				$nomJF = $valueCiv["jeuneFille"];
			}
		}
		
	} else {
		klRedir ("listePrescription.php", 3,_s("Vous n'avez pas le droit d'accéder à cette prescription"));
		die();
	}
}

if(isset($_POST["dataPatient"])){
	$dataPatient = $_POST["dataPatient"];
	$ipp 				= _secho($dataPatient["ipp"],"sts");
	$numeroAdmission 	= _secho($dataPatient["numAdm"],"sts");
	$numPermanent 		= _secho($dataPatient["numPerm"],"sts");
	$numSecu 			= _secho($dataPatient["numSecu"],"sts");
	$nomNaissance 		= _secho($dataPatient["nomNaissance"],"sts");	
	$dateNaissance 		= afficheDate($dataPatient["dateN"]);
	$nomUsuel 			= _secho($dataPatient["nomUsuel"],"sts");
	$sexe 				= _secho($dataPatient["sexe"],"sts");
	$prenom 			= _secho($dataPatient["prenom"],"sts");
	$rangGemellaire 	= _secho($dataPatient["rangG"],"sts");
	$civilitePresc 		= _secho($dataPatient["civ"],"sts");
}

?>
<FORM id="prescription" AUTOCOMPLETE=off NAME="principal" METHOD="POST" ACTION="prescription.php" onSubmit="return validePrescription(event,this);" class="needs-validation" novalidate>
	<div class="container py-4">
		<div class="card mb-4">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0"><?=_s("Légende");?></h5>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-4">
						<span class="badge bg-danger">AAA</span>: <?=_s("Champs obligatoires");?>
					</div>
					<div class="col-md-4">
						<span class="badge bg-warning">*</span>: <?=_s("Au moins un des champs doit être renseigné");?>
					</div>
					<div class="col-md-4">
						<span class="badge bg-info"><i class="fas fa-calendar"></i></span>: <?=sprintf(_s("Utilisez %sEspace%s pour remplir les champs de date et heure avec la date actuelle (JJ-MM-AAAA / HH:MM)"),"<span class=\"fw-bold\">","</span>");?>
					</div>
				</div>
			</div>
		</div>

		<input id="form_choix" type="hidden" name="choix" value="" />
		<input id="idPrescripteur" type="hidden" name="data[kaliresReference]" value="<?=$patientLogged->id?>" />
		<input id="typeRequete" type="hidden" name="data[typeRequete]" value="<?=$typeRequete;?>">
		<input id="idSiteDest" type="hidden" name="data[idSiteDest]" value="<?=$dataPrescription["idSiteDest"]?>" />
		<input id="idPrescription" type="hidden" name="data[idPrescription]" value="<?=$idPrescription?>" />
		<input id="origine" type="hidden" name="data[origine]" value="kalires" />
		<input type="hidden" name="keyFormForm" value="<?=$keyForm;?>">

		<!-- Admission Information -->
		<div class="card mb-4">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0"><?=_s("Informations sur l'admission");?></h5>
			</div>
			<div class="card-body">
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-calendar-alt text-primary"></i>
							<?=_s("Date d'admission");?>
						</label>
						<?=navGetInputDate(Array("id" => "datePrescription", "name" => "data[dateAdmission]", "dataType" => "date", "value" => $dateAdmission, "class"=>"form-control"),true,false,true,false,true)?>
					</div>
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-clock text-primary"></i>
							<?=_s("Heure d'admission");?>
						</label>
						<div class="input-group">
							<input type="text" class="form-control" id="heureAdmission" name="data[heureAdmission]" value="<?=_secho($heureAdmission,"input");?>" onKeyUp="if(kKeyCode(event,32)) $(this).val(getHeure())" onBlur="formatSaisieHeure(this,true);" />
							<button class="btn btn-outline-secondary" type="button" onClick="$('#heureAdmission').val(getHeure());">
								<i class="fas fa-clock"></i>
							</button>
						</div>
					</div>
				</div>

				<div class="row g-3 mt-2">
					<div class="col-md-6">
						<label class="form-label required">
							<i class="fas fa-calendar-check text-primary"></i>
							<?=_s("Date d'ordonnance");?>
						</label>
						<?=navGetInputDate(Array("id" => "dateOrdonnance", "name" => "data[dateOrdonnance]", "dataType" => "date", "value" => $dateOrdonnance, "class"=>"form-control"),true,false,true,false,true)?>
					</div>
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-id-card text-primary"></i>
							<?=_s("N° IPP");?> *
						</label>
						<div class="input-group">
							<input type="text" class="form-control" id="numPatientExterne" <?= ($patientLogged->niveau != "preleveur") ? "onKeyUp=\"if(kKeyCode(event,13)) { completionForm(this.value);  }\"" : "" ?> name="data[numPatientExterne]" value="<?=_secho($ipp,"input");?>" />
							<span class="input-group-text" id="imageWait" style="display:none;">
								<i class="fas fa-spinner fa-spin"></i>
							</span>
						</div>
					</div>
				</div>

				<div class="row g-3 mt-2">
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-exclamation-triangle text-primary"></i>
							<?=_s("Demande urgente");?>
						</label>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="data[urgent]" id="urgentOui" value="1" <?php if($demandeUrgente==1) echo "checked=\"checked\""?>/>
							<label class="form-check-label" for="urgentOui"><?=_s("Oui");?></label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="data[urgent]" id="urgentNon" value="0" <?php if($demandeUrgente==0) echo "checked=\"checked\""?> />
							<label class="form-check-label" for="urgentNon"><?=_s("Non");?></label>
						</div>
					</div>
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-hashtag text-primary"></i>
							<?=_s("N° d'Admission");?>
						</label>
						<input type="text" class="form-control" id="numDemandeExterne" name="data[numDemandeExterne]" value="<?=_secho($numeroAdmission,"input");?>" />
					</div>
				</div>
			</div>
		</div>

		<!-- Patient Information -->
		<div class="card mb-4">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0"><?=_s("Informations sur le patient");?></h5>
			</div>
			<div class="card-body">
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label required">
							<i class="fas fa-calendar text-primary"></i>
							<?=_s("Date de naissance");?>
						</label>
						<?=navGetInputDate(Array("id" => "dateNaissance", "name" => "data[dateNaissance]", "dataType" => "date","class"=>"form-control","value" => $dateNaissance),true,false,true,false,true)?>
					</div>
					<div class="col-md-6">
						<label class="form-label required">
							<i class="fas fa-user text-primary"></i>
							<?=_s("Civilité");?>
						</label>
						<select id="civilite" class="form-select" name="data[civilite]">
							<option value="" selected="selected">(<?=_s("Sélectionnez");?>)</option>
							<?php
								foreach($dataPrescription["tabCivilite"] as $civilite){
									$selectedCiv = "";
									if($civilite["civilite"] == $civilitePresc) $selectedCiv="selected=\"selected\"";
									echo '<option value="'.$civilite["civilite"].'" sexe="'.$civilite["sexe"].'" nomJF="'.$civilite["jeuneFille"].'" '.$selectedCiv.'>'.$civilite["civilite"].(($civilite["sexe"] != "")?' ('.$civilite["sexe"].')':'').'</option>';
								}
							?>
						</select>
						<input type="hidden" id="sexe" name="data[sexe]" value="<?=_secho($sexe,"input")?>" />
					</div>
				</div>

				<div class="row g-3 mt-2">
					<div class="col-md-6">
						<label class="form-label required">
							<i class="fas fa-user text-primary"></i>
							<?=_s("Nom de naissance");?>
						</label>
						<input type="text" class="form-control" id="nomJeuneFille" name="data[nomJeuneFille]" value="<?=_secho($nomNaissance,"input");?>" maxlength="50" />
					</div>
					<div class="col-md-6">
						<label class="form-label required">
							<i class="fas fa-user text-primary"></i>
							<?=_s("Prénom");?>
						</label>
						<input type="text" class="form-control" id="prenom" name="data[nom][1]" value="<?=_secho($prenom,"input");?>" maxlength="40" />
					</div>
				</div>

				<div class="row g-3 mt-2">
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-user text-primary"></i>
							<?=_s("Nom usuel");?>
						</label>
						<input type="text" class="form-control" id="inputNom" value="<?=_secho($nomUsuel,"input");?>" <?=(($nomJF==0)?"disabled":"")?> maxlength="50" />
						<input type="hidden" id="nom" name="data[nom][0]" value="<?=_secho($nomUsuel,"input");?>" maxlength="50" />
					</div>
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-sort-numeric-up text-primary"></i>
							<?=_s("Rang gémellaire");?>
						</label>
						<input type="text" class="form-control" id="rangNaissance" name="data[caisse][rangNaissance]" value="<?=_secho($rangGemellaire,"input");?>" maxlength="1" />
					</div>
				</div>

				<div class="row g-3 mt-2">
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-id-card text-primary"></i>
							<?=_s("N° Patient KaliSil");?> *
						</label>
						<input type="text" class="form-control" id="numPermanent" name="data[numPermanent]" value="<?=_secho($numPermanent,"input");?>" />
					</div>
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-id-card text-primary"></i>
							<?=$labelNumSecu;?> *
						</label>
						<input type="text" class="form-control" id="numeroSecu" name="data[caisse][numeroSecu]" value="<?=_secho($numSecu,"input");?>" maxlength="15" onBlur="<?=$recupCle;?>"/>
					</div>
				</div>
			</div>
		</div>

		<!-- Analyses Section -->
		<?php if (count($dataPrescription["tabAnalyses"]) > 0) { ?>
		<div class="card mb-4">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0"><?=_s("Analyses");?></h5>
			</div>
			<div class="card-body">
				<div class="row">
					<?php
					$nombreColonnes = 4;
					$i=0;
					$nbAnalyses=0;
					$longueurTronquer=20;
					$analyseDejaFaite = Array();
					$listeCodesPrescription = listeCodePrescription($analysePresc);    
					$infoAControle = Array();

					foreach($dataPrescription["tabAnalyses"] as $chapitre){
						foreach($chapitre as $code => $analyse){
							if(!is_array($analyse)){
								if($i > 0){
									while($i < $nombreColonnes){
										echo "<div class=\"col-md-".(12/$nombreColonnes)."\"></div>";
										$i++;
									}
									echo "</div><div class=\"row\">";
								}
								echo "<div class=\"col-12\">
										<h6 class=\"text-primary mb-3\">".$chapitre["nomChapitre"]."</h6>
									</div>";
								$i=0;
							} else {
								$champs = Array();
								$bonusTitle="";
								if(isset($analyse["groupe"])){
									$bonusTitle = "\nTubes : ".$analyse["listeTube"];
								}
								if($i==$nombreColonnes){
									echo "</div><div class=\"row\">";
									$i=0;
								}
						
								$selectedAnalysePres = "";
								$classTdSelected = "bg-light";
								if(is_array($listeCodesPrescription) && count($listeCodesPrescription)>0) {
									if(in_array($code,$listeCodesPrescription)) {
										$selectedAnalysePres = "checked=\"checked\"";
										$classTdSelected = "bg-primary text-white";
									}
								}            
					
								if(is_array($analyse["infoAccueil"])){    
									foreach($analyse["infoAccueil"] as $tabAna){
										$champs[$tabAna["nomAnalyse"]] = "\"".$tabAna["nomAnalyse"]."\"";
									}
								}
								if($oldCode==""){
									$oldCode=$code;
								}
								if(isset($analyse["infoAccueil"])){
									echo "<div class=\"col-md-".(12/$nombreColonnes)." ".$classTdSelected."\" id=\"".$code."_".$nbAnalyses."\">
											<div class=\"form-check\">
												<input class=\"form-check-input\" type=\"checkbox\" name=\"data[analyses][][actes][]\" data-code=\"".implode(",",array_keys($champs))."\" onclick='changeCouleur(this, \"".$code."_".$nbAnalyses."\");showTR(\"".implode(",",array_keys($champs))."\");' value=\"".$code."\" ".$selectedAnalysePres."/>
												<label class=\"form-check-label\" title=\"".$analyse["nom"].$bonusTitle."\">".tronquer($analyse["nom"], $longueurTronquer)."</label>
											</div>
										</div>";
									$countTab = true;
									foreach($analyse["infoAccueil"] as $data){
										$data = stripslashesRecurse($data);
										$span = "";
										if($countTab && !isset($analyse["groupe"])){
											$span = "<div class=\"col-md-3\">".$data["nomAnalyse"]."</div>";
											$countTab = false;
										}else if(isset($analyse["groupe"])){
											$span = "<div class=\"col-md-3\">".$data["nomAnalyse"]."</div>";
										}else{
											$span="";
										}
										$infoAcceuilPresent = false;
										$displayInfoAcceuil = "style=\"display:none;\" disabled=\"disabled\"";
										$valueInfoAcceuil = "";    
										if(isset($data["valeurDefaut"])){
											$valueInfoAcceuil = $data["valeurDefaut"];
											$infoAcceuilPresent = true;                        
										}
										if(is_array($infoAcceuil) && array_key_exists($data["id"],$infoAcceuil)) {
											$infoAcceuilPresent = true;
											$displayInfoAcceuil = "style=\"display:block;\"";
											$valueInfoAcceuil = $infoAcceuil[$data["id"]];
										}
						
										if($data["valeurVideAutorise"] == 0) $classObligatoire = "required";
										else $classObligatoire = "";
										if($code==$oldCode || ($code!=$oldCode && !in_array($data["nomAnalyse"],$analyseDejaFaite))){
											switch($data["type"]){
												case "texteCodifie" :
												case "texte" :
													if(($data["type"]=="texte" || $data["type"]=="texteCodifie") && count($data["texteCodifie"])>0){
														echo "<div class=\"row ".$displayInfoAcceuil." dataType=\"data[".$data["nomAnalyse"]."]\" >
																".$span."
																<div class=\"col-md-3 ".$classObligatoire."\">".$data["nom"]."</div>
																<div class=\"col-md-6\">
																	<select data-val-vide=\"".$data["valeurVideAutorise"]."\" id=\"".$data["id"]."\" name=\"data[infoAccueil][".$data["id"]."]\" class=\"form-select\">
																		<option selected=\"selected\" value=\"\">Choisissez une valeur</option>";
																		foreach($data["texteCodifie"] as $textCodifie){
																			$selectedTexte = "";
																			if($infoAcceuilPresent === true && $textCodifie == $valueInfoAcceuil) $selectedTexte = "selected=\"selected\"";
																			echo "<option ".$selectedTexte.">".$textCodifie."</option>";
																		}
														echo "</select></div></div>";
													}
													break;
												case "date" :
													echo "<div class=\"row ".$displayInfoAcceuil." dataType=\"data[".$data["nomAnalyse"]."]\" >
															".$span."
															<div class=\"col-md-3 ".$classObligatoire."\">".$data["nom"]."</div>
															<div class=\"col-md-6\">";
													echo navGetInputDate(Array("id" => $data["id"], "name" => "data[infoAccueil][".$data["id"]."]", "dataType" => "date", "value" => $valueInfoAcceuil, "data-val-vide" => $data["valeurVideAutorise"], "class"=>"form-control"),true,false,true,false,true);
													echo "</div></div>";
													break;
												case "heure" :
													echo "<div class=\"row ".$displayInfoAcceuil." dataType=\"data[".$data["nomAnalyse"]."]\" >
															".$span."
															<div class=\"col-md-3 ".$classObligatoire."\">".$data["nom"]."</div>
															<div class=\"col-md-6\">
																<div class=\"input-group\">
																	<input type=\"text\" class=\"form-control\" data-val-vide=\"".$data["valeurVideAutorise"]."\" id=\"".$data["id"]."\" name=\"data[infoAccueil][".$data["id"]."]\" value=\""._secho($valueInfoAcceuil,"input")."\" onKeyUp=\"if(kKeyCode(event,32)) ajouteHeure(this)\" onBlur=\"formatSaisieHeure(this,true);\" />
																	<button class=\"btn btn-outline-secondary\" type=\"button\" onClick=\"$('#".$data["id"]."').val(getHeure());\">
																		<i class=\"fas fa-clock\"></i>
																	</button>
																</div>
															</div>
														</div>";
													break;
												case "numerique" :
													echo "<div class=\"row ".$displayInfoAcceuil." dataType=\"data[".$data["nomAnalyse"]."]\" >
															".$span."
															<div class=\"col-md-3 ".$classObligatoire."\">".$data["nom"]."</div>
															<div class=\"col-md-6\">
																<div class=\"input-group\">
																	<input type=\"text\" class=\"form-control\" data-val-vide=\"".$data["valeurVideAutorise"]."\" name=\"data[infoAccueil][".$data["id"]."]\" id=\"".$data["id"]."\" nom=\"".$data["nom"]."\" value=\""._secho($valueInfoAcceuil,"input")."\" onblur=\"checkNum(this);\" />
																	<span class=\"input-group-text\">".$data["unite"]."</span>
																</div>
															</div>
														</div>";
													break;
											}
											$analyseDejaFaite[$data["id"]] = $data["nomAnalyse"];
											$oldCode = $code;
										}
									}
								} else {
									echo "<div class=\"col-md-".(12/$nombreColonnes)." ".$classTdSelected."\" id=\"".$code."_".$nbAnalyses."\">
											<div class=\"form-check\">
												<input class=\"form-check-input\" type=\"checkbox\" name=\"data[analyses][][actes][]\" onclick='changeCouleur(this, \"".$code."_".$nbAnalyses."\");' value=\"".$code."\" ".$selectedAnalysePres."/>
												<label class=\"form-check-label\" title=\"".$analyse["nom"].$bonusTitle."\">".tronquer($code." - ".$analyse["nom"], $longueurTronquer)."</label>
											</div>
										</div>";
								}
								$i++;
								$nbAnalyses++;
							}
						}
					}
					if($i > 0){
						while($i < $nombreColonnes){
							echo "<div class=\"col-md-".(12/$nombreColonnes)."\"></div>";
							$i++;
						}
						echo "</div>";
					}
					?>
				</div>
			</div>
		</div>
		<?php } ?>

		<!-- Additional Comments Section -->
		<div class="card mb-4">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0"><?=_s("Commentaires");?></h5>
			</div>
			<div class="card-body">
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-clipboard-list text-primary"></i>
							<?=_s("Analyses supplémentaires");?>
						</label>
						<textarea class="form-control" id="analyseSup" name="data[commentaires][analyseSup]" rows="4"><?=_secho($analyseSupp,"input");?></textarea>
					</div>
					<div class="col-md-6">
						<label class="form-label">
							<i class="fas fa-comment text-primary"></i>
							<?=_s("Commentaire");?>
						</label>
						<textarea class="form-control" id="commentaire" name="data[commentaires][autre]" rows="4"><?=_secho($commentaire,"input");?></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- Form Actions -->
		<div class="d-flex justify-content-end gap-2">
			<button type="button" class="btn btn-secondary" onclick="history.back()">
				<i class="fas fa-arrow-left"></i> <?=_s("Retour");?>
			</button>
			<button type="submit" class="btn btn-primary" onclick="document.getElementById('form_choix').value='enregistrerPresc';">
				<i class="fas fa-save"></i> <?=_s("Enregistrer");?>
			</button>
			<button type="submit" class="btn btn-success" onclick="document.getElementById('form_choix').value='validerPresc';">
				<i class="fas fa-check"></i> <?=_s("Valider");?>
			</button>
		</div>
	</div>
</FORM>

<style>
.required:after {
    content: " *";
    color: red;
}

.form-label {
    font-weight: 500;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

.card-header {
    border-bottom: none;
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.form-select:focus, .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(11, 112, 206, 0.25);
}

.btn {
    padding: 0.5rem 1rem;
}

.btn i {
    margin-right: 0.5rem;
}

@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .row {
        margin-left: -0.5rem;
        margin-right: -0.5rem;
    }
    
    .col-md-6, .col-md-3 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
}
</style>

<script>
// Add Bootstrap form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Update the changeCouleur function to work with Bootstrap classes
function changeCouleur(box, id) {
    if($(box).prop("checked")==true) {
        $("#"+id).removeClass("bg-light").addClass("bg-primary text-white");
    } else {
        $("#"+id).removeClass("bg-primary text-white").addClass("bg-light");
    }
}
</script>
<?php
afficheFoot();
?>