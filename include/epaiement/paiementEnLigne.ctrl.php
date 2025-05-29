<?php

include_once($conf['baseDir']."include/epaiement/paiementEnLigne.class.php");


/**
 * Created by PhpStorm.
 * User: sebastien.eckert
 * Date: 27/05/2016
 * Time: 10:42
 */
class paiementEnLigneCtrl
{

    static function get($type,$merchantId,$secretKey,$tpeNumber,$modeTest='TEST',$modeRetour="callback"){
        switch($type){
            case 'MERCANET':
                include_once("paiementEnLigne_Mercanet.class.php");
                return new paiementEnLigne_Mercanet($merchantId,$secretKey,$tpeNumber,$modeTest,$modeRetour);
            case 'MONETICO':
                include_once("paiementEnLigne_Monetico.class.php");
                return new paiementEnLigne_Monetico($merchantId,$secretKey,$tpeNumber,$modeTest,$modeRetour);
            case 'OGONE':
                include_once("paiementEnLigne_Ogone.class.php");
                return new paiementEnLigne_Ogone($merchantId,$secretKey,$tpeNumber,$modeTest,$modeRetour);
			case 'PAYBOX':
				include_once("paiementEnLigne_Paybox.class.php");
				return new paiementEnLigne_Paybox($merchantId,$secretKey,$tpeNumber,$modeTest,$modeRetour);
        }
        return false;
    }

	function isInterfaceMonoSite($interface){
		return $interface == "PAYBOX";
	}

}