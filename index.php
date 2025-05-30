<?php                                  
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");  
header("Cache-Control: no-cache, must-revalidate");  
header("Pragma: no-cache");

if(!isset($choix) || $choix == "") {
	define("KALILAB_SESSION_NO_UPDATE","1");
}

include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");
session_start();

// Add modern meta tags and CSS/JS includes
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo _s("KaliRes"); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style2.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favico.kalires.ico">
</head>
<body class="bg-light">
<?php
if($conf['debug']) {
	$conf['debug_filtrageAcces'] = true;
}

if( (getSrOption("kaliResMedecinPermalink") || getSrOption("kaliResCorrespondantPermalink") || getSrOption("kaliResPreleveurPermalink")) && isset($_GET["sha"]) && isset($_GET["login"]) ) {
	if(logWithSha(Array("sha"=>$_GET["sha"],"login"=>$_GET["login"],"numId"=>$_GET["numId"],"user"=>$_GET["user"]))) {
		if($patientLogged->isAuth()) {
            $_SESSION["patientLogged"] = $patientLogged;
		} else {
			$_SESSION["loginError"] = 4;
			$goToDeny = true;
		}
	} else {
		$_SESSION["loginError"] = 5;
		$goToDeny = true;
	}
}

if($patientLogged && $patientLogged->isAuth() && isset($_GET["to"])){
	$lienTo = "";
	
	if($_SESSION["accesPermalinkLevel"] == 0 || $_SESSION["accesPermalinkLevel"] == 2){
		switch($_GET["to"]){
			case "consultation":
				if(!$_GET["numId"] && !$_GET["numIPP"]){
					$_SESSION["loginError"] = 6;
					$goToDeny = true;
				} else {
					$lienTo = "consultation.php?numId=".$_GET["numId"]."&numIPP=".$_GET["numIPP"];
					unset($_SESSION["listeDemandesSess"]);
					unset($_SESSION["listeDemandesNomSess"]);
				}
				break;
			default :
				$_SESSION["loginError"] = 6;
				$goToDeny = true;
				break;
		}	
	} else {
		switch ($_GET["to"]){
			case "prescription":
				$lienTo = "prescription.php";
				break;
			case "consultation":
				$lienTo = "consultation.php".((isset($_GET["numId"]))?("?numId=".$_GET["numId"]):(""));
				break;
			default :
				$_SESSION["loginError"] = 6;
				$goToDeny = true;
				break;
		}
	}
	if(!$goToDeny && $lienTo != "") {	
		header("Location:".$lienTo);
		exit;
	} else {
		$logout = 1;
	}
}

if ($logout == 1 && $patientLogged && $patientLogged->isAuth()) {
	$patientLogged->logout();
}

if($choix == "changePassword") {
	if($_SESSION["keyForm"] != "" && $keyFormForm == $_SESSION["keyForm"]) {
		if(($sPasswordOld != "" || $_SESSION["keyFormToken"][$_SESSION["keyForm"]] != "") && $sPassword1 != "" && $sPassword2 != "") {
			if(strlen($sPassword1) >= 5) {
				if($sPassword1 == $sPassword2) {
					$sc = new SoapClientKalires();
					$changePassw = $sc->changePassword($sNiveau, $sLogin, $sPasswordOld, $sPassword1, $_SESSION["keyFormToken"][$_SESSION["keyForm"]]);
					if($changePassw->result == "1") {
						$sMsg=""._s("Le nouveau mot de passe a bien �t� enregistr�")."";
						$patientLogged = new PatientLogged($sLogin, $sPassword1, "", $sNiveau);
					} elseif($changePassw->result == "2") {
						$sMsg="<font color=red>"._s("Erreur : le nouveau mot de passe doit �tre diff�rent de l'ancien")."</font>";
					} else {
						$sMsg="<font color=red>"._s("Erreur : le changement de mot de passe a �chou�")."</font>";
					}
				} else {
					$sMsg="<font color=red>"._s("Erreur : les 2 mots de passe ne sont pas identiques")."</font>";
				}
			} else {
				$sMsg="<font color=red>"._s("Erreur : le mot de passe doit faire au minimum 5 caract�res")."</font>";
			}
		} else {
			$sMsg="<font color=red>"._s("Erreur : veuillez remplir tous les champs requis")."</font>";
		}
	} else {
		$sMsg="<font color=red>"._s("Erreur : session incorrecte")."</font>";
	}
}
unset($_SESSION["keyForm"]);
unset($_SESSION["keyFormToken"]);
unset($_SESSION["refAnalyse"]);
unset($_SESSION["accesPC"]);
unset($_SESSION["accesPermalink"]);

$sessSessionIdKaliResOld = $_COOKIE["sessSessionIdKaliRes"];
$sessSessionIdKaliRes = uniqid("sessLogKaliRes");
setcookie("sessSessionIdKaliRes",$sessSessionIdKaliRes,time()+(52*7*24*60*60));  /* expire dans une ann�e */

if ($sessionId!="" && ($sessSessionIdKaliResOld!=$sessionId || $sessSessionIdKaliResOld=="")){

	$sMsg = _s("Session incorrecte ou expir�e : veuillez saisir votre mot de passe une nouvelle fois.");
	
}elseif ($sessionId!="" && isset($sessSessionIdKaliResOld)) {
	unset($_SESSION["patientLogged"]);

	if (trim($sNumSecu)!="" && trim($sPassword)!="") $patientLogged = new PatientLogged( $sNumSecu , $sPassword, $sNumDoss, "patient" );
	elseif (trim($sNumAdeli)!="" && trim($sPasswordM)!="") $patientLogged = new PatientLogged( $sNumAdeli , $sPasswordM, $sNumDossM, "medecinCorrespondant" );

	if ($patientLogged && $patientLogged->isLogin()) {

		if( $patientLogged->passwordExpired == 3 ){
			
			$patientLogged->logout();
			?><script type="text/javascript"> document.location.href='getPassword.php?sNiveau=patient'; </script><?php
			afficheFoot();
			die();
		
		} elseif( $patientLogged->passwordExpired == 1 || $patientLogged->passwordExpired == 2 ){
		
			afficheHead(_s("Identification"),"",false);
			entete();
			$keyForm = uniqid(date("YmdHis"));
			$_SESSION["keyForm"] = $keyForm;
			$_SESSION["keyFormToken"][$keyForm] = $patientLogged->passwordToken;
			echo "<form name=principal action=\"index.php\" method=post>";
			echo "<input type=hidden name=choix value=\"changePassword\">"
				."<input type=\"hidden\" name=\"keyFormForm\" value=\"".$keyForm."\">"
				."<input type=\"hidden\" name=\"sLogin\" value=\"".(($patientLogged->niveau=="patient")?($sNumSecu):($sNumAdeli))."\">"
				."<input type=\"hidden\" name=\"sNiveau\" value=\"".$patientLogged->niveau."\">"
				."<input type=\"hidden\" name=\"sessionId\" value=\"".$sessSessionIdKaliRes."\">";
			?>
				<table class="corps" align=center cellpadding="2" cellspacing="3" border="0" style="border:1px solid #bbb;">
					<?php  if($patientLogged->passwordExpired == 2) { ?>
						<tr class=titre><td align=center colspan=2><?=_s("Votre mot de passe a expir�, veuillez saisir un nouveau mot de passe personnel");?> :</td></tr>
					<?php } else { ?>
						<tr class=titre><td align=center colspan=2><?=_s("Veuillez saisir un mot de passe personnel pour acc�der � KaliRes");?> :</td></tr>
					<?php } ?>
					<tr class=corpsFonce><td align=center colspan=2 style="font-size:11px;"><?=_s("Note : le mot de passe doit faire au minimum 5 caract�res et doit �tre diff�rent du dernier utilis�.");?></td></tr>
					<tr><td align=right><?=_s("Identifiant");?> : </td><td><?=(($patientLogged->niveau=="patient")?($sNumSecu):($sNumAdeli));?></td></tr>
					<?php if($patientLogged->passwordToken != '') { ?>

					<?php } else { ?>
						<tr><td align=right><?=_s("Mot de passe actuel");?> : </td><td><input type="Password" value="" name="sPasswordOld" autocomplete="off" ></td></tr>
					<?php } ?>
					<tr><td colspan=2 class=titre></td></tr>
					<tr><td align=right><?=_s("Nouveau mot de passe personnel");?> : </td><td><input type="Password" value="" name="sPassword1" autocomplete="off" ></td></tr>
					<tr><td align=right><?=_s("Nouveau mot de passe personnel (v�rification)");?> : </td><td><input type="Password" value="" name="sPassword2" autocomplete="off" ></td></tr>
					<tr><td align=right></td><td align="right"><input type="submit" name="send" value="<?=_s("Enregistrer");?>"></td></tr>
				</table>
			<?php
			echo "</form>";
			die();
			
		} else {
		
			$_SESSION["patientLogged"] = $patientLogged;
			 
			afficheHead(_s("Identification"),"",false);
			entete();
			
			$patientLogged->loadAccesSession();
			
			if($patientLogged->cgu==0) {
			
				echo "<center>
						<table class=corps>
						<tr>
							<td>
					<form method=post action=index.php onSubmit=\"if(!document.getElementById('accord').checked) {alert('"._s("Vous devez cocher la case prise de connaissance !","javascript")."');return false;}\">"
						."<input type=hidden name=choix value=accepter>"
						."<input type=hidden name=sNumDoss value=\"".$sNumDoss."\">"
						."<br />"
							."<table class=\"corps\" align=center cellpadding=\"2\" cellspacing=\"3\" border=\"0\" style=\"border:1px solid #bbb;\">"
						."<tr><td align=left class=corpsFonce>"._s("Conditions d'utilisation")."</td></tr>"
						."<tr><td align=left class=corps>"
							.nl2br(getSrOption("kaliresCGU"));
					if($patientLogged->datePasswordExpired != "") {
						echo "<br /><br />".sprintf(_s("Votre mot de passe est valable jusqu'au %s"),"<b>".afficheDate($patientLogged->datePasswordExpired)."</b>");
						if(getSrOption("passwordPerso") == 0) {
							echo "<br />"._s("Pass� cette date vous pourrez en g�n�rer un nouveau sur le site ou en demander un aupr�s du laboratoire.");
						} else {
							echo "<br />"._s("Pass� cette date vous devrez saisir un nouveau mot de passe personnel.");
						}
					}
					echo "<br />"
						."<br /><label><input type=checkbox name=accord id=accord value=1>&nbsp;"._s("J'ai pris connaissance et j'accepte les conditions d'utilisation")."</label>"
						."<br /><br><input type=submit value=\""._s("J'accepte")."\">"
						."</td></tr></table>"
					."</form>
							</td>
						</tr>
					</table></center>";
					
			} else {
			
				unset($_SESSION["rechDossier"]);
				if($sNumDoss == ""){
					klRedir("consultation.php",1,_s("Connexion en cours ..."));
				}else{
					klRedir("afficheDossier.php?sNumDossier=".$sNumDoss."",1,_s("Connexion en cours ..."));
				}
				
			}
					
			afficheFoot();
			die();
		}
			
	} else {
		if ($patientLogged->accountLocked) {
			$_SESSION["loginError"] = 1;
		} else if((empty($sNumSecu) && empty($sNumAdeli)) || empty($sPassword)){
			$_SESSION["loginError"] = 2;
		}else{
			$_SESSION["loginError"] = 3;
		}
		$goToDeny = true;
	}
	
}elseif($choix == "accepter" && $accord){
		
        $patientLogged = $_SESSION["patientLogged"];

		$patientLogged->loadAccesSession();
		
		$patientLogged->setAccordSigne();
		afficheHead(_s("Identification"),"",false);
		entete();

		if($sNumDoss == ""){
			klRedir("consultation.php",1,_s("Connexion en cours ..."));
		}else{
			klRedir("afficheDossier.php?sNumDossier=".$sNumDoss."",1,_s("Connexion en cours ..."));
		}

		afficheFoot();
		die();
}


if($goToDeny) {
	afficheHead(_s("Serveur de résultats")." - ".getSrOption("laboNom"),"",false);
	entete();
	klRedir("denied.php?type=".$_SESSION["loginError"],1,"<span class='text-danger'>"._s("L'authentification a échoué")."</span>");
	afficheFoot();
	die();
}

afficheHead(_s("Serveur de résultats")." - ".getSrOption("laboNom"),"",false);
entete();
if($_SESSION["loginError"] > 0) {
	switch($_SESSION["loginError"]) {
		case 1 :     
			$sMsg=_s("Erreur : Ce compte est verrouillé suite à un trop grand nombre de tentatives de connexion échouées. Veuillez contacter le laboratoire pour rétablir l'accès");
		break;       
		case 2 :     
			$sMsg=_s("Erreur : L'authentification a échoué ou l'un des champs requis est vide");
		break;       
		case 3 :     
			$sMsg=_s("Erreur : L'authentification a échoué. Veuillez vérifier vos identifiant et mot de passe puis vous reconnecter à nouveau");
		break;       
		case 4 :     
			$sMsg=_s("Erreur : Vous devez vous identifier au moins une fois manuellement avec votre mot de passe et accepter les conditions générales pour activer votre compte");
		break;       
		case 5 :     
			$sMsg=_s("Erreur : L'authentification a échoué");
		break;
		case 6 :    
			$sMsg=_s("Erreur : Ce lien permanent ne permet pas d'accéder à cette page. Veuillez saisir vos identifiant et mot de passe.");
		break;
	}
	$sMsg = '<div class="alert alert-danger">'.$sMsg.'</div>';
}
afficheMessage($sMsg,"width:450px;");
unset($_SESSION['KALIRES_OPTION_SITE']);
unset($_SESSION['KALIRES_OPTION']);
unset($_SESSION["loginError"]);
$loginPatient = getSrOption("kaliResPatient");
$loginMedecin = (getSrOption("kaliResMedecin")||getSrOption("kaliResCorrespondant")||getSrOption("kaliResPreleveur"));

?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h1 class="text-center mb-4"><?=_s("Serveur de résultats - Identification");?></h1>
            <form method="POST" action="index.php" name="principal" class="needs-validation" novalidate>
                <div class="row">
                    <?php if ($loginPatient): 
                        $typeLoginPatient = getSrOption("loginPatient");
                        if($typeLoginPatient == "numSecu") {
                            $txtLogin = _s("Numéro de Sécurité Sociale")." : <br><small class='text-muted'>("._s("15 chiffres accolés").")</small>";
                            $txtTxt = _s("Numéro de Sécurité Sociale");
                        } else {
                            $txtLogin = _s("Numéro Patient")." : ";
                            $txtTxt = _s("Numéro Patient");
                        }
                    ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0 text-center"><?=_s("ACCES PATIENT");?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><?=$txtLogin;?></label>
                                        <input type="text" class="form-control" value="<?=$sNumSecu?>" name="sNumSecu" autocomplete="off" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?=_s("Mot de passe");?> :</label>
                                        <input type="password" class="form-control" name="sPassword" autocomplete="off" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?=_s("Numéro de demande");?> : <small class="text-muted">(<?=_s("facultatif");?>)</small></label>
                                        <input type="text" class="form-control" name="sNumDoss" value="<?=$sNumDoss?>" autocomplete="off">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <?php if(getSrOption("passwordPerso")): ?>
                                            <a href="getPassword.php?sNiveau=patient" class="text-decoration-none"><?=_s("Mot de passe oublié ?");?></a>
                                        <?php endif; ?>
                                        <button type="submit" name="send" class="btn btn-primary"><?=_s("Se connecter");?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($loginMedecin): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0 text-center"><?=_s("ACCES PROFESSIONNEL DE SANTE");?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><?=_s("Identifiant");?> :</label>
                                        <input type="text" class="form-control" value="<?=$sNumAdeli?>" name="sNumAdeli" autocomplete="off" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?=_s("Mot de passe");?> :</label>
                                        <input type="password" class="form-control" name="sPasswordM" autocomplete="off" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?=_s("Numéro de demande");?> : <small class="text-muted">(<?=_s("facultatif");?>)</small></label>
                                        <input type="text" class="form-control" name="sNumDossM" value="<?=$sNumDoss?>" autocomplete="off">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="getPassword.php?sNiveau=demandeur" class="text-decoration-none"><?=_s("Mot de passe oublié ?");?></a>
                                        <button type="submit" name="send" class="btn btn-primary"><?=_s("Se connecter");?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="sessionId" value="<?=$sessSessionIdKaliRes?>">
            </form>

            <?php
            if(getSrOption("passwordPatient") == "saisie") {
                if(getSrOption("passwordPerso") > 0) {
                    $txt = getSrOption("accueilSaisiePerm");
                } else {
                    $txt = getSrOption("accueilSaisieTemp");
                }
            } else if(getSrOption("passwordPatient") == "validation") {
                if(getSrOption("passwordPerso") > 0) {
                    $txt = getSrOption("accueilValidePerm");
                } else {
                    $txt = getSrOption("accueilValideTemp");
                }
            } else if(getSrOption("passwordPatient") == "saisieMail") {
                if(getSrOption("passwordPerso") > 0) {
                    $txt = getSrOption("accueilSaisieMailPerm");
                } else {
                    $txt = getSrOption("accueilSaisieMailTemp");
                }
            }
            ?>
            <div class="mt-5">
                <h2 class="h4 mb-3"><?=formateTexteAccueil(getSrOption("titreAccesPatient"));?></h2>
                <div class="card">
                    <div class="card-body">
                        <?=formateTexteAccueil($txt);?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Form validation
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
</script>

<?php
afficheFoot();
?>

</body>
</html>
