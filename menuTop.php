<?
	global $conf;
?>
<font style="font-weight:bold;font-size:13px;"><?=$laboNom;?></font><br /><span style="font-size:9px;"><?=$laboAddresse." ".$laboCodePostal." ".$laboVille.(($laboTelephone!="")?("&nbsp;&nbsp;<img src=\"images/tel.gif\">&nbsp;".$laboTelephone.""):(""));?></span>
