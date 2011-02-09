function rmBlock(o)
{
	$(o).parent().remove();
}
(function($){
	$.fn.startDrag 	= function(){
		$("#sortable1,#sortable2,#sortable3,#sortable4,#sortable5,#sortable6,#sortable7,#sortable8").sortable({
			revert: true,
			stop: blkstop
		});
		
		$("#"+$(this).attr("id")+" li").draggable({
			connectToSortable: '#sortable1,#sortable2,#sortable3,#sortable4,#sortable5,#sortable6,#sortable7,#sortable8',
			helper: 'clone',
			revert: 'invalid',
			stop: 	setBlock,
			start:  getBlock,
			snap: true
		})

		function getBlock()
		{
			var v = $("#xo-page-main li[data="+$(this).attr("data")+"]").parent().attr("data");
			if ( $("#xo-page-main ul[data="+v+"]" + " li[data="+$(this).attr("data")+"]").length > 0 ) {
				return false;
			}
		}
		
		function setBlock()
		{
			var v = $("#xo-page-main li[data="+$(this).attr("data")+"]").parent().attr("data");
			if ( $("#xo-page-main ul[data="+v+"]" + " li[data="+$(this).attr("data")+"]").length == 1 ) {
				$("#xo-page-main ul[data="+v+"]" + " li[data="+$(this).attr("data")+"]").prepend("<input type=\"hidden\" size=\"4\" name=\"position["+v+"][]\" value=\""+$(this).attr("data")+"\" />");
			}
		}
		
		function blkstop()
		{
			var n = $(this).attr("data")*1+1;
			var child = $("#sortable"+n).children("li");
		    for(i=0;i<child.size();i++){
		    	child.eq(i).children("em").html(" ("+(i+1)+") <input type=\"hidden\" size=\"4\" name=\"orders["+$(this).attr("data")+"]["+child.eq(i).attr("data")+"]\" value=\""+i+"\" /> ");
		    }
		}
		
	};
	$.fn.selectModule = function(options){
		var settings = $.extend({
			url: "",
			container: ""
        }, options || {});
		
		$(this).bind("change",loadBlock);
		
		function loadBlock() {
			$.get(settings.url,{dirname:$(this).val()},function(html){
				$(settings.container).html(html);
				$(settings.container).startDrag();
			});
		}
	};
	
	$.fn.selectBlock = function(options){
		var settings = $.extend({
			url: "",
			container: "#select-page"
        }, options || {});
		$(this).bind("change",loadPage);
		
		function loadPage() {
			$.get(settings.url,{dirname:$(this).val()},function(html){
				$(settings.container).html(html);
			});
		}
	};
})(jQuery);