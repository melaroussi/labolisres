<?
CLASS paiementEnLigne
{

    var $data;

    function __construct($merchantId,$secretKey,$tpeNumber,$modeTest,$modeRetour){
        $this->set('merchantId',$merchantId);
        $this->set('secretKey',$secretKey);
        $this->set('tpeNumber',$tpeNumber);
        $this->set('modeTest',$modeTest);
        $this->set('modeRetour',$modeRetour);
    }

    function set($key,$val) {
        $this->data[$key] = $val;
    }

    // format  "xxxxx.yy" (no spaces)
    function get($key) {
        return $this->data[$key];
    }

    function getDataRetour(){
      return $_REQUEST;
    }

    function calculateToken() {
        $idPatient = $this->get('customerId');
        $decodeOrderId = explode("-", $this->get('orderId'));
	    $idDemande = $decodeOrderId[0];
	    $numDemande = $this->get('transactionReference');
	    $montant = $this->get('amount');

	    $y = date("Y");
		$md = date("md");
		$h = date("H");
		$is = date("is");
		$idDemandeFixe = sprintf("%012d",$idDemande);
		list($montant1,$montant2) = explode('.',$montant);
		$montant1 = sprintf("%06d",$montant1);
		$montant2 = str_pad($montant2, 2, "0");
		$tokenTmp = sha1("#|Jv".$idPatient."=+&£".$idDemande."*!W<".$numDemande."};Ku".$montant1."J?;s".$montant2."%kEn".$y."Er".$md."9*".$h."£ù".$is.".§Ft");
		$tokenCalcule = $montant2.substr($idDemandeFixe,6,6).substr($tokenTmp,30,6).$h.substr($tokenTmp,0,10).$y.substr($tokenTmp,10,5).$is.substr($tokenTmp,20,10).$md.substr($tokenTmp,15,5).$montant1.substr($idDemandeFixe,0,6);
		$check = md5(substr($numDemande,-5,5));
		$token = $tokenCalcule.$check;
        return $token;
    }

}
