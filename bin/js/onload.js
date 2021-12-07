function mobileMenu(){
	if(detectmob() || true){
		setTimeout(function(){
			$("div.navbar-header button").click();
			setTimeout(function(){
				$("div.navbar-header button").click();
			},500);
		},250);
	}

}
$(document).ready(function(){
	//mobileMenu();
});
