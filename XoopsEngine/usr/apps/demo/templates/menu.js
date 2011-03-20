// JavaScript Document
/********************************************
*     Filename:    js/init.js
*    Author:        Ahmet Oguz Mermerkaya
*    E-mail:        ahmetmermerkaya@hotmail.com
*    Begin:        Sunday, April 20, 2008  16:22
***********************************************/


/**
 * initialization script 
 */
//var langManager = new languageManager();
//de for german
//en for english
//tr for turkish
//langManager.load("en");  

var treeOps = new TreeOperations(treeBaseUrl, treeCallback);

$(document).ready(function() {    
        
    // initialization of tree
    simpleTree = $('.simpleTree').simpleTree({
        //drag: false,
        baseUrl: treeBaseUrl,
        autoclose: false,
        animate: true,
        docToFolderConvert: true,
        
		/**
		 * Callback function is called when one item is double-clicked
		 */	
		afterDblClick:function(node){
    		treeOps.updateElementNameReq();
		},
		afterMove:function(destination, source, pos) {
			if (dragOperation == true) 
			{
				var params = "action=changeOrder&elementId="+source.attr('id')+"&destOwnerEl="+destination.attr('id')+"&position="+pos;
				treeOps.ajaxReq(params, treeOps.callback, null, function(result)
				{						
					treeOps.treeBusy = false;
					treeOps.showInProcessInfo(false);
					try {
						var t = eval(result);
						// if result is less than 0, it means an error occured														
						if (treeOps.isInt(t) == true  && t < 0) { 
							alert(eval("langManager.error_" + Math.abs(t)) + "\n"+langManager.willReload);									
							window.location.reload();							
						}
						else {
							var info = eval("(" + result + ")");
							$('#' + info.oldElementId).attr("id", info.elementId);
						}
					}
					catch(ex) {	
							var info = eval("(" + result + ")");
							$('#' + info.oldElementId).attr("id", info.elementId);	
					}	
				});
			}
		}, 
		 
		afterContextMenu: function(element, event)
		{
			$(document).unbind('mousemove',this.dragStart).unbind('mouseup').unbind('mousedown');
			
		},
		      
    });
    
    // Show menu when a list item is clicked
    $("#myList UL LI SPAN").contextMenu({
        menu: 'myMenu'
    }, function(action, el, pos) {
        alert(
            'Action: ' + action + '\n\n' +
            'Element text: ' + $(el).text() + '\n\n' + 
            'X: ' + pos.x + '  Y: ' + pos.y + ' (relative to element)\n\n' + 
            'X: ' + pos.docX + '  Y: ' + pos.docY+ ' (relative to document)'
            );
    });    
});