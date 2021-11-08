function stylizeYADCF(){
	$("div.yadcf-filter-wrapper").addClass("input-group");
	$("select.yadcf-filter").addClass("form-control");
	$("input.yadcf-filter").addClass("form-control");
}
function detectmob() {
   if(window.innerWidth < 1024 || window.innerHeight < 768) {
     return true;
   } else {
     return false;
   }
}
function finishLoading(){
	$("div#page-wrapper.loading").remove();
    $("div#page-wrapper.content").show();
}
function finishLoadingPage(){
    finishLoading();
    if(typeof initialize == 'function'){initialize();}
    /*$("div.dataTables_length select").on('change',function(){
      var Select_Parent = $(this).parent().parent().attr('ID');
      var Table_ID = Select_Parent.substring(0,Select_Parent.lastIndexOf("_"));
      var href = Table_ID.substring(Select_Parent.indexOf("_") + 1).toLowerCase().substring(0,Table_ID.substring(Select_Parent.indexOf("_") + 1).length- 1);
      hrefRow(Table_ID,href);
    });*/
}
function hrefRow(table,obj){
	$("#" + table + " tbody tr").each(function(){
		$(this).on('click',function(){
			document.location.href=obj + ".php?ID=" + $(this).children(":first-child").html();
      //window.open(obj+ ".php?ID=" + $(this).children(":first-child").html(),"_blank");
		});
	});
}
function hrefRows(table,obj){hrefRow(table,obj);}
function toProperCase(s){
    return s.replace(/([^\s:\-])([^\s:\-]*)/g,function($0,$1,$2){
        return $1.toUpperCase()+$2.toLowerCase();
    });
}