// JavaScript Document
/********************************************
* 	Filename:	js/treeOperations.js
*	Author:		Ahmet Oguz Mermerkaya
*	E-mail:		ahmetmermerkaya@hotmail.com
*	Begin:		Sunday, April 20, 2008  16:28
***********************************************/



/**
 * Operations used in tree structure
 */
var simpleTree;
//var structureManagerURL = "update.php";
var dragOperation = true;
var operationFailed = -1;

function TreeOperations(baseUrl, callback)
{
	
	treeOps = this;
	this.ajaxActive = true;
	this.treeBusy = false;
	this.timer = 0;
	this.folderElement = false;
	this.lookAtFolderElement = false;

	
	this.inputText = "<input type='text' id='inputText' maxlength='30'>";
	this.inputId = '#inputText';
	
	this.baseUrl = baseUrl;
	this.callback = callback;
	
	//alert(this.baseUrl + this.callback);
//*******************************************************	
	for (var n in arguments[0]) 
	{ 
		this[n] = arguments[0][n]; 
	}
//*******************************************************
	this.isTreeBusy = function()
	{
		if (this.treeBusy == true){
			//alert(langManager.doOneOperationAtATime);
		}
		return (this.treeBusy);
	}
	this.setTreeBusy = function(busy)
	{
		this.treeBusy = busy;
	}
//**************************************************************
	this.showOperationInfo = function (text){
		
		$('#processing').html(text);		
		
		$('#processing').fadeIn(1, function(){			
			treeOps.timer = setTimeout("$('#processing').fadeOut(1000)", 1000);			
		});
		
	}
//********************************************************
	this.escapeChars = function(str) {	
		var ch = Array();	
		ch[0] = '.';
		ch[1] = '[';
		ch[2] = ']';
		ch[3] = '/';
		ch[4] = '@';
		ch[5] = ' ';
		var i = 0;
		for (i = 0; i < ch.length; i++) 
		{
			str = str.replace(ch[i], '\\' + ch[i]);	
		}		
		return str;
	}
//*******************************************************
	this.showInProcessInfo = function(show)
	{
		if (show == true) {
			clearTimeout(treeOps.timer);
			$('#processing').hide();
			//$('#processing').html(langManager.operationInProcess);
			$('#processing').show();
			
		}
		else {
			$('#processing').hide();
		}		
	}
//*******************************************************
	this.trGetSelected = function()
	{
		return simpleTree.get(0).getSelected();
	}


	this.trGetSelectedWithAlert = function()
	{
		var selectedNode = treeOps.trGetSelected();
		if (selectedNode.html() == null){
			//alert(langManager.selectNode2MakeOperation);
			return null;
		}
		return selectedNode;
	}
//**********************************************************
//         YENI ELEMAN EKLEME FONKSIYONLARI
//**********************************************************
	this.trAddElement = function(result)
	{
		//treeOps.treeBusy = false; ajax makes it false
		var info;
		if (typeof(result) == "undefined")
		{
			info.id = "null";
			info.name = "undefined";
		}
		else {
			info = eval("(" + result + ")");
			//alert(info.elementName);
		}	
		$('#inputText').parent().attr('id', info.elementId);
		$('#inputText').replaceWith("<span>"+info.elementName+"</span>");
		
		var elementId = treeOps.escapeChars(info.elementId);
		
		simpleTree.get(0).setTreeNodes($('#' + elementId).get(0));
		if (info.slave == 0) { // eger dosya doc degilse klas?r yapiliyor.
			simpleTree.get(0).convertToFolder($("#" + elementId));
		}
	}    
	/////////////////////////////////////////////////////////////
	this.addElementReq = function(folder)
	{    // Menu de yeni eleman ekle se?enegi tiklandiginda ilk bura ?agrilir 
		 // ve yeni bir yazi alani eklenir.
		
		if ( treeOps.isTreeBusy() == true ||  
			 treeOps.trGetSelectedWithAlert() == null
			) 
		{
			// aga?ta baska bir islem yapiliyorsa veya se?ili eleman 
			// yok ise islem yapilmasi engelleniyor
			return;
		}			
		dragOperation = false;		
		
		if (treeOps.trGetSelected().get(0).className.indexOf('close') >= 0)
		{
			var childUl = $('>ul', treeOps.trGetSelected().get(0));
			if (childUl.is('.ajax')) {
				simpleTree.get(0).nodeToggle(treeOps.trGetSelected().get(0), treeOps.addElementReq);
				treeOps.lookAtFolderElement = true;
				treeOps.folderElement = folder;				
				return;
			}
			else {				
				simpleTree.get(0).nodeToggle(treeOps.trGetSelected().get(0));
			}
		}
		
		
		treeOps.treeBusy = true;	
		var content = $.trim($('ul', treeOps.trGetSelected()).html());

		if (content == "") 
		{
			// Klas?r?n alti bosken altina yeni eleman eklenemiyordu bu y?zden asagidaki kodlar yazildi.
			// eger IE de fazladan g?z?ken dosyanin g?z?kmemesi i?in son iki silme (remove) satiri eklendi.
			$('ul', treeOps.trGetSelected()).html('<li class="line">&nbsp;</li><li class="doc-last"></li><li class="line-last"/>');
			
			simpleTree.get(0).addNode("newElement", "name", null);
			treeOps.trGetSelected().prev().remove();
			treeOps.trGetSelected().prev().remove();
		}
		else {
			simpleTree.get(0).addNode("newElement", "name", null);			
		}
		
		
		var slave = 1;
		
		if (treeOps.lookAtFolderElement == true){
			folder = treeOps.folderElement;
		}
		treeOps.lookAtFolderElement = false;
		
		if (folder == true) {
			simpleTree.get(0).convertToFolder(treeOps.trGetSelected());
			slave = 0;
		}
		
		treeOps.trGetSelected().html(treeOps.inputText);
		$('#inputText').focus();
		
		$('#inputText').bind("blur", function() {
    	    var name = $('#inputText').attr('value');										
    		var ownerEl = $('#inputText').parent().parent().parent().attr('id');
    		var params = encodeURI("op=insertElement"+"&name="+name+"&ownerEl="+ownerEl+"&slave="+slave);
    		treeOps.ajaxReq(params, treeOps.callback, treeOps.trAddElement);
    		dragOperation = true;
        });
		
		$('#inputText').bind("keypress", 
			 function(evt)
			 {												
				if (evt.keyCode == 13) // when pressed enter 
				{	
				    var name = $('#inputText').attr('value');										
					var ownerEl = $('#inputText').parent().parent().parent().attr('id');
					var params = encodeURI("op=insertElement"+"&name="+name+"&ownerEl="+ownerEl+"&slave="+slave);
					treeOps.ajaxReq(params, treeOps.callback, treeOps.trAddElement);
					dragOperation = true;
				}
				else if (evt.keyCode == 27) // when pressed esc 
				{	
				    
					treeOps.setTreeBusy(false);
					dragOperation = true;
					if ($('#inputText').parent().attr('class').indexOf('last')>=0) {
						var className = $('#inputText').parent().prev().prev().attr('class');
						$('#inputText').parent().prev().prev().attr('class',className+'-last');										
					}
					//$('#inputText').parent().prev().remove();
					$('#inputText').parent().remove();
				}
				//$('#inputText').unbind("keypress");
			}
        );		
	}
/*******************************************************
	ELEMAN SILME FONKSIYONLARI
********************************************************/
	this.trDeleteElement = function(result)
	{
		if (result != operationFailed)	{		
			simpleTree.get(0).delNode();				
		}
		else{
			alert("Error in operation");
		}
	}
	/////////////////////////////////////////////////////
	this.deleteElementReq = function()
	{	
		if ( treeOps.isTreeBusy() == true ||  
			 treeOps.trGetSelectedWithAlert() == null
			) 
		{
			// aga?ta baska bir islem yapiliyorsa veya se?ili eleman 
			// yok ise islem yapilmasi engelleniyor
			return;
		}	
	
		if (confirm(langManager.deleteConfirm))
		{
			treeOps.treeBusy = true;
			var params = "op=deleteElement&elementId="+treeOps.trGetSelected().attr('id');
			treeOps.ajaxReq(params, treeOps.callback, treeOps.trDeleteElement);
		}	
	}
/*******************************************************	
	ELEMANIN ISMINI DEGISTIRME FONKSIYONLARI
*******************************************************/
	this.trUpdateElementName = function(result)
	{
		var info = eval('('+result +')');
		var tmp_node = "<span>"+info.elementName+"</span>";
		$('#inputText').parent().attr('id', info.elementId);

		var elementId = treeOps.escapeChars(info.elementId);
		
		$('#inputText', '#'+ elementId).replaceWith(tmp_node);
		
		$('ul.ajax>li.doc-last', '#' + elementId).attr('id', info.elementId).html("{url:"+ treeOps.callback +"?op=getElementList&ownerEl="+ info.elementId +"}");
		simpleTree.get(0).setTreeNodes2($('#' + elementId));
	}

	this.updateElementNameReq = function()
	{
		if ( treeOps.isTreeBusy() == true ||  
			 treeOps.trGetSelectedWithAlert() == null
			) 
		{
			return;
		}			
		treeOps.treeBusy = true;
		
		var elementName = $('span.active').text();
		var elementId = treeOps.trGetSelected().attr('id');	
		
		$('span:first', treeOps.trGetSelected()).replaceWith("<input type='text' id='inputText' value='"+elementName+"'/>");	
		$('#inputText').focus();
		
		$('#inputText').bind("blur", function() {
    		var name = $('#inputText').attr('value'); 										
    	 	var params = "op=updateElementName&name="+name+"&elementId="+elementId;
    		treeOps.ajaxReq(params, treeOps.callback, treeOps.trUpdateElementName);										
        });
		
		$('#inputText').bind("keypress",
								 function(evt)
								 {
									 if (evt.keyCode == 13) { //pressed enter
										var name = $('#inputText').attr('value'); 										
									 	var params = "op=updateElementName&name="+name+"&elementId="+elementId;
									 	
										treeOps.ajaxReq(params, treeOps.callback, treeOps.trUpdateElementName);										
									 }
									 else if (evt.keyCode == 27) { // pressed esc
									 	treeOps.setTreeBusy(false);
									    $('#inputText').replaceWith("<span>"+elementName+"</span>");
									 	simpleTree.get(0).setTreeNodes2($('#'+elementId))
									 }
								 }
							);
		
	}
/**********************************
 * Expand All
***********************************/	 
	this.expandAll = function (obj)
	    {
			var folder = $('.folder-close, .folder-close-last', obj);				
			
			$(folder).each(function(){
				simpleTree.get(0).nodeToggle(this, treeOps.expandAll);
			});				
		}
/**********************************
 * Collapse All
***********************************/	
	this.collapseAll = function (){
			$('.folder-open, .folder-open-last').each(function(){
				simpleTree.get(0).nodeToggle(this);
			});
	}

//*******************************************************
	this.trReload = function()
	{
		//simpleTree.get(0).setAjaxNodes(getSelected(), null, null);
	}
//*******************************************************
	this.isInt = function(t) {
		try {
			//var t = eval(x);
			var y = parseInt(t);
			
			if (isNaN(y)) {
				return false;
			}
			return t == y && t.toString() == y.toString();
		}
		catch(ex){
			
		}
		return false;
 	} 
//*******************************************************	
	this.ajaxReq = function(params, url, callback, overrideSuccessFunc)
	{
		if (treeOps.ajaxActive == true)
		{
			var successFunction = function(result){	
						
							treeOps.treeBusy = false;
							treeOps.showInProcessInfo(false);
							
							try {
								var t = eval(result);
								// if result is less than 0, it means an error occured														
								if (treeOps.isInt(t) == true  && t < 0) { 
									//alert(eval("langManager.error_" + Math.abs(t)));									
								}	
								else{ // if result is greater than 0 it means operation is succesfull
									callback(result);
									//treeOps.showOperationInfo(langManager.missionCompleted);
								}
							}
							catch(ex) {	// if result is string it means operation is succesfull				
								callback(result);
								//treeOps.showOperationInfo(langManager.missionCompleted);								
							}
			};
			
			if (typeof overrideSuccessFunc == 'function') {
				successFunction = overrideSuccessFunc;	
			}
		 	$.ajax({ 
   					type: 'POST',
					url: url,
					data: params,
					dataType: 'script',
					timeout:100000,
					beforeSend: function(){ treeOps.showInProcessInfo(true);  },
					success: successFunction,
					failure: function(result) {								
							treeOps.treeBusy = false;
							treeOps.showInProcessInfo(false);
							if (result == operationFailed) {
								alert("Error in ajax.")
							}
					},
					error: function(par1, par2, par3){
						treeOps.showInProcessInfo(false);
						alert("Error in ajax..")
					}
			});
		}
		else {
			callback();
			treeOps.treeBusy = false;
		}
	}
}