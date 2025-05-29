<?                                  
 /**                                
  * commentaire                     
  *                                 
  *        		                 
  * @package KaliLab                
  * @module KaliLab                
  * @author Netika <info@netika.net>
  * @cvs $Id: courier.php,v 1.1.1.1 2010-08-26 12:13:53 daniel Exp $
  * @tests T00000
  **/                               
?><?php
for($i=0;$i<=255;$i++)
	$fpdf_charwidths['courier'][chr($i)]=600;
$fpdf_charwidths['courierB']=$fpdf_charwidths['courier'];
$fpdf_charwidths['courierI']=$fpdf_charwidths['courier'];
$fpdf_charwidths['courierBI']=$fpdf_charwidths['courier'];
?>
