 /**
  * commentaire
  *
  *
  * @package KaliLab
  * @module KaliLab
  * @author Netika <info@netika.net>
  **/

var remote;

var agt=navigator.userAgent.toLowerCase();

var is_major = parseInt(navigator.appVersion);
var is_minor = parseFloat(navigator.appVersion);

var is_nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
            && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
            && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1));
var is_nav2 = (is_nav && (is_major == 2));
var is_nav3 = (is_nav && (is_major == 3));
var is_nav4 = (is_nav && (is_major == 4));
var is_nav4up = (is_nav && (is_major >= 4));
var is_navonly      = (is_nav && ((agt.indexOf(";nav") != -1) ||
                      (agt.indexOf("; nav") != -1)) );
var is_nav6 = (is_nav && (is_major == 5));
var is_nav6up = (is_nav && (is_major >= 5));
var is_gecko = (agt.indexOf('gecko') != -1);
var is_firefox = (agt.indexOf('firefox') != -1);


var is_ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
var is_ie3    = (is_ie && (is_major < 4));
var is_ie4    = (is_ie && (is_major == 4) && (agt.indexOf("msie 4")!=-1) );
var is_ie4up  = (is_ie && (is_major >= 4));
var is_ie5    = (is_ie && (is_major == 4) && (agt.indexOf("msie 5.0")!=-1) );
var is_ie5_5  = (is_ie && (is_major == 4) && (agt.indexOf("msie 5.5") !=-1));
var is_ie5up  = (is_ie && !is_ie3 && !is_ie4);
var is_ie5_5up =(is_ie && !is_ie3 && !is_ie4 && !is_ie5);
var is_ie6    = (is_ie && (is_major == 4) && (agt.indexOf("msie 6.")!=-1) );
var is_ie6up  = (is_ie && !is_ie3 && !is_ie4 && !is_ie5 && !is_ie5_5);


function makeRemote(nom,page,width,height){
		w = screen.availWidth - 10;
		h = screen.availHeight - 35;

		if( ((width == 1024) && (height==768)) || ((width == 800) && (height==600) ) || ((width == 700) && (height==500) )){
			width = w ;
			height = h;
		}

		if (width > w) width=w;
		if (height > h) height=h;

        remote = window.open(page,nom,"scrollbars=yes,resizable=yes,width="+width+",height="+height);

        if (remote.opener == null) remote.opener = window;

		try{
			remote.moveTo(0,0)
			remote.focus();
		}catch(e){
			//alert("La fenêtre est déjà ouverte.");
		}

		return remote;
}

 /**
  * makeRemoteKaliLab :
  * ...
  *
  *
  * @param unknown nom
  * @param unknown page
  * @param unknown width
  * @param unknown height
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function makeRemoteKaliLab(nom,page,width,height)
        {
        remote = window.open("",nom,"scrollbars=yes,width="+width+",height="+height+",left=0,top=0");
        remote.location.href = page;
        if (remote.opener == null)
                        remote.opener = window;
           return remote;
        }

 /**
  * closeAllKalilab :
  * ...
  *
  *
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function closeAllKalilab(){
	for(var i=1;i<4;i++){
		iVar=""+i;
		if( "kalisil"+iVar != top.name){
			testwindow = window.open("", "kalisil"+iVar,"toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=1,height=1,top=9999,left=9999");
	    	if (testwindow) { testwindow.close();}
		}
	}

	top.document.location = "kalilab.php?logout=1"

}


 /**
  * reloadAllKalilab :
  * ...
  *
  *
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function reloadAllKalilab(){
	for(var i=1;i<4;i++){
		iVar=""+i;
		testwindow = window.open("", "kalisil"+iVar,"toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=1,height=1,top=9999,left=9999");
    	if (testwindow && testwindow.KALILABROOT) {testwindow.reloadKalilab()}
		else testwindow.close();
	}
}
 /**
  * restartAllKalilab :
  * ...
  *
  *
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function restartAllKalilab(){
	for(var i=1;i<4;i++){
		iVar=""+i;
		testwindow = window.open("", "kalisil"+iVar,"toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=1,height=1,top=9999,left=9999");
    	if (testwindow && testwindow.KALILABROOT) {testwindow.document.location.reload();}
		else testwindow.close();
	}
}

 /**
  * _makeNewKalilab :
  * ...
  *
  *
  *
  * @return
  *
  * @since 03 Aug 2004
  * @access public
  *
  **/
function _makeNewKalilab(windowTmp){
	windowTmp.document.location.replace(confBaseUrl+'kalilab.php');
	windowTmp.opener = top;

	windowTmp.moveTo(0,0);
	windowTmp.focus();
}

 /**
  * makeNewKalilab :
  * ...
  *
  *
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function makeNewKalilab(){
	var termine=0;

	for(var i=1;termine==0 && i<4;i++){
		iVar=""+i;
		if(top.name == "kalisil"+iVar) continue;
		testwindow = window.open("", "kalisil"+iVar,"toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=1,height=1,top=9999,left=9999");
    	if (testwindow.KALILABROOT) {

		}
		else{
			_makeNewKalilab(testwindow);
		   	termine=1;
		}
	}
	if(termine==0) alert("Vous avez le nombre maximal de fenêtres ouvertes.");
}

 /**
  * makeRemoteDoc :
  * ...
  *
  *
  * @param unknown hostname
  * @param unknown texte
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function makeRemoteDoc(hostname,texte)
         {
        remote = window.open("file:\\\\"+hostname+"\\tmp\\"+texte,"procedureDoc","location=yes,toolbar=yes,menubar=yes,resizable=yes,scrollbars=yes,width=800,height=600");
        return remote;

        }


 /**
  * kalilab_redir :
  * ...
  *
  *
  * @param unknown ledoc
  * @param unknown url
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function kalilab_redir(ledoc,url){
	ledoc.location.href = url;
	return false;
}

 /**
  * visuDoc :
  * ...
  *
  *
  * @param unknown texte
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function visuDoc(texte)
        {
		var bonus="";
		if(arguments.length>1) bonus=arguments[1];
        remote = window.open(texte,"Document"+bonus,"location=no,toolbar=no,menubar=yes,resizable=yes,scrollbars=yes,width=800,height=600");
        return remote;

        }

 /**
  * visuDocForce :
  * ...
  *
  *
  * @param unknown texte
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function visuDocForce(texte){
        top.location.href = "pjforce.php?fichier="+texte;
        }


 /**
  * funMenuProduit :
  * ...
  *
  *
  * @param unknown url
  * @param unknown type
  * @param unknown id
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function funMenuProduit(url,type,id){
  makeRemote("menuProduit",url+"moduleKalilab/gestion/menuProduit.php?type="+type+"&id="+id,800,450);
  return false;

}


 /**
  * funlocalisation :
  * ...
  *
  *
  * @param unknown form
  * @param unknown input
  * @param unknown inputNom
  * @param unknown url
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function funlocalisation(form,input,inputNom,url)
{
  var aValider=0;
  if(arguments.length==5) aValider=arguments[4];
  var recherche=eval("document."+form+"."+input+".value");
  var tmp=url+"moduleKalilab/localisation/localisation.php?recherche="+recherche+"&form="+form+"&input="+input+"&inputNom="+inputNom+"&validerForm="+aValider;
  makeRemote("localisation",tmp,300,500);
}


 /**                                  
  * localisationZoom : 
  * ...                              
  *                                   
  *                                   
  * @param unknown baseUrl 
  * @param unknown id 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function localisationZoom(baseUrl,id){

	makeRemote("localisationZoom",baseUrl+"/moduleKalilab/localisation/zoom.php?loc="+id,300,300);
	return false;
}

/*************************************************************/


 /**
  * affichediv3 :
  * ...
  *
  *
  * @param unknown division
  * @param unknown tailleY
  * @param unknown tailleX
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function affichediv3(evenement,division,tailleY,tailleX) {
	if( typeof division != 'object'){
		lObjet = getById(division);
	}else{
		lObjet = division;
	}
	
	if(evenement == null){
		if(is_ie5_5up || is_nav6up) evenement = event;
	}
	if(tailleX==0) tailleX=2;
	if(tailleY==0) tailleY=2;

	var myLeftMin = document.body.scrollLeft;
	var myLeftMax = document.body.clientWidth + document.body.scrollLeft - lObjet.offsetWidth -1;
	var myTopMin = document.body.scrollTop;
	var myTopMax = document.body.clientHeight + document.body.scrollTop - lObjet.offsetHeight -1;
	
	if(is_nav6up || is_ie6up){ 
		if(evenement){
			var myLeftUtilisateur = evenement.clientX + document.body.scrollLeft + ((tailleX<0)?tailleX-lObjet.offsetWidth:tailleX);
			var myTopUtilisateur = evenement.clientY + document.body.scrollTop + ((tailleY<0)?tailleY- lObjet.offsetHeight:tailleY);

			var myLeft = (myLeftUtilisateur>myLeftMax)?myLeftMax:myLeftUtilisateur;
			    myLeft = (myLeft<myLeftMin)?myLeftMin:myLeft;
	
			var myTop = (myTopUtilisateur>myTopMax)?myTopMax:myTopUtilisateur;
				myTop = (myTop<myTopMin)?myTopMin:myTop;

			lObjet.style.left = myLeft+"px";
			lObjet.style.top  = myTop+"px";
		}
	} else if(is_ie5_5up){ 
		if(evenement){
			var myLeftUtilisateur = evenement.x + document.body.scrollLeft + ((tailleX<0)?tailleX-lObjet.offsetWidth:tailleX);
			var myTopUtilisateur = evenement.y + document.body.scrollTop + ((tailleY<0)?tailleY- lObjet.offsetHeight:tailleY);
		
			var myLeft = (myLeftUtilisateur>myLeftMax)?myLeftMax:myLeftUtilisateur;
			    myLeft = (myLeft<myLeftMin)?myLeftMin:myLeft;
	
			var myTop = (myTopUtilisateur>myTopMax)?myTopMax:myTopUtilisateur;
				myTop = (myTop<myTopMin)?myTopMin:myTop;
				
			lObjet.style.pixelLeft = myLeft;
			lObjet.style.pixelTop  = myTop;
		}
	}
	
	if(lObjet.style.display == "none") {
		lObjet.style.display = "block";
	} else {
		lObjet.style.visibility = "visible";
	}

}

 /**
  * affichediv :
  * ...
  *
  *
  * @param unknown division
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function affichediv(evenement,division)
{
	affichediv3(evenement,division,2,2);
}

function affichedivpos(division,x,y)
{
	var e = new Object();
	e.x = x;
	e.y = y;
	e.clientX = x;
	e.clientY = y;
	affichediv3(e,division,2,2);
}

 /**
  * affichediv2 :
  * ...
  *
  *
  * @param unknown division
  * @param unknown taille
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function affichediv2(evenement,division,taille)
{
	affichediv3(evenement,division,0,-taille);
}

 /**
  * affichedivTxt :
  * ...
  *
  *
  * @param unknown division
  * @param unknown text
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function affichedivTxt(evenement,division,text)
{
	affichedivTxt3(evenement,division,text,2,2);
}

 /**
  * affichedivTxt2 :
  * ...
  *
  *
  * @param unknown division
  * @param unknown text
  * @param unknown taille
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function affichedivTxt2(evenement,division,text,taille)
{
	affichedivTxt3(evenement,division,text,0,-taille);
}

 /**
  * affichedivTxt3 :
  * ...
  *
  *
  * @param unknown division
  * @param unknown text
  * @param unknown tailleX
  * @param unknown tailleY
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function affichedivTxt3(evenement,division,text,tailleX,tailleY)
{

	document.getElementById(division).innerHTML=text;
	affichediv3(evenement,division,tailleY,tailleX);

}

 /**
  * affichedivfixe :
  * ...
  *
  *
  * @param unknown division
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function affichedivfixe(evenement,division)
{
document.getElementById(division).style.visibility = "visible";
}


 /**                                  
  * affichedivblock : 
  * ...                              
  *                                   
  *                                   
  * @param unknown division 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 



function affichedivblock(evenement,division)
{
document.getElementById(division).style.display = navGetDisplayBlock(document.getElementById(division));
}

 /**                                  
  * affichedivinline : 
  * ...                              
  *                                   
  *                                   
  * @param unknown division 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 



function affichedivinline(evenement,division)
{
document.getElementById(division).style.display = 'inline';
}

 /**
  * cachediv :
  * ...
  *
  *
  * @param unknown division
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function cachediv(division)
{
document.getElementById(division).style.visibility = "hidden";
}

 /**
  * cachedivblock :
  * ...
  *
  *
  * @param unknown division
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function cachedivblock(division)
{
document.getElementById(division).style.display = "none";
}

 /**
  * testnonedivimg :
  * ...
  *
  *
  * @param unknown division
  * @param unknown imgblock
  * @param unknown imgnone
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function testnonedivimg(division,imgblock,imgnone){

	testnonediv(division);

	if(document.getElementById(division).style.display == 'none')	document.getElementById('IMG'+division).src=confBaseUrl+''+imgblock;
	else document.getElementById('IMG'+division).src=confBaseUrl+''+imgnone;

}




function navGetDisplayBlock(elt){
	if(is_ie5_5up){
		return 'block';
	}
	if(is_nav6up){
		switch(elt.nodeName){
			case 'TABLE': return 'table';break;
			case 'TR': return 'table-row';break;
			default: return 'block';break;
		}
	}
	return 'block';
}

 /**
  * testnonediv :
  * ...
  *
  *
  * @param unknown division
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function testnonediv(division)
{
	var elt = document.getElementById(division);
	if (elt){
		if (elt.style.display == "none") elt.style.display = navGetDisplayBlock(elt);
		else elt.style.display = "none";
		return true;
	}
	else return false;
}

 /**
  * testnonedivs :
  * ...
  *
  *
  * @param unknown division
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/

function testnonedivs(baseName){
	var lesTr = document.getElementsByName(baseName);

	for(var i = 0; i < lesTr.length; i++){
		var elt = lesTr[i];
		if (elt.style.display == "none") elt.style.display = navGetDisplayBlock(elt);
		else elt.style.display = "none";
	}

}

 /**
  * testvisibilitydiv :
  * ...
  *
  *
  * @param unknown division
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function testvisibilitydiv(division)
{
if (document.getElementById(division))
{
if (document.getElementById(division).style.visibility == "hidden") document.getElementById(division).style.visibility = "visible";
else document.getElementById(division).style.visibility = "hidden";
return true;
}
else return false;
}

 /**
  * verifie :
  * ...
  *
  *
  * @param unknown nom
  * @param unknown txt
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function verifie(nom,txt)
{

if ((eval("document.truc."+nom+".value.length"))>250) {alert("Le champs "+txt+" est trop long ...");return false;}
else return true;
}

 /**
  * verificationQte :
  * ...
  *
  *
  * @param unknown form
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function verificationQte(form)
{
qte=eval("document."+form+".qte");
qteRestante=eval("document."+form+".qteRestante");

if ( qte.value-qteRestante.value<0  )
 {alert("La quantité restante doit être plus petite ou égale à la quantité reçue (non nulle) !");return false;}
else return true;

}

 /**
  * rechercheFormFromNom :
  * ...
  *
  *
  * @param unknown nomInput
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function rechercheFormFromNom(nomInput){
	var nbrForm=document.forms.length;
	var maForm;
		var trouve=0;
			for (var i=0;i<nbrForm && trouve==0;i++)
				for (var j=0;j<document.forms[i].elements.length && trouve==0;j++) {

					if (document.forms[i].elements[j].name==nomInput) {
						maForm=document.forms[i];
						trouve=1;
					}

			}
		if (trouve==1) return maForm; else return false;
}

 /**
  * selectionDeLaDivAvecRemplissage :
  * ...
  *
  *
  * @param unknown type
  * @param unknown maForm
  * @param unknown nom
  * @param unknown nomAbrege
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function selectionDeLaDivAvecRemplissage(evenement,type,maForm,nom,nomAbrege)
{

	var obj=maForm[nom];
	var compteur=0;
	var str='';

	if (type=='choisir') {
		document.getElementById('buttonRecap'+nomAbrege).className='corpsFonce';
		cachedivblock('descr'+nomAbrege);
		affichedivblock(evenement,'select'+nomAbrege);
	}
	else {
		for (var intLoop=0; intLoop < obj.length; intLoop++) {
			if (obj[intLoop].selected == true && str.indexOf(obj[intLoop].innerHTML+'<BR>')==-1) {
				str+=obj[intLoop].innerHTML+'<BR>';
				compteur++;
			}
		}

		if (compteur>0 || type=='recap') {
			document.getElementById('buttonRecap'+nomAbrege).className='titre';
			cachedivblock('select'+nomAbrege);
			affichedivblock(evenement,'descr'+nomAbrege);
			affichedivTxt(evenement,'descr'+nomAbrege,str);
		}
	}
}

function selectionDeLaDivAgrandir(nom){

	var leSelect = document.getElementById(nom);
	if(leSelect){
		if(leSelect.getAttribute('agrandir') == 1 ) {
			leSelect.setAttribute('agrandir',0);
			leSelect.size = leSelect.getAttribute('sizeOld');
		}else{
			leSelect.setAttribute('agrandir',1);
			leSelect.setAttribute('sizeOld',leSelect.size);
			leSelect.size = Math.min(leSelect.options.length,20);
		}
		
	}

}
 /**                                  
  * afficheHeure : 
  * ...                              
  *                                   
  *                                   
  * @param unknown heure 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
 function afficheHeure(heure) { 
  
      h = parseInt(heure.substr(0,2), 10); 
      m = parseInt(heure.substr(2,2), 10); 
  
      if (heure.length>0 ) { 
           if ( h < 24 && m < 60 ) return heure.substr(0,2)+":"+heure.substr(2,2); 
           else if ( h < 24 && !m ) return heure.substr(0,2)+":00"; 
           else alert('Heure erronée'); 
      } 
  
      return ""; 
 }

 /**
  * autoComplete : Complete la saisie a la frappe
  *
  *
  * @param input field  L'input ou a lieu la saisie
  * @param select select  La source de données possibles
  * @param string property  Le champ a comparer dans le select
  * @param bool forcematch  Vrai si la saisie doit obligatoirement faire parti de la liste . Faux si une autre valeur est possible.
  *
  * @return Propose une possibilite et mets a jour la liste
  *
  * @since 26 Mar 2004
  *
  **/
function autoComplete (field, select, property, forcematch) {
	var found = false;
	for (var i = 0; i < select.options.length; i++) {
		if (select.options[i].getAttribute(property).toUpperCase() == field.value.toUpperCase()) {
			found=true; ifound = i; break;
			}
		if (!found && select.options[i].getAttribute(property).toUpperCase().indexOf(field.value.toUpperCase()) == 0) {
			found=true; ifound = i;
			}
	}
	if (found) { select.selectedIndex = ifound; }
	else { select.selectedIndex = -1; }

//Internet Explorer
if(navigator.appName=="Microsoft Internet Explorer"){
	if (field.createTextRange) {
		if (forcematch && !found) {
			field.value=field.value.substring(0,field.value.length-1);
			return;
			}
		var cursorKeys ="8;46;37;38;39;40;33;34;35;36;45;";
		if (cursorKeys.indexOf(event.keyCode+";") == -1) {
			var r1 = field.createTextRange();
			var oldValue = r1.text;
			var newValue = found ? select.options[ifound][property] : oldValue;
			if (newValue != field.value) {
				field.value = newValue;
				var rNew = field.createTextRange();
				rNew.moveStart('character', oldValue.length) ;
				rNew.select();
				}
			}
		}
	}
else{
//Mozilla
	if (field.selectionStart) {
		if (forcematch && !found) {
			field.value=field.value.substring(0,field.value.length-1);
			return;
			}
		var cursorKeys ="8;46;37;38;39;40;33;34;35;36;45;";

		if (cursorKeys.indexOf(Event.keyCode+";") == -1) {
			var r1 = field.selectionStart;
			var oldValue = r1.text;
			var newValue = found ? select.options[ifound].getAttribute(property) : oldValue;
			if (newValue != field.value) {

				field.value = newValue;

				}
			}
		}

}
	}

 /**
  * documentResizeAuto :
  * ...
  *
  *
  *
  * @return
  *
  * @since 30 Jan 2004
  * @access public
  *
  **/
function documentResizeAuto(){

	if(document.getElementById('ancreDeFinAffichage'))
	{
		popupHeight=document.getElementById('ancreDeFinAffichage').offsetTop;
		popupWidth=document.body.clientWidth + 10;
		addHeight=33;
		popupHeight=((popupHeight+addHeight)>screen.height)?screen.height-addHeight:popupHeight+addHeight;
		window.resizeTo(popupWidth,popupHeight);
	}
}

function klNavTabSwitch(id,n) {
	nc=document.getElementsByName(id+"navcell");
	if(nc){
		t=document.getElementsByName(id+"tb")
		for(i=0;i<nc.length;i++){
			nc.item(i).className="tab-off corpsFonce";
			t.item(i).className="hide";
		}

		nc.item(n).className="tab-on ";
		t.item(n).className="tab-content show";
	}
}

function klNavTabVSwitch(id,n) {
	nc=document.getElementsByName(id+"navcell");
	if(nc){
		t=document.getElementsByName(id+"tb")
		for(i=0;i<nc.length;i++){
			if(t.item(i).className != "hide"){
				nc.item(i).className="tabv-off corpsFonce";
				t.item(i).className="hide";
			}
		}

		nc.item(n).className="tabv-on ";
		t.item(n).className="tabv-content show";
	}
}

function klElementGetTop(obj){return parseInt(obj.style.top);}

function klElementSetTop(obj,val){	obj.style.top = val+'px';}

function klElementGetLeft(obj){return parseInt(obj.style.left);}

function klElementSetLeft(obj,val){	obj.style.left = val+'px';}

function AnchorPosition_getPageOffsetLeft(el){var ol=el.offsetLeft;while((el=el.offsetParent) != null){ol += el.offsetLeft;}return ol;}

function AnchorPosition_getWindowOffsetLeft(el){return AnchorPosition_getPageOffsetLeft(el)-document.body.scrollLeft;}

function AnchorPosition_getPageOffsetTop(el){var ot=el.offsetTop;while((el=el.offsetParent) != null){ot += el.offsetTop;}return ot;}

function AnchorPosition_getWindowOffsetTop(el){return AnchorPosition_getPageOffsetTop(el)-document.body.scrollTop;}

function Event_getPageOffsetLeft(){if(!window.event) return -1; return window.event.clientX+document.body.scrollLeft; }

function Event_getPageOffsetTop(){if(!window.event) return -1; return window.event.clientY+document.body.scrollTop; }

function comparaisonDate(date1,date2,inclus){
	date1A = date1.split("-");
	date2A = date2.split("-");
	if(date1A.length != 3) return false;
	if(date2A.length != 3) return false;
     if (date1A[2] < date2A[2])
        return true;
     if (date1A[2] == date2A[2])
       if (date1A[1] < date2A[1])
              return true;
     if (date1A[2] == date2A[2])
       if (date1A[1] == date2A[1])
         if ( (date1A[0] < date2A[0]) || (inclus && (date1A[0] == date2A[0])) )
              return true;
     return false;
}


/* Find In Page Script- By Mike Hall (MHall75819@aol.com) */
/* Recherche de mot */
var win = window;    // window to search.
var numRecherche   = 0;

function findInPage(str) {
	var txt, i, found;
	if (str == "") return false;
	if (is_nav) {
		// Look for match starting at the current point. If not found, rewind
		// back to the first match.
		if (!win.find(str))
			while(win.find(str, false, true))
			numRecherche++;
		else
			numRecherche++;
		// If not found in either direction, give message.
		//if (n == 0)
		//	alert("Je suis navré, je n'ai rien trouvé. Vérifiez l'orthographe.");
	}
	if (is_ie) {
		txt = win.document.body.createTextRange();
		// Find the nth match from the top of the page.
		for (i = 0; i <= numRecherche && (found = txt.findText(str)) != false; i++) {
			txt.moveStart("character", 1);
			txt.moveEnd("textedit");
		}
		// If found, mark it and scroll it into view.
		if (found) {
			numRecherche++;
			txt.moveStart("character", -1);
			txt.findText(str);
			try{
				txt.select();
				txt.scrollIntoView();
			}catch(e){
				findInPage(str);
				return false;
			}
	  	}
		else {
			if (numRecherche > 0) {
				numRecherche = 0;
				findInPage(str);
			}
		}
	}
	return false;
}

function klStopEvent(evenement) {
	if (is_ie5_5up) {
		if(window.event){
			window.event.cancelBubble = true;
			window.event.returnValue = 0; 
		}
	} else if(evenement){
		evenement.preventDefault();
		evenement.stopPropagation();
	}
};

function addEvent(obj,event_name,func_name){
	if(obj.attachEvent){
		obj.attachEvent("on"+event_name, func_name);
	}else if(obj.addEventListener){
		obj.addEventListener(event_name,func_name,true);
	}else{
		obj["on"+event_name] = func_name;
	}
}

function removeEvent(obj,event_name,func_name){
	if(obj.detachEvent){
		obj.detachEvent("on"+event_name, func_name);
	}else if(obj.removeEventListener){
		obj.removeEventListener(event_name,func_name,true);
	}else{
		obj["on"+event_name] = "";
	}
}

function srcEvent(evt){
	return (evt.target) ? evt.target : evt.srcElement
}

 /**                                  
  * actionEnable : réactive un bouton issue de displayAction avec l'option @ dans l'action
  *                                   
  * exemple:
  * return (form_validate(event,Array('securiteSociale',Array('gt',99999999999999)))||actionEnable('btEnreg'));
  *                            
  * @param string id l'identifiant de l'action       
  * @return false 
  *                                   
  * @since 17 Aou 2005
  * @access public                    
  *                             
  **/   
function actionEnable(id){
	var enabled = true;
	if( arguments.length > 1) enabled = arguments[1];

	var obj = document.getElementById('tab'+id);
	if(obj){
		obj.disabled = !enabled;
		obj.className = (enabled?'':'disabled');
	}
	return false;
}

function in_array_js(my_array,my_value) {
    for(i = 0 ; i < my_array.length ; i++) {
		if(my_array[i] == my_value) {
			return true;
		}
	}
	return false;
}

function getById(objectID){
	return (document.getElementById(objectID));
}

function demandePasswordURL(event,url,javascriptBonus) {
	if( getById('divdemandePasswordURL') ){
		promptbox =  getById('divdemandePasswordURL');
		document.getElementById("divdemandePasswordURLbox").setAttribute ('id' , 'divdemandePasswordURLOLD') ;				
	}else{
		promptbox = document.createElement('div'); 
		promptbox.setAttribute ('id' , 'divdemandePasswordURL') ;
		document.getElementsByTagName('body')[0].appendChild(promptbox) ;

		promptboxstyle = eval("document.getElementById('divdemandePasswordURL').style") ;
		promptboxstyle.position = 'absolute' ;
		promptboxstyle.top = 100 ;
		promptboxstyle.left = 200 ;
		promptboxstyle.width = 300 ;
		promptboxstyle.zIndex = 50 ;
		promptboxstyle.border = 'outset 1 #AAAAAA' ; //#bbbbbb
		
		iframe = document.createElement('iframe');
		iframe.src = 'javascript:false';
		iframe.frameBorder='no';
		iframe.scrolling='no';
		iframe.style.position='absolute';
		iframe.style.top = 100;//div.style.top;
		iframe.style.left = 200;//div.style.left;
		iframe.id = 'iframedemandePasswordURL';
		iframe.style.zIndex = '49';
		iframe.border = '0';
		document.body.appendChild(iframe);
		
		
	}

	promptbox.innerHTML = "<table cellspacing='0' cellpadding='2' border='0' width='100%' class='corpsFonce'>\
							<form method=post action=\""+url+"\" onSubmit=\"if(this.password.value.length<=0) {getById('divdemandePasswordURLbox').focus();return false;}else{"+javascriptBonus+"} \">\
								<tr >\
									<td align='center' width='22' height='22' style='text-indent:2;' class='titre'><img src='" + confBaseUrl + "images/icopassmodif.gif' ></td>\
									<td class='titre' width=* align='center' ><b>"+STR_VERIF_PASSWORD+"</b></td>\
									<td align='center' width='22' height='22' style='text-indent:2;' class='titre'><img src='" + confBaseUrl + "images/attention.gif' ></td>\
								</tr><tr>\
									<td colspan=3 align='center'><br><input name='password' type='password' id='divdemandePasswordURLbox'></td>\
								</tr><tr>\
									<td colspan=3 align='center'>\
										<br>\
										<input type='image' src=\""+confBaseUrl+"images/icoval.gif\" > \
										&nbsp;&nbsp;&nbsp;&nbsp; \
										<IMG class=hand src=\""+confBaseUrl+"images/icofermer.gif\"  onClick=\"cachediv('divdemandePasswordURL');cachediv('iframedemandePasswordURL');\">\
										</td>\
								</form>\
								</tr></table>" ;

	affichediv(event,'divdemandePasswordURL');
	affichedivfixe(event,'iframedemandePasswordURL');
	
	getById('iframedemandePasswordURL').style.height = promptbox.offsetHeight + "px";
	getById('iframedemandePasswordURL').style.width = promptbox.offsetWidth + "px";
	getById('iframedemandePasswordURL').style.top = promptbox.style.top;
	getById('iframedemandePasswordURL').style.left = promptbox.style.left;
	
	getById("divdemandePasswordURLbox").focus() ;
	getById("divdemandePasswordURLbox").focus() ;

	return false;
} 

function saisieDirectResizeInput(obj) {
	var taille = obj.value.length;
	if(taille < 3) {
		obj.size = 1;
	} else if(taille < 10) {
		obj.size = (obj.value.length+1)*1.01;
	} else if(taille < 15) {
		obj.size = (obj.value.length+1)*1.02;
	} else if(taille < 20) {
		obj.size = (obj.value.length+1)*1.03;
	} else {
		obj.size = (obj.value.length+1)*1.04;
	}
}

function inArray(the_needle, the_haystack){
    var the_hay = the_haystack.toString();
    if(the_hay == ''){
        return false;
    }
    var the_pattern = new RegExp(the_needle, 'g');
    var matched = the_pattern.test(the_haystack);
    return matched;
}

function formatSaisieHeure(heure,forceVal) {
	nonCommunique = false;
	if(arguments.length > 2){ nonCommunique = arguments[2]; }
	if(nonCommunique == true && (heure.value == "Non communiquée" || heure.value == "Absence")) return true;
	var reg = new RegExp("^([01][0-9]|2[0-3]|[0-9])[.:]?([0-5][0-9])[.:]?([0-5][0-9])?$", "g");
	var res = reg.exec(heure.value);
	if(res != null) {
	   if(res[1].length==1) heure.value="0"+res[1]+":"+res[2];
	   else heure.value=res[1]+":"+res[2];
    } else {
    	if(heure.value != "" && forceVal && forceVal > 0) {
			alert('Format heure non valide');
    		heure.value = '';
    	}
    }
}

function kKeyCode(evt,leCode) {
	var keyCode = evt.keyCode ? evt.keyCode : evt.which ? evt.which : evt.charCode;
	return (keyCode == leCode); 
}

function getHeure(input) {
	var ObjetDate = new Date();
	var heure = parseInt(ObjetDate.getHours());if (heure<10) heure="0"+heure;
	var minute = parseInt(ObjetDate.getMinutes());if (minute<10) minute="0"+minute;
	return heure+":"+minute;
}

function afficheDate(date,sep){
     if(sep == undefined){
         sep = "-";
     }

     //trim
     date = date.replace(/^\s+|\s+$/gm, '');
     var tmpDate = null;
     var regs = null;

     var regex1 = /^([0-9-\/\s]*) [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}$/;
     var regex2 = /^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})$/;
     // suppression de l'heure si presente
     if(tmpDate = regex1.exec(date)) {
        date = $tmpDate[1];
     }
     if(regs = regex2.exec(date)){
        tmp = sprintf("%02d",parseInt(regs[3],'10'))+sep+sprintf("%02d",parseInt(regs[2],'10'))+sep+regs[1];
    	return tmp;
     }
     else {
        return date;
     }
 }