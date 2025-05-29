<?php
include 'include/conf.inc.php';
include 'include/lib.soap.inc.php';

ini_set("soap.wsdl_cache_enabled", "0");

$sc = new SoapClientKalires();
$sc->getKaliresLogo();


