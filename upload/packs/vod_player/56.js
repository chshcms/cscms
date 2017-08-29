function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=56"></iframe>';
	}else{
		var player = '<embed src="http://share.vrs.sohu.com/my/v.swf&topBar=1&id='+unescape(url)+'&autoplay=true&from=page" type="application/x-shockwave-flash" width="100%" height="'+height+'" allowfullscreen="true" allownetworking="all" allowscriptaccess="always"></embed>';
    }
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}




