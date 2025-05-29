<?php

include_once ("include/conf.inc.php");
include_once ("include/lib.inc.php");

$scp = new SoapClientPrescription();
$params = Array("idIntervenant"=>$patientLogged->id(), "typeIntervenant"=>$patientLogged->niveau, "numIPP"=>$numIPP);
$data = $scp->getInfoPatientIpp($params);

echo res_json_encode($data);

?>