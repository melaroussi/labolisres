	                                  
 /**                                
  * commentaire                     
  *                                 
  *        		                 
  * @package KaliLab                
  * @module KaliLab                
  * @author Netika <info@netika.net>
  **/                               

var myHTrackkeydown=0;
var onlyOneSubmit = Array();


function firstSubmit(leForm){
	var tmp = parseInt(leForm.getAttribute('nbSubmit'));
	if(!isNaN(tmp) && tmp>0){
		alert('Validation du formulaire!\n\nUn clic suffit.');
		leForm.setAttribute('nbSubmit',tmp+1);
		return false;
	}else{
		leForm.setAttribute('nbSubmit',1);
		return true;
	}
	
}




 /**                                  
  * myHTrackKeyUp : 
  * ...                              
  *                                   
  *                                   
  * @param unknown monInput 
  * @param unknown laBaseInput   
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function myHTrackKeyUp(monInput,laBaseInput){
	var monInputValue = monInput.value;
	var leSecondInput = monInput.form[laBaseInput+'[M]'];
	var leResuInput = monInput.form[laBaseInput+'[HM]'];
	
	var argv = arguments;
    var argc = argv.length;
    if (argc > 1)
        type = argv[2];
    else 
        type ="horaire";
           
	if(myHTrackkeydown==monInput && monInputValue.length==2) {
        if (type != "duree" && monInputValue > 23)
            monInput.value = "23";
        leSecondInput.focus();
    }
	if(myHTrackkeydown==monInput && monInputValue.length==1 && monInputValue > 2  && monInputValue < 10 && type != "duree") {
		monInput.value = "0" + monInput.value ;
		leSecondInput.focus();
	}
	
	myHTrackRefresh(monInput,laBaseInput);
	myHTrackkeydown=0;
}

 /**                                  
  * myHTrackKeyDown : 
  * ...                              
  *                                   
  *                                   
  * @param unknown monInput 
  * @param unknown nb 
  * @param unknown leSecondInput 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function myHTrackKeyDown(monInput,nb,leSecondInput){
	myHTrackkeydown=monInput;
}

 /**                                  
  * myHTrackRefresh : 
  * ...                              
  *                                   
  *                                   
  * @param unknown monInput 
  * @param unknown laBaseInput 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function myHTrackRefresh(monInput,laBaseInput){
	var lePremierInputValue = monInput.form[laBaseInput+'[H]'].value;
	var leSecondInputValue = monInput.form[laBaseInput+'[M]'].value;
	var leResuInput = monInput.form[laBaseInput+'[HM]'];
	
	if(lePremierInputValue.length==1) lePremierInputValue='0'+lePremierInputValue;
	if(leSecondInputValue.length==1) leSecondInputValue='0'+leSecondInputValue;
	monInput.form[laBaseInput+'[M]'].value = leSecondInputValue;
	leResuInput.value = lePremierInputValue+leSecondInputValue;
}

 /**                                  
  * myHTrackUpdate : 
  * ...                              
  *                                   
  *                                   
  * @param unknown monForm 
  * @param unknown monInput 
  * @param unknown heure 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function myHTrackUpdate(monForm,monInput,heure) {
	monForm[monInput+'[HM]'].value=heure;
	monForm[monInput+'[H]'].value=heure.substr(0,2);
	monForm[monInput+'[M]'].value=heure.substr(2,4);


}

 /**                                  
  * textarea_maxlength : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  * @param unknown autoUpdate 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function textarea_maxlength(obj,autoUpdate) 
{ 
	var monmax;
	monmax =obj.getAttribute('maxlength');
	
	if(monmax!=null && !isNaN(monmax)){
		if (monmax-obj.value.length < 0) { 
			if(autoUpdate) obj.value = obj.value.substring(0,monmax);
			return validator_error(obj,"Attention ce champ ne doit pas dépasser " + monmax + " caractères ! ");
		}
		else validator_resetClassError(obj);
	}
	return true;
}
 /**                                  
  * textarea_minlength : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function textarea_minlength(obj) 
{ 
	var monmin;
	monmin =obj.getAttribute("minlength");
	if(monmin!=null && !isNaN(monmin)){
		if (monmin >= obj.value.length) { 
			return validator_error(obj,"Attention ce champ doit être composé de plus de " + monmin + " caractères ! ");
		}
		else validator_resetClassError(obj);
	}
	return true;
}
 /**                                  
  * textarea_maxlength_self : 
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
function textarea_maxlength_self(){
	var autoUpdate;
	obj = window.event.srcElement; 
	if(!obj) return true;
	if(obj.getAttribute('autoupdate') && obj.getAttribute('autoupdate').toUpperCase() == "FALSE") autoUpdate=false; else autoUpdate=true;
	textarea_maxlength(obj,autoUpdate);
}

 /**                                  
  * textarea_maxlength_ini : 
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
function textarea_maxlength_ini(){
	var obj;
	obj = window.event.srcElement; 
	if(!obj || obj.type != 'textarea') return true;
	obj.onclick = textarea_maxlength_self;
	obj.onfocus = textarea_maxlength_self;
	obj.onblur = textarea_maxlength_self;
	obj.onkeyup = textarea_maxlength_self;
}
 /**                                  
  * validator_error : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  * @param unknown msg 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function validator_error(obj,msg){
	if(obj) {
		try{ obj.focus(); }catch (error){/*rien*/}
		if( obj.getAttribute('validatorError')) alert("Erreur: "+obj.getAttribute('validatorError'));
		else alert(msg);
	}
    validator_setClassError(obj);
	return false;
}
 /**                                  
  * validator_isValidTarget : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function validator_isValidTarget(obj){
	try{
		var condition=obj.getAttribute("VALIDATORIF");
		if (condition == null){return true;}
		return eval(condition);
		}
	catch (error){
		//alert(error.message);
		return false;
	}
	return true;
}
 /**                                  
  * lib_isArray : 
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
function lib_isArray() {
        if (typeof arguments[0] == 'object') {
          var criterion = arguments[0].constructor.toString().match(/array/i);
           return (criterion != null);
          }
        return false;
}
 /**                                  
  * lib_isString : 
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
function lib_isString() {return (typeof arguments[0] == 'string');}
 /**                                  
  * validator_setClassError : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function validator_setClassError(obj){if(obj.oldClassName == undefined) obj.oldClassName=""+obj.className;obj.className = 'validator_error';}
 /**                                  
  * validator_resetClassError : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function validator_resetClassError(obj){if(obj.oldClassName != undefined) obj.className=obj.oldClassName;}
 /**                                  
  * lib_isInt : 
  * ...                              
  *                                   
  *                                   
  * @param unknown val 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_isInt(val) {return (!(val.search(/^([0-9]*)$/i) == -1));/*return parseInt(val)==val;*/}
 /**                                  
  * lib_isFloat : 
  * ...                              
  *                                   
  *                                   
  * @param unknown val 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_isFloat(val) {return parseFloat(val)==val;}
 /**                                  
  * lib_isMail : 
  * ...                              
  *                                   
  *                                   
  * @param unknown strEmail 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_isMail(strEmail){return (!(strEmail.search(/^[^@]+@[^@]+.[a-z]{2,}$/i) == -1));}
 /**                                  
  * lib_isDate : 
  * ...                              
  *                                   
  *                                   
  * @param unknown strDate 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_isDate(strDate){return (!(strDate.search(/^([1-2][0-9]|[0]?[1-9]|[3][01])-([1][0-2]|[0]?[1-9])-([1|2][0-9]{3})$/i) == -1));}
 /**                                  
  * lib_isHeure : 
  * ...                              
  *                                   
  *                                   
  * @param unknown strHeure 
  *                                   
  * @return                           
  *                                   
  * @since 08 Apr 2008       
  * @access public                    
  *                             
  **/
function lib_isHeure(strHeure) {return (!(strHeure.search(/^([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/i) == -1));}
 /**                                  
  * lib_str_parse : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  * @param unknown regle 
  * @param unknown value 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_str_parse(obj,regle,value){
	if (lib_isArray(regle)){
		switch( regle[0]){
			case 'nz':regle[1]=0;
			case 'ne':if(!isNaN(regle[1]) && regle[1] == value.length) return validator_error(obj,'Vous devez saisir une chaîne de caractère de taille différente à '+regle[1]+' !');break;
			case 'gt':if(!isNaN(regle[1]) && regle[1] >= value.length) return validator_error(obj,'Vous devez saisir une chaîne de caractère de taille supérieure à '+regle[1]+' !');break;
			case 'ge':if(!isNaN(regle[1]) && regle[1] > value.length) return validator_error(obj,'Vous devez saisir une chaîne de caractère de taille supérieure ou égale à '+regle[1]+' !');break;
			case 'lt':if(!isNaN(regle[1]) && regle[1] <= value.length) return validator_error(obj,'Vous devez saisir une chaîne de caractère de taille inférieure à '+regle[1]+' !');break;
			case 'le':if(!isNaN(regle[1]) && regle[1] < value.length) return validator_error(obj,'Vous devez saisir une chaîne de caractère de taille inférieure ou égale à '+regle[1]+' !');break;
			case 'eq':if(!isNaN(regle[1]) && regle[1] != value.length) return validator_error(obj,'Vous devez saisir une chaîne de caractère de taille égale à '+regle[1]+' !');break;
			case 'strne':if(regle[1] == value) return validator_error(obj,'Vous devez saisir une chaîne de caractère différente de : "'+regle[1]+'" !');break;
		}//fin switch
	}//fin if
	return true; // cas par defaut
}
 /**                                  
  * lib_number_parse : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  * @param unknown regle 
  * @param unknown value 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_number_parse(obj,regle,value){
	if (lib_isArray(regle)){
		switch( regle[0]){
			case 'nz':regle[1]=0;
			case 'strne': //pareil a ne
			case 'ne':if(!isNaN(regle[1]) && regle[1]==value) return validator_error(obj,'Vous devez saisir/sélectionner un nombre différent de '+regle[1]+' !');break;
			case 'gt':if(!isNaN(regle[1]) && regle[1]>=value) return validator_error(obj,'Vous devez saisir/sélectionner un nombre supérieur à '+regle[1]+' !');break;
			case 'ge':if(!isNaN(regle[1]) && regle[1]>value) return validator_error(obj,'Vous devez saisir/sélectionner un nombre supérieur ou égal à '+regle[1]+' !');break;
			case 'lt':if(!isNaN(regle[1]) && regle[1]<=value) return validator_error(obj,'Vous devez saisir/sélectionner un nombre inférieur à '+regle[1]+' !');break;
			case 'le':if(!isNaN(regle[1]) && regle[1]<value) return validator_error(obj,'Vous devez saisir/sélectionner un nombre inférieur ou égal à '+regle[1]+' !');break;
			case 'enum':
						var intTrouve = false;
						var intListe = "";var espace = "";
						for(var i=1 ; i<regle.length ; i++){
							if(!isNaN(regle[i]) && regle[i]==value){
								intTrouve = true;
							}
							intListe += espace+regle[i];
							espace = ", ";
						}
						if(!intTrouve)		return validator_error(obj,'Vous devez saisir/sélectionner un nombre égal à '+intListe+' !');
						break;
		}//fin switch
	}//fin if
	return true; // cas par defaut
}
 /**                                  
  * lib_date_toString : 
  * ...                              
  *                                   
  *                                   
  * @param unknown date 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_date_toString(date){
	var valueSplit,i=0;
	if(!(valueSplit=date.split('-')) || valueSplit.length != 3) return false;
	while(valueSplit[2].length<4 && i<5) {valueSplit[2] = "0"+valueSplit[2];i++;}
	while(valueSplit[1].length<2 && i<3) {valueSplit[1] = "0"+valueSplit[1];i++;}
	while(valueSplit[0].length<2 && i<3) {valueSplit[0] = "0"+valueSplit[0];i++;}
	return valueSplit[2]+valueSplit[1]+valueSplit[0];
}
 /**                                  
  * lib_date_parse : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  * @param unknown regle 
  * @param unknown value 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_date_parse(obj,regle,value){
	var valueStr,regleValueStr;
	if(!(valueStr=lib_date_toString(value))) return false;
	if (lib_isArray(regle)){
		if(!(regleValueStr=lib_date_toString(regle[1]))) return false;
		switch( regle[0]){
			case 'strne': //pareil a ne 
			case 'ne':if( regle[1]==valueStr) return validator_error(obj,'Vous devez saisir/sélectionner une date différente de '+regle[1]+' !');break;
			case 'gt':if(regleValueStr>=valueStr) return validator_error(obj,'Vous devez saisir/sélectionner une date postérieure au '+regle[1]+' !');break;
			case 'ge':if(regleValueStr>valueStr) return validator_error(obj,'Vous devez saisir/sélectionner une date postérieure ou égale au '+regle[1]+' !');break;
			case 'lt':if(regleValueStr<=valueStr) return validator_error(obj,'Vous devez saisir/sélectionner une date antérieure au '+regle[1]+' !');break;
			case 'le':if(regleValueStr<valueStr) return validator_error(obj,'Vous devez saisir/sélectionner uune date antérieure ou égale au '+regle[1]+' !');break;
		}//fin switch
	}//fin if
	return true; // cas par defaut
}
 /**                                  
  * lib_select_nbselect : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  * @param unknown regleNumber 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_select_nbselect(obj,regleNumber){
	if (!obj.options) return true;
	var selectedNb=0;
	for(var i=0;i<obj.options.length;i++) {
			if(obj.options[i].selected)		selectedNb++;
	}
	return (lib_number_parse(obj,regleNumber,selectedNb));
}
 /**                                  
  * lib_inArray : 
  * ...                              
  *                                   
  *                                   
  * @param unknown monarray 
  * @param unknown pattern 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_inArray(monarray,pattern){
	if(!lib_isArray(monarray)) return false;
	for(i=0;i<monarray.length;i++){
		if(!lib_isString(monarray[i])) continue;
		if(monarray[i].search(pattern) > -1) return true;
	}
	return false;
}

 /**                                  
  * lib_select_selectone : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  * @param unknown action 
  * @param unknown notin 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function lib_select_selectone(obj,action,notin){
	if (!obj.options) return true;
	for(var i=0;i<obj.options.length;i++) {
		if(obj.options[i].selected){
			if(lib_inArray(notin,obj.options[i].value) == action)		return true;
			else return validator_error(obj,'Vous ne pouvez pas choisir cette valeur "'+obj.options[i].innerHTML+'" !');
		}
	}
	return true;
}
 /**                                  
  * validator_isTimesdate : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  *                                   
  * @return                           
  *                                   
  * @since 08 Apr 2008       
  * @access public                    
  *                             
  **/ 

function lib_isTimesdate(obj){
	var text = obj.value;
	var date = text.substr(0,10); //coupe la chaine "text" à partir de l'élément 0, jusqu'aux 10 éléments suivant 
	var heure = text.substr(11,8);//coupe la chaîne "text" à partir de l'élément 11, et jusqu'aux 8 éléments suivant
	var jour = date.substr(0,2);
	var mois = date.substr(3,2);
	var an = date.substr(6,4);
	
	if(!lib_isDate(date))
		return validator_error(obj,'Vous devez saisir une date de la forme 23-12-2003 !');
	if(!lib_isHeure(heure))
		return validator_error(obj,'Vous devez saisir une heure de la forme 15:37:20 !');
	obj.focus();
	return true;	
}  

 /**                                  
  * text_validate : 
  * ...                              
  *                                   
  *                                   
  * @param unknown obj 
  *                                   
  * @return                           
  *                                   
  * @since 30 Jan 2004       
  * @access public                    
  *                             
  **/                                 
function text_validate(obj){
	var argv = arguments;
    var argc = argv.length;
	if(!obj) return true;
	var type = obj.getAttribute('dataType'); 
	var value=obj.value;
	if(argc>1) rules=argv[1]; else rules=Array();
	nbRules = rules.length;
	switch(type){
		case 'string':	for(i=0;i<nbRules;i++){if(!lib_str_parse(obj,rules[i],value)) return false;}//fin for
						break;
		case 'date':	if (!lib_isDate(value)) return validator_error(obj,'Vous devez saisir une date de la forme 23-12-2003!');
						for(i=0;i<nbRules;i++){if(!lib_date_parse(obj,rules[i],value)) return false;}//fin for
						break;
		case 'timesdate':lib_isTimesdate(obj);break;
		case 'mail':	if (!lib_isMail(value)) return validator_error(obj,'Vous devez saisir une adresse mail de la forme monadresse@test.com!');break;
		case 'int':		if (!lib_isInt(value)) return validator_error(obj,'Vous devez saisir un entier!');			 
		case 'float':	if (!lib_isFloat(value)) return validator_error(obj,'Vous devez saisir un nombre!\nRq: Le séparateur est un point.');			 
						for(i=0;i<nbRules;i++){if(!lib_number_parse(obj,rules[i],value)) return false;}//fin for
		}//fin case
	validator_resetClassError(obj);
	return true;
}
 /**                                  
  * form_validate : 
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
function form_validate(evt){
	var argv = arguments;
    var argc = argv.length;
	var i;
	var data = Array();
	var monForm = (is_ie5_5up?evt.srcElement:evt.target);
	var elements = monForm.elements;
	for(i=1;i<argv.length;i++){
		key = argv[i][0];
		argv[i].shift();
		data[key] = argv[i];
	}

	
	for(var iElt=0;iElt<elements.length;iElt++){
		var objTypeNe,objTypeNeValue;
		elt = data[elements[iElt].name];
		if(!lib_isArray(elt)) elt = Array();
		obj = elements[iElt];
		if(!obj) continue;
		value = obj.value;
		var objDataType = obj.getAttribute('dataType');
		var objType = obj.type;
		var nbRules = elt.length
		if(obj.getAttribute('dataTypeNE') !=undefined) {
				//permet de définir un champ obligatoire
				objTypeNe=true;
				objTypeNeValue=obj.getAttribute('dataTypeNE');
			}
		else objTypeNe=false;
		if(!validator_isValidTarget(obj)) {/*alert("false");*/continue;}
//		else alert("true");
		//alert(obj.name + '\n\n\n' + elt);
		switch(objType){
			case 'text':	if(!text_validate(obj,elt)) return false;
							if(objTypeNe && !text_validate(obj,Array(Array('strne',objTypeNeValue)))) return false;
							break;
			case 'textarea':if(!textarea_maxlength(obj,false)) return false;
							if(!textarea_minlength(obj)) return false;
							break;
			case 'select-one':
					for(i=0;i<nbRules;i++){
						if(!lib_isArray(elt[i])) continue;
						switch(elt[i][0].toUpperCase()){
							case "SELECTONELIKE" :if(!lib_select_selectone(obj,true,elt[i][1])) return false;break;
							case "SELECTONENOTLIKE" :if(!lib_select_selectone(obj,false,elt[i][1])) return false;break;
						}
					}//fin for
					break;
			case 'select-multiple':	
					for(i=0;i<nbRules;i++){
						switch(elt[i][0].toUpperCase()){
							case "NBSELECT" :if(!lib_select_nbselect(obj,elt[i][1])) return false;break;
						}
					}//fin for
					break;
		}// fin switch objType
		validator_resetClassError(obj);
	}//fin for iElt
	return true;
}

var validator_verifie_date_focus = false;
var validator_verifie_heure_focus = false;

function validator_masque_date(e,obj) {
	var ch;
	var ch_gauche, ch_droite;
	var lancerCalendar = false;
			
	ch = obj.value;
	ch.toString(); 
	//ch = ch.slice(0,10);
		// on nettoie la date
	var chOk = String();
	var last = "";
	termine = false;
	for(var c=0; c<ch.length && !termine ; c++){
		var car = ch.slice(c,c+1);
		if( !isNaN( parseInt(car) ) ) {
			chOk += car;
		} else if( ( (car == '-') || (car == '+') ) && (obj.value=='+' || obj.value=='-' || obj.value.length == 11) ) {
			if(document.getElementById(obj.id))	{
				if(obj.value.length == 11) {
					var tmp = obj.value.substr(0,10).split("-");
					var t = new Date(tmp[2],(tmp[1]-1),tmp[0]);
					var monC = obj.value.substr(10,1);
				} else {
					var t = new Date();
					var monC = obj.value;
				}
				var today = t.getTime();
				if(monC == '+') {
					today = today + 86400000;
				}
				else if(monC == '-') {
					today = today - 86400000;
				}
				var d = new Date(today);
				var j = d.getDate();
				var m = d.getMonth() + 1;
				if(j<10) j = "0"+j; 
				if(m<10) m = "0"+m; 
				chOk = ''+j+'-'+m+'-'+d.getFullYear();
				termine = true;
			}
		} else if( ( (car == '-') || (car == '/') ) && (last != car) ) {
			chOk += '-';
		} else if( car == 'c') {
			lancerCalendar = true;
		} else if( car == ' ') {
			if(document.getElementById(obj.id))	{
				var d = new Date()
				var j = d.getDate();
				var m = d.getMonth() + 1
				if(j<10) j = "0"+j; 
				if(m<10) m = "0"+m; 
				chOk = ''+j+'-'+m+'-'+d.getFullYear();
				termine = true;
			}
		}
		last = car;
	}
	
	ch = chOk;
	
	if(obj.value != ch) {
		obj.value = ch;
	}
	
	if( lancerCalendar ){
		if(document.getElementById(obj.id))	showCalendar(obj.id);
	}
	
	return 1;
}

function validator_erreur_date(e,obj){
	
	if(!validator_verifie_date_focus || (validator_verifie_date_focus == obj) ){
		if(obj.type && obj.type!='hidden') obj.focus();
		validator_setClassError(obj);
		alert("La date est invalide.");
		validator_verifie_date_focus = obj;
	}
	return false;
	
}

function validator_erreur_format(e,obj){

	if(!validator_verifie_date_focus || (validator_verifie_date_focus == obj) ){
		alert("La date doit être de la forme :\njj-mm-aaaa\njj-mm-aa\njjmmaaaa\njjmmaa.\n\nAppuyez sur la touche 'c' pour afficher le calendrier.");
		validator_setClassError(obj);
		obj.focus();
		validator_verifie_date_focus = obj;
	}
	return false;
	
}

function validator_erreur_heure(e,obj){
	

	if(!validator_verifie_heure_focus || (validator_verifie_heure_focus == obj) ){
		obj.focus();
		validator_setClassError(obj);
		alert("L'heure est invalide.");
		validator_verifie_heure_focus = obj;
	}
	return false;
	
}

function validator_erreur_format_heure(e,obj){

	if(!validator_verifie_heure_focus || (validator_verifie_heure_focus == obj) ){
		alert("L'heure être de la forme :\nh:mm\nhh:mm");
		validator_setClassError(obj);
		obj.focus();
		validator_verifie_heure_focus = obj;
	}
	return false;
	
}
	
function validator_erreur_format_timesdate(e,obj){

	if(!validator_verifie_date_focus || (validator_verifie_date_focus == obj) ){
		alert("La date doit être de la forme : \nJJ-MM-AAAA hh:mm:ss");
		validator_setClassError(obj);
		obj.focus();
		validator_verifie_date_focus = obj;
	}
	return false;
}

function validator_verifie_date(e,obj){
	var d = obj.value;
	var nulOk = false;
	var dateLunaire = false;
	var bloqueFutur = false;
	var anneeDiff = 5;

	if(arguments.length > 2){ nulOk = arguments[2]; }
	if(arguments.length > 3){ dateLunaire = arguments[3]; }
	if(arguments.length > 4 && !arguments[4]){ anneeDiff = 0; }
	if(arguments.length > 5){ bloqueFutur = arguments[5]; }

	if (d == ""){
		if(nulOk) {
			validator_verifie_date_focus = null;
			return 1;
		}
		else return validator_erreur_format(e,obj);
	}

	var eclat = d.split("-");
	if( eclat.length == 1){
		if( (d.length == 6) || (d.length == 8) ){
			var dNew = String();
			dNew = d.slice(0,2);
			dNew += "-";
			dNew += d.slice(2,4);
			dNew += "-";
			dNew += d.slice(4);
			d = dNew;
		}
	}	  
	  
	e = new RegExp("^[0-9]{1,2}[-]{0,1}[0-9]{1,2}[-]{0,1}([0-9]{2}|[0-9]{4})$");
	
	if (!e.test(d))
	return validator_erreur_format(e,obj); 
	
	j = parseInt(d.split("-")[0], 10); // jour
	m = parseInt(d.split("-")[1], 10); // mois
	a = parseInt(d.split("-")[2], 10); // année
	
	if (a < 1000) {
		var now = new Date();
		if (a+2000 <= (now.getFullYear()+anneeDiff) )  a+=2000; // Si a les 5 prochaines années alors on ajoute 2000 sinon on ajoute 1900
		else a+=1900;
	}
	
	if(j<10) d = "0"+j; else d = j;
	d += "-";
	if(m<10) d += "0"+m; else d += m;
	d += "-";
	d += a;
	
	obj.value = d;
	 
	if (a%4 == 0 && a%100 !=0 || a%400 == 0) fev = 29;
	else fev = 28;
	
	nbJours = new Array(31,fev,31,30,31,30,31,31,30,31,30,31);

	if(bloqueFutur) {
		var now = new Date();
		var myDate = new Date();
		myDate.setFullYear(a,m-1,j);
		if(now < myDate) {
			if(getById(obj.name+'Label')) alert('Erreur : la '+getById(obj.name+'Label').innerHTML+' ne peut pas être dans le futur !')
			else alert('Erreur : la date ne peut pas être dans le futur !')
			obj.value='';
			obj.focus();
			return false;
		}
	}

	if ( dateLunaire || (m >= 1 && m <=12 && j >= 1 && j <= nbJours[m-1]) ){
		validator_verifie_date_focus = null;
		validator_resetClassError(obj);
		return true;
	}else{
		return validator_erreur_date(e,obj);
	}
}

function validator_verifie_timesdate(e,obj){
	var d = obj.value;
	var nulOk = false;

		
		if(arguments.length > 2) {nolOk = arguments[2]; }
		if (d == ""){
			if(nulOk){
				validator_erreur_date_focus = null;
				return 1;
			}
			else return validator_erreur_format_timesdate(e,obj);
		}
		
		var eclat = d.split(" "); // jj-mm-aaaa , hh:mm:ss
		var eclatDate = eclat[0].split("-"); //jj,mm,aaaa
		var eclatHeure = eclat[1].split(":"); //hh,mm,ss
	//	var l1 = eclat[0].length; //10
	//	var l2 = eclatDate.length; //3
		if( eclat[0].length ==1){
			if( (eclatDate.length == 6 || eclatDate.length == 8) ){
				var dNew = String();
				dNew = eclat[0].slice(0,2);
				dNew += "-";
				dNew += eclat[0].slice(2,4);
				dNew += "-";
				dNew += eclat[0].slice(4);
				eclat[0] = dNew;
			}
		}
		if( eclat[1].length == 1){
			if(eclatHeure.length == 6){
				var hNew = String();
				hNew = eclat[1].slice(0,2);
				hNew += ":";
				hNew += eclat[1].slice(2,4);
				hNew += ":";
				hNew += eclat[1].slice(4,6);
				eclat[1] = hNew;
			}
		}
		d=eclat[0]+" "+eclat[1];
		e = new RegExp("^[0-9]{2}-[0-9]{2}-([0-9]{4}) (([0-1][0-9])|([2][0-3])):[0-5][0-9]:[0-5][0-9]$");
		if (!e.test(d)){
			return validator_erreur_format_timesdate(e,obj);
		}		
		j = parseInt(eclat[0].split("-")[0],10); //jour
		m = parseInt(eclat[0].split("-")[1],10); //mois
		a = parseInt(eclat[0].split("-")[2],10); //annee
		he = parseInt(eclat[1].split(":")[0],10); //heure
		mi = parseInt(eclat[1].split(":")[1],10); //minute
		se = parseInt(eclat[1].split(":")[2],10); //seconde
		
		if (a<1000){
			if(a<79) a+=2000;
			else a+=1900;
		}
		
		if(j<10) d="0"+j; else d=j;
		d += "-";
		if(m<10) d+= "0"+m; else d += m;
		d += "-";
		d += a+" ";
		if(he<10) d+="0"+he; else d += he;
		d += ":";
		if(mi<10) d+="0"+mi; else d += mi;
		d += ":";
		if(se<10) d+="0"+se; else d += se;
		
		obj.value = d;
		
		if(a%4 == 0 && a%100 !=0 || a%400 == 0) fev = 29;
		else fev = 28;
		
		nbJours = new Array(31,fev,31,30,31,30,31,31,30,31,30,31);
		
		if( m>=1 && m<= 12 && j>=1 && j<=nbJours[m-1]){
			validator_verifie_date_focus = null;
			validator_resetClassError(obj);
			return true;
		}else{
			return validator_erreur_date(e,obj);
		}
	
}

function validator_verifie_heure(e,obj){
	var d = obj.value;
	var nulOk = false;
	var dNew = String();
	if(d.length == 3){ 
		dNew = d.slice(0,1);
		dNew += ":";
		dNew += d.slice(1,3);
		d = dNew;
	} 
	else if(d.length==4){
		dNew = d.slice(0,2);
		dNew += ":";
		dNew += d.slice(2,4);
		d = dNew;
	}
	  
	e = new RegExp("^([0-9]{1,2}):([0-9]{1,2})$");

	if (!e.test(d))
	return validator_erreur_format_heure(e,obj); 
	
	h = parseInt(d.split(":")[0], 10); // heure
	m = parseInt(d.split(":")[1], 10); // minute

	if(h<0 || h>24 || m<0 || m>60) return validator_erreur_format_heure(e,obj);
	if(h<10) d = "0"+h; else d = h;
	d += ":";
	if(m<10) d += "0"+m; else d += m;
	
	obj.value = d;
	
	if ( (m >= 0 && m <=59 && h >= 0 && h <= 23) ){
		validator_verifie_heure_focus = null;
		validator_resetClassError(obj);
		return true;
	}else{
		return validator_erreur_heure(e,obj);
	}
}
