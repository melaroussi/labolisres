<?php                                  
 /**                                
  * Librairie de modification des dates                     
  *                                 
  *        		                 
  * @package KaliLab                
  * @module KaliLab                
  * @author Netika <info@netika.net>
  * @cvs $Id: lib.date.inc.php,v 1.1.1.1.10.3 2017-12-05 10:04:44 sebastien Exp $
  * @tests T00000
  **/                               

if(!defined('INCLUDE_LIB_DATE')){
define('INCLUDE_LIB_DATE',1);

 /**                                  
  * isTodayDMY : Retour vrai si on est aujourd'hui
  *                                   
  *                                   
  * @param     int $jour=-1 
  * @param     int $mois=-1 
  * @param     int $annee=-1 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function isTodayDMY($jour=-1,$mois=-1,$annee=-1)
{
 
 	return (date("M-d-Y") == date("M-d-Y", mktime (0,0,0,$mois,$jour,$annee)));
	
}

 /**                                  
  * afficheDate : Affichage de la date au format dd-mm-yyyy
  *                                   
  *                                   
  * @param unknown $date 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function afficheDate($date)
{
	if ( preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $regs ) )
	return sprintf("%02d",$regs[3])."-".sprintf("%02d",$regs[2])."-".$regs[1];
	else { return $date;}
}

 /**                                  
  * afficheDateCourt : Affichage de la date au format dd-mm-yy
  *                                   
  *                                   
  * @param unknown $date 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function afficheDateCourt($date)
{
	if ( preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $regs ) )
	return $regs[3]."-".$regs[2]."-".substr($regs[1],2,2);
	else { return $date;}
}

 /**                                  
  * saisieDate : Affichage de la date au format yyyy-mm-dd 
  * ...                              
  *                                   
  *                                   
  * @param unknown $date 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function saisieDate($date){
	if ( !preg_match( "/([0-9]{1,2})-([0-9]{1,2})-([0-9]{2,4})/", $date, $regs ) )
	if ( !preg_match( "/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2,4})/", $date, $regs ) )
	if ( !preg_match( "/([0-9]{1,2}) ([0-9]{1,2}) ([0-9]{2,4})/", $date, $regs ) )
	return false;
	
	$regs[2]=sprintf("%02d",$regs[2]);
	$regs[1]=sprintf("%02d",$regs[1]);
	$regs[3]=(strlen($regs[3])==2)?"20".$regs[3]:$regs[3];
	return $regs[3]."-".$regs[2]."-".$regs[1];
}

 /**                                  
  * saisieDateTime : Affichage de la date au format yyyy-mm-dd HH:ii:ss
  *                                   
  *                                   
  * @param unknown $date 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function saisieDateTime($date){
	$time=substr($date,-9);
	$date=substr($date,0,10);
	return saisieDate($date).$time;
}

 /**                                  
  * afficheDateTime  : Affichage de la date au format dd-mm-yyyy HH:ii:ss
  * ...                              
  *                                   
  *                                   
  * @param unknown $dateTime 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function afficheDateTime ($dateTime){
	if(strlen($dateTime) == strlen("20061212203410")){
		// la date est un timestamp
		$dateTime = 	 substr($dateTime,0,4)."-"
				.substr($dateTime,4,2)."-"
				.substr($dateTime,6,2)." "
				.substr($dateTime,8,2).":"
				.substr($dateTime,10,2).":"
				.substr($dateTime,12,2)."";
	}
	
	$date=substr($dateTime,0,10);
	$time=substr($dateTime,-9);
	return afficheDate($date).$time;
	
}

 /**                                  
  * afficheTime  : Affichage de l'heure
  * ...                              
  *                                   
  *                                   
  * @param unknown $dateTime 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function afficheTime ($dateTime){
	$date=substr($dateTime,0,10);
	$time=substr($dateTime,-9);
	return $time;
}

 /**                                  
  * numToJour : Affichage du nom du jour
  *                                   
  *                                   
  * @param unknown $nbr 
  *                                   
  * @return string                          
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function numToJour($nbr,$options = "all")
{
	if ($nbr==1) $res=_s("Lundi",$options);
	if ($nbr==2) $res=_s("Mardi",$options);
	if ($nbr==3) $res=_s("Mercredi",$options);
	if ($nbr==4) $res=_s("Jeudi",$options);
	if ($nbr==5) $res=_s("Vendredi",$options);
	if ($nbr==6) $res=_s("Samedi",$options);
	if ($nbr==0) $res=_s("Dimanche",$options);
	return $res;
}


 /**                                  
  * numToJourAbrege : Affichage du nom du jour abrégé
  * ...                              
  *                                   
  *                                   
  * @param unknown $nbr 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function numToJourAbrege($nbr,$options = "all")
{
	if ($nbr==1) $res=_s("Lun",$options);
	if ($nbr==2) $res=_s("Mar",$options);
	if ($nbr==3) $res=_s("Mer",$options);
	if ($nbr==4) $res=_s("Jeu",$options);
	if ($nbr==5) $res=_s("Ven",$options);
	if ($nbr==6) $res=_s("Sam",$options);
	if ($nbr==0) $res=_s("Dim",$options);
	return $res;
}

          
 /**                                  
  * numToMoisSsClass : Affichage des noms des mois                          
  *                                   
  *                                   
  * @param unknown $nbr 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function numToMoisSsClass($nbr,$options = "all")
{
	if ($nbr==1) $res=_s("Janvier",$options);
	if ($nbr==2) $res=_s("Février",$options);
	if ($nbr==3) $res=_s("Mars",$options);
	if ($nbr==4) $res=_s("Avril",$options);
	if ($nbr==5) $res=_s("Mai",$options);
	if ($nbr==6) $res=_s("Juin",$options);
	if ($nbr==7) $res=_s("Juillet",$options);
	if ($nbr==8) $res=_s("Août",$options);
	if ($nbr==9) $res=_s("Septembre",$options);
	if ($nbr==10) $res=_s("Octobre",$options);
	if ($nbr==11) $res=_s("Novembre",$options);
	if ($nbr==12) $res=_s("Décembre",$options);
	return $res;
}

 /**                                  
  * numToMoisAbrege : Affichage des noms abrégés des mois 
  * ...                              
  *                                   
  *                                   
  * @param unknown $nbr 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function numToMoisAbrege($nbr,$options = "all")
{
	if ($nbr==1) $res=_s("Jan",$options);
	if ($nbr==2) $res=_s("Fev",$options);
	if ($nbr==3) $res=_s("Mars",$options);
	if ($nbr==4) $res=_s("Avr",$options);
	if ($nbr==5) $res=_s("Mai",$options);
	if ($nbr==6) $res=_s("Juin",$options);
	if ($nbr==7) $res=_s("Juil",$options);
	if ($nbr==8) $res=_s("Août",$options);
	if ($nbr==9) $res=_s("Sept",$options);
	if ($nbr==10) $res=_s("Oct",$options);
	if ($nbr==11) $res=_s("Nov",$options);
	if ($nbr==12) $res=_s("Déc",$options);
	return $res;
}
 
 /**                                  
  * numToMois : Affichage des noms des mois                             
  *                                   
  *                                   
  * @param unknown $nbr 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function numToMois($nbr,$options = "all")
{
  return numToMoisSsClass($nbr,$options);
}


 /**                                  
  * listeJour : Menu pour les jour
  *                                   
  *                                   
  * @param unknown $nom 
  * @param  string $selected="" 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function listeJour($nom,$selected=""){
  echo "<SELECT  name=\"$nom\">\n";
  if ($selected=="") $selected=date("d");
  for ($i=1;$i<32;$i++)
   {
   $i=sprintf("%02d",$i);
   if ($i==$selected) $BONUS="selected"; else $BONUS="";
   echo "<OPTION $BONUS value=$i>".$i."</OPTION>\n";
   }

  echo "</SELECT>\n";
}


 /**                                  
  * menuJour : Menu pour les jour (lundi, ...)
  *                                   
  *                                   
  * @param unknown $nom 
  * @param  string $selected="" 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function menuJour($nom,$selected=""){
  echo "<SELECT  name=\"$nom\">\n";
  if ($selected=="") $selected=0;
  for ($i=0;$i<7;$i++)
   {
   if ($i==$selected) $BONUS="selected"; else $BONUS="";
   echo "<OPTION $BONUS value=$i>".numToJour($i)."</OPTION>\n";
   }

  echo "</SELECT>\n";
}


 /**                                  
  * listeMois : Menu pour les mois 
  *                                   
  *                                   
  * @param unknown $nom 
  * @param  string $selected="" 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function listeMois($nom,$selected=""){
  echo "<SELECT  name=\"$nom\">\n";
  if ($selected=="") $selected=date("m");
  for ($i=1;$i<13;$i++)
   {
   $i=sprintf("%02d",$i);
   if ($i==$selected) $BONUS="selected"; else $BONUS="";
   echo "<OPTION $BONUS value=$i>".numToMois($i)."</OPTION>\n";
   }

  echo "</SELECT>\n";
}

 /**                                  
  * listeAnnee : Menu pour les années (à partir de 2000)
  *                                   
  *                                   
  * @param unknown $nom 
  * @param  string $selected="" 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function listeAnnee($nom,$selected=""){
  echo "<SELECT  name=\"$nom\">\n";
  if ($selected=="") $selected=date("Y");
  for ($i=2000;$i<2010;$i++)
   {
   $i=sprintf("%04d",$i);
   if ($i==$selected) $BONUS="selected"; else $BONUS="";
   echo "<OPTION $BONUS value=$i>".$i."</OPTION>\n";
   }

  echo "</SELECT>\n";
}




 /**                                  
  * listeAnneeF : Menu pour les années (à partir de cette année)                              
  *                                   
  *                                   
  * @param unknown $nom 
  * @param  string $selected="" 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function listeAnneeF($nom,$selected=""){
  echo "<SELECT  name=\"$nom\">\n";
  if ($selected=="") $selected=date("Y");
  $i=date("Y");
  for (;$i<2010;$i++)
   {
   $i=sprintf("%04d",$i);
   if ($i==$selected) $BONUS="selected"; else $BONUS="";
   echo "<OPTION $BONUS value=$i>".$i."</OPTION>\n";
   }

  echo "</SELECT>\n";
}

 /**                                  
  * jourSemaine : retourne le jour de la semaine
  *                                   
  *                                   
  * @param unknown $j 
  * @param unknown $m 
  * @param unknown $y 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function jourSemaine($j,$m,$y)
{
$tt=date("w",mktime(0,0,0,$m,$j,$y));
//$tt=(($tt+6)%7);
return $tt;
}

 /**                                  
  * dernierJour : Retour le nombre de jour du mois
  *                                   
  *                                   
  * @param unknown $j 
  * @param unknown $m 
  * @param unknown $y 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function dernierJour($j,$m,$y)
{
$tt=date("t",mktime(0,0,0,$m,$j,$y));
return $tt;


}

 /**                                  
  * numSemaineOnce : Retourne le numéro de semaine
  *                                   
  *                                   
  * @param unknown $date 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function numSemaineOnce($date){
	$res = explode("-",$date);
	return numSemaine($res[0],$res[1],$res[2]);


}

 /**                                  
  * numSemaineOnceTrace : Retourne le numéro de semaine pour les traces
  *                                   
  *                                   
  * @param unknown $date 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function numSemaineOnceTrace($date){
	$res = explode("-",$date);
	return _s("Semaine")." ".numSemaine($res[0],$res[1],$res[2]);
}

 /**                                  
  * premierJourSemaineFromDate : Retourne le premier jour de la semaine
  *                                   
  *                                   
  * @param unknown $date 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function premierJourSemaineFromDate($date){

	list($d,$m,$y)=explode('-',$date);
	$dateUnix = mktime(0,0,0,$m,$d,$y);

	if($dateUnix>0){
		while($i<8 && date("w",$dateUnix) != 1){
			$i++;
			$dateUnix -= 24*60*60; 
		
		}
		
		return date("d-m-Y",$dateUnix);
	}
	else return false;
}
 /**                                  
  * premierDernierJourSemaine : retourne le dernier jour de la semaine
  *                                   
  *                                   
  * @param unknown $semaine 
  * @param unknown $annee 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function premierDernierJourSemaine($semaine,$annee) {
	$i=1;
	while(numSemaine($i,1,$annee)!=1){
		$i++;
	} // while
	$dateUnixPremierJour=mktime(0,0,0,1,$i,$annee);
	$numPremierJour=date("w",$dateUnixPremierJour);
	$dateUnixPremierLundi=$dateUnixPremierJour-(($numPremierJour+6)%7)*24*60*60;
	
	$dateUnixLundiSemaine=$dateUnixPremierLundi+7*24*60*60*($semaine-1);
	$dateUnixDimancheSemaine=$dateUnixLundiSemaine+6*24*60*60;
	
	if (numSemaineOnce(date("d-m-Y",$dateUnixLundiSemaine))!=$semaine) return false;
	else return array(date("d-m-Y",$dateUnixLundiSemaine),date("d-m-Y",$dateUnixDimancheSemaine));
}

 /**                                  
  * numSemaine : Retourne le numéro de semaine                            
  *                                   
  *                                   
  * @param unknown $d 
  * @param unknown $m 
  * @param unknown $y 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function numSemaine($d,$m,$y)
{
		$week=strftime("%W", mktime(0, 0, 0, $m, $d, $y));
		$dow0101=getdate(mktime(0, 0, 0, 1, 1, $y));
		$dow3112=getdate(mktime(0, 0, 0, 12, 31, $y));
		if ($dow0101["wday"]>1 && 
		    $dow0101["wday"]<5)
		  $week++;
		elseif ($week==0)
		  $week=53;
		  
		if ($week==53 && ($dow3112["wday"]>0 && $dow3112["wday"]<4) ) {
		    $week=1;
		}
		return(substr("00" . $week, -2));
}


 /**                                  
  * dateExpire : Retourne si la date est expiré                             
  *                                   
  *                                   
  * @param unknown $jour 
  * @param unknown $mois 
  * @param unknown $annee 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function dateExpire($jour,$mois,$annee){
 // Regarde si la date donnée en argument est anterieur a 3 ans
 if (checkdate($mois,$jour,$annee))
  {
   $atester=mktime(0,0,0,$mois,$jour,$annee);
   $limite=mktime(0,0,0,date("m"),  date("d"),   date("Y")-3);
   if ($limite<=$atester)
    return false;
   else return true;
  }
 else return 0;
}


 /**                         
  * Date  :                      
  *                          
  *                          
  * @param unknown $jour 
  * @param unknown $mois 
  * @param unknown $annee 
  *                                   
  * @version                           
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
class Date {

 /**                                  
  *   
  *                               
  * @var unknown $now 
  * @access private                             
  */                               
var $now;
 /**                                  
  *   
  *                               
  * @var unknown $jour 
  * @access private                             
  */                               
var $jour;
 /**                                  
  *   
  *                               
  * @var unknown $moisString 
  * @access private                             
  */                               
var $moisString;
 /**                                  
  *   
  *                               
  * @var unknown $mois 
  * @access private                             
  */                               
var $mois;
 /**                                  
  *   
  *                               
  * @var unknown $annee 
  * @access private                             
  */                               
var $annee;

 /**                                  
  * Date : Création d'une variable de type date                            
  *                                   
  *                                   
  * @param     int $mois=0 
  * @param     int $annee=0 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see Date ()                    
  *                             
  **/                                 
function __construct($mois=0,$annee=0){

        $nowDate=Getdate(time());
            if ($mois==0||$annee==0||$mois==""||$annee=="")
                {
                $mois=$nowDate["mon"];
                $annee=$nowDate["year"];
                }

        if($mois == $nowDate["mon"] && $annee==$nowDate["year"]) $jour=$nowDate["mday"];
		else $jour=1;

        $now=mktime(0,0,0,$mois,$jour,$annee);
        $this->now=$now;
        $this->jour=$jour;
        $this->moisString = array(_s("Janvier"),_s("Février"),_s("Mars"),_s("Avril"),_s("Mai"),_s("Juin"),_s("Juillet"),_s("Août"),_s("Septembre"),_s("Octobre"),_s("Novembre"),_s("Décembre"));
        $this->annee=$annee;
        $this->mois=$this->format($mois);
}

 /**                                  
  * format : Retourne un chiffre de format 00
  *                                   
  *                                   
  * @param unknown $sStr 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see Date ()                    
  *                             
  **/                                 
function format($sStr){
        if ($sStr<10) $sStr="0".$sStr;
        return $sStr;
        }


 /**                                  
  * present : Donne la date actuelle
  *                                   
  *                                   
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see Date ()                    
  *                             
  **/                                 
function present(){
        $date=Getdate($this->now);
        $resu["mois"]=$this->format($date["mon"]);
        $resu["annee"]=$date["year"];
        $resu["jour"]=$date["mday"];
        return $resu;
}


 /**                                  
  * suivant : Donne la date suivante                           
  *                                   
  *                                   
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see Date ()                    
  *                             
  **/                                 
function suivant(){
                 /*retourn un tableau [mois,annee]*/
        if ($this->mois<9)
            {
            $resu["mois"]="0".$this->mois+1;
            $resu["annee"]=$this->annee;
            }
        else
            if ($this->mois<12)
                {
                 $resu["mois"]=$this->mois+1;
                 $resu["annee"]=$this->annee;
                 }
            else
                {
                 $resu["mois"]=1;
                 $resu["annee"]=$this->annee+1;
                 }
        return $resu;
}

 /**                                  
  * precedent : Donne la date précedente
  *                                   
  *                                   
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see Date ()                    
  *                             
  **/                                 
function precedent(){
                 /*retourn un tableau [mois,annee]*/
        $date=$this->now-($this->jour)*24*3600-24*3600;          // on soustrait environ 25 jours
        $date=Getdate($date);
        $resu["mois"]=$this->format($date["mon"]);
        $resu["annee"]=$date["year"];
        return $resu;
}


 /**                                  
  * numToMois : Donne le nom du mois
  *                                   
  *                                   
  * @param unknown $num 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see Date ()                    
  *                             
  **/                                 
function numToMois($num)
{
		return $this->moisString[$num-1];
}

 /**                                  
  * dayMax : Nombre de jour max dans le mois
  *                                   
  *                                   
  *                                   
  * @return int nbr de jour                          
  *                                   
  * @since 17 Fev 2004       
  * @access public                    
  * @see Date ()                    
  *                             
  **/                                 
function dayMax(){
        return date("t",mktime(0,0,0,$this->mois,1,$this->annee));
}

 /**                                  
  * mois : Affectation d'un mois                             
  *                                   
  *                                   
  * @param unknown $moisSeek 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see Date ()                    
  *                             
  **/                                 
function mois($moisSeek){
        
        $date=Getdate(mktime(0,0,0,$this->mois+$moisSeek,$this->jour,$this->annee));
        $resu["mois"]=$this->format($date["mon"]);
        $resu["annee"]=$date["year"];
        $resu["jour"]=$this->format($date["mday"]);
        return $resu;
}

 /**                                  
  * annee : Affectation d'une année
  *                                   
  *                                   
  * @param unknown $anneeSeek 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see Date ()                    
  *                             
  **/                                 
function annee($anneeSeek){
        $date=Getdate(mktime(0,0,0,$this->mois,$this->jour,$this->annee+$anneeSeek));
        $resu["mois"]=$this->format($date["mon"]);
        $resu["annee"]=$date["year"];
        $resu["jour"]=$this->format($date["mday"]);
        return $resu;
}
}//class


 /**                         
  * DateJour : Classe Date+jour
  *                          
  *                          
  * @param unknown $anneeSeek 
  *                                   
  * @version                           
  * @since 130 Jan 2004       
  * @access public                    
  *                             
  **/                                 
class DateJour{

 /**                                  
  *   
  *                               
  * @var unknown $now 
  * @access private                             
  */                               
var $now;
 /**                                  
  *   
  *                               
  * @var unknown $jour 
  * @access private                             
  */                               
var $jour;
 /**                                  
  *   
  *                               
  * @var unknown $mois 
  * @access private                             
  */                               
var $mois;
 /**                                  
  *   
  *                               
  * @var unknown $annee 
  * @access private                             
  */                               
var $annee;
 /**                                  
  *   
  *                               
  * @var unknown $present 
  * @access private                             
  */                               
var $present;

 /**                                  
  * DateJour : Création d'une variable DateJour
  *                                   
  *                                   
  * @param  string $jour="" 
  * @param  string $mois="" 
  * @param  string $annee="" 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see DateJour()                    
  *                             
  **/                                 
function __construct($jour="",$mois="",$annee=""){
        $nowDate=Getdate(time());
        if ($jour==""||$mois==""||$annee=="")
                {
                $jour=$nowDate["mday"];
                $mois=$nowDate["mon"];
                $annee=$nowDate["year"];
                }

        $now=mktime(0,0,0,$mois,$jour,$annee);
        $this->now=$now;
        $this->annee=$annee;
        $this->mois=$this->format($mois);
        $this->jour=$jour;
		$this->present = $this->present();
}


 /**
  * isGreaterOrEqualThan : Renvoie true si la date est plus grande que le paramêtre
  *
  *
  * @param DateJour $dateJour
  *
  * @return bool
  *
  * @since 30 Jan 2004
  * @access public
  * @see DateJour()
  *
  **/
function isGreaterOrEqualThan($dateJour) {
	if ($this->annee>$dateJour->annee
        || ($this->annee==$dateJour->annee && $this->mois>$dateJour->mois)
        || ($this->annee==$dateJour->annee && $this->mois==$dateJour->mois && $this->jour>=$dateJour->jour)
        ) return true;
    else return false;
}

 /**
  * isLowerOrEqualThan : Renvoie true si la date est plus petite que le paramêtre
  *
  *
  * @param DateJour $dateJour
  *
  * @return bool
  *
  * @since 30 Jan 2004
  * @access public
  * @see DateJour()
  *
  **/
function isLowerOrEqualThan($dateJour) {
	if ($this->annee<$dateJour->annee
        || ($this->annee==$dateJour->annee && $this->mois<$dateJour->mois)
        || ($this->annee==$dateJour->annee && $this->mois==$dateJour->mois && $this->jour<=$dateJour->jour)
        ) return true;
    else return false;
}
 /**                                  
  * jourSeek : Affectation du jour
  *                                   
  *                                   
  * @param unknown $sStr 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see DateJour()                    
  *                             
  **/ 
function jourSeek($jour){

        $now=mktime(0,0,0,$this->mois,$this->jour+$jour,$this->annee);
		$nowDate=Getdate($now);		
		$jour=$nowDate["mday"];
		$mois=$nowDate["mon"];
		$annee=$nowDate["year"];

        $this->now=$now;
        $this->annee=$annee;
        $this->mois=$this->format($mois);
        $this->jour=$jour;
		$this->present = $this->present();

}

 /**
  * moisSeek : Décale la date de n mois
  *
  *
  * @param unknown $mois
  *
  * @return
  *
  * @since 07 Fev 2004
  * @access public
  * @see DateJour()
  *
  **/
function moisSeek($mois){

        $now=mktime(0,0,0,$this->mois+$mois,$this->jour,$this->annee);
		$nowDate=Getdate($now);
		$jour=$nowDate["mday"];
		$mois=$nowDate["mon"];
		$annee=$nowDate["year"];

        $this->now=$now;
        $this->annee=$annee;
        $this->mois=$this->format($mois);
        $this->jour=$jour;
		$this->present = $this->present();

}

 /**                                  
  * numSemaine : Retourne le numéro de semaine
  *                                   
  *                                   
  * @param unknown $sStr 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see DateJour()                    
  *                             
  **/ 
function numSemaine(){
	return numSemaine($this->jour,$this->mois,$this->annee);
}

 /**                                  
  * format : Retourne un chiffre de type 00
  *                                   
  *                                   
  * @param unknown $sStr 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see DateJour()                    
  *                             
  **/                                 
function format($sStr){
        if ($sStr<10) $sStr="0".$sStr;
        return $sStr;
        }

/**                                  
  * present : Retourne jour en cours
  *                                   
  *                                   
  * @param unknown $sStr 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see DateJour()                    
  *                             
  **/ 
function present($type="classic"){


        $date=Getdate($this->now);
        $resu["mois"]=$this->format($date["mon"]);
        $resu["moisStr"]=numToMois($date["mon"]);
        $resu["moisStrAbrege"]=numToMoisAbrege($date["mon"]);
        $resu["annee"]=$date["year"];
        $resu["jour"]=$this->format($date["mday"]);
        $resu["jourStrAbrege"]=numToJourAbrege($date["wday"]);
        $resu["jourDeLaSemaine"]=$date["wday"];
        $resu["jourStr"]=numToJour($date["wday"]);
        $resu["date"]=$resu["jour"]."-".$resu["mois"]."-".$resu["annee"];
		if($type=="full"){
			$aujourdhui = Getdate(time());
			$resu["estAujourdhui"] = ($date['yday']==$aujourdhui['yday'] && $date['year']==$aujourdhui['year']);
			$resu["estFerie"] = dateEstFerie($resu["date"]);
			$resu["estOuvert"] = dateJourEstOuvert($resu["jourDeLaSemaine"]);
		}
		
        return $resu;
}

 /**                                  
  * jour : Retourne le jour
  *                                   
  *                                   
  * @param unknown $jourSeek 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see DateJour()                    
  *                             
  **/                                 
function jour($jourSeek,$type="classic"){
        
        $date=Getdate(mktime(0,0,0,$this->mois,$this->jour+$jourSeek,$this->annee));
        $resu["mois"]=$this->format($date["mon"]);
        $resu["moisStr"]=numToMois($date["mon"]);
        $resu["moisStrAbrege"]=numToMoisAbrege($date["mon"]);
        $resu["annee"]=$date["year"];
        $resu["jour"]=$this->format($date["mday"]);
        $resu["jourStrAbrege"]=numToJourAbrege($date["wday"]);
        $resu["jourDeLaSemaine"]=$date["wday"];
        $resu["jourStr"]=numToJour($date["wday"]);
        $resu["date"]=$resu["jour"]."-".$resu["mois"]."-".$resu["annee"];
       	if($type=="full"){
			$aujourdhui = Getdate(time());
			$resu["estAujourdhui"] = ($date['yday']==$aujourdhui['yday'] && $date['year']==$aujourdhui['year']);
			$resu["estFerie"] = dateEstFerie($resu["date"]);
			$resu["estOuvert"] = dateJourEstOuvert($resu["jourDeLaSemaine"]);
		}

        return $resu;
}

 /**                                  
  * mois : Retourne le mois
  * ...                              
  *                                   
  *                                   
  * @param unknown $moisSeek 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see DateJour()                    
  *                             
  **/                                 
function mois($moisSeek){
        
        $date=Getdate(mktime(0,0,0,$this->mois+$moisSeek,$this->jour,$this->annee));
        $resu["mois"]=$this->format($date["mon"]);
        $resu["moisStr"]=numToMois($date["mon"]);
        $resu["moisStrAbrege"]=numToMoisAbrege($date["mon"]);
        $resu["annee"]=$date["year"];
        $resu["jour"]=$this->format($date["mday"]);
        $resu["jourStrAbrege"]=numToJourAbrege($date["wday"]);
        $resu["jourStr"]=numToJour($date["wday"]);
        $resu["date"]=$resu["jour"]."-".$resu["mois"]."-".$resu["annee"];
        return $resu;
}

 /**                                  
  * annee : Retourne l'année                             
  *                                   
  *                                   
  * @param unknown $anneeSeek 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  * @see DateJour()                    
  *                             
  **/                                 
function annee($anneeSeek){
        $date=Getdate(mktime(0,0,0,$this->mois,$this->jour,$this->annee+$anneeSeek));
        $resu["mois"]=$this->format($date["mon"]);
        $resu["moisStr"]=numToMois($date["mon"]);
        $resu["moisStrAbrege"]=numToMoisAbrege($date["mon"]);
        $resu["annee"]=$date["year"];
        $resu["jour"]=$this->format($date["mday"]);
        $resu["jourStrAbrege"]=numToJourAbrege($date["wday"]);
        $resu["jourStr"]=numToJour($date["wday"]);
        $resu["date"]=$resu["jour"]."-".$resu["mois"]."-".$resu["annee"];
        return $resu;
}

 /**                                  
  * dayMax : Nombre de jour max dans le mois
  *                                   
  *                                   
  *                                   
  * @return int nbr de jour                          
  *                                   
  * @since 17 Fev 2004       
  * @access public                    
  * @see DateJour ()                    
  *                             
  **/                                 
function dayMax(){
        return date("t",mktime(0,0,0,$this->mois,1,$this->annee));
}


}//class


 /**                                  
  * dateAnterieure : Teste si date1<date2
  *                                   
  *                                   
  * @param unknown $date1 
  * @param unknown $date2 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function dateAnterieure($date1,$date2) {

	$jour1=substr($date1,8,2);
	$mois1=substr($date1,5,2);
	$anne1=substr($date1,0,4);
	$jour2=substr($date2,8,2);
	$mois2=substr($date2,5,2);
	$anne2=substr($date2,0,4);
	if ($anne1<$anne2) return 1;
	else if ($anne1==$anne2) {
		if ($mois1<$mois2) return 1;
		else if ($mois1==$mois2) {
			if ($jour1<=$jour2) return 1;
			else return 0;
		}
		else return 0;
	}
	else return 0;

}

 /**                                  
  * differenceHoraire : Calcul la différence horaire
  *                                   
  *                                   
  * @param unknown $heure1 
  * @param unknown $heure2 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function differenceHoraire($heure1,$heure2){

	$h1['m']=substr($heure1,2,2);
	$h1['h']=substr($heure1,0,2);
	$h2['m']=substr($heure2,2,2);
	$h2['h']=substr($heure2,0,2);
	
	$diff=($h2['h']-$h1['h'])*60;
	$diff+=($h2['m']-$h1['m']);
	return $diff;
				
}

 /**                                  
  * differenceDate : Calcul la différence entre 2 dates
  *                                   
  *                                   
  * @param unknown $date1 
  * @param unknown $date2 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function differenceDate($date1,$date2){
	list($y1,$m1,$d1)=explode('-',$date1);
	list($y2,$m2,$d2)=explode('-',$date2);
	
	$mktime1=mktime(0,0,0,$m1,$d1,$y1);
	$mktime2=mktime(0,0,0,$m2,$d2,$y2);
	
	$nbrJour=($mktime2-$mktime1)/60/60/24;
	return $nbrJour;
				
}

 /**                                  
  * formInputHeure : Input pour un format heure
  *                                   
  *                                   
  * @param unknown $nom 
  * @param  string $selected="" 
  * @param  string $type="" 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function formInputHeure($nom,$selected="",$type=""){
	if($selected!=""){
		$h = substr($selected,0,2);
		$m = substr($selected,2,2);
	}
	if($type=="horaire"){$filtreH=",Array(Array('lt',24),Array('ge',0))";$filtreM=",Array(Array('lt',60),Array('ge',0))";}

	return "<nobr style=\"border:2px inset #FFFFFF;background-color:white\">"
		."<input name=\"".$nom."[HM]\" type=hidden value=\"".$selected."\">"
		."<input name=\"".$nom."[H]\" type=text value=\"".$h."\" size=\"2\" dataType=\"int\" maxlength=\"2\" onFocus=\"this.select()\" onKeyDown=\"myHTrackKeyDown(this);\" onKeyUp=\"myHTrackKeyUp(this,'".$nom."','".$type."');\"  onBlur=\"text_validate(this".$filtreH.");\"  STYLE=\"text-align:center;border:0px;\">"
		."H"
		."<input name=\"".$nom."[M]\" type=text value=\"".$m."\" dataType=\"int\" size=\"2\" onFocus=\"this.select()\"  onBlur=\"text_validate(this".$filtreM.");myHTrackRefresh(this,'".$nom."');\" maxlength=\"2\" STYLE=\"text-align:center;border:0px;\">"
		."</nobr>";
}

 /**                                  
  * afficheHeure : Affichage de l'heure au format HH:ii
  *                                   
  *                                   
  * @param unknown $heure 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function afficheHeure($heure) {
	if ($heure!="") 
		return sprintf("%02d",substr($heure,0,count($heure)-3)).":".sprintf("%02d",substr($heure,-2));
	else return "";
}

 /**                                  
  * afficheMinuteEnHeure : retourne le nomvre de minute en heure(HHii)
  *                                   
  *                                   
  * @param unknown $minute 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function afficheMinuteEnHeure($minute) {

	if($minute<0) $moins=true;
	else $moins=false;
	$minute=abs($minute);

	$h=floor($minute/60);
	$m=$minute%60;
	return ($moins?"-":"").afficheHeure(sprintf("%02d%02d",$h,$m));


}


 /**                                  
  * afficheHeureEnMinute : retourne le nb de minutes
  *                                   
  *                                   
  * @param unknown $minute 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function afficheHeureEnMinute($heure) {

	$nbH = substr($heure,0,2);
	if(strpos($heure,":")!==false) {
		$nbM = 	substr($heure,3,2);
	} else {
		$nbM = 	substr($heure,2,2);
	}

	return $nbM+($nbH*60);
}


 /**                                  
  * dateDiff : retourne le difference entre deux dates
  *                                   
  *                                   
  * @param unknown $dateDe 
  * @param unknown $dateA 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/   
function dateDiff($dateDe , $dateA){ 
	list($jour , $mois , $an) = explode("-",$dateDe);
	list($jour2 , $mois2 , $an2) = explode("-",$dateA);
	
	$timestamp = mktime(1, 1, 1, (int)$mois, (int)$jour, (int)$an); 
	$timestamp2 = mktime(1, 1, 1, (int)$mois2, (int)$jour2, (int)$an2); 

	$diff = floor(($timestamp - $timestamp2) / (3600 * 24)); 
	
	return $diff; 
}

}
?>
