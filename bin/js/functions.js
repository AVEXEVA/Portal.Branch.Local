function detectmob() {
   if(window.innerWidth < 1024 || window.innerHeight < 768) {
     return true;
   } else {
     return false;
   }
}
function toProperCase(s){
    return s.replace(/([^\s:\-])([^\s:\-]*)/g,function($0,$1,$2){
        return $1.toUpperCase()+$2.toLowerCase();
    });
}