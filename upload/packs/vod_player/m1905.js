function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=m1905"></iframe>';
	}else{
		var url = unescape(url);
		if(isNaN(url)){
			var arr = url.split("/play/");
			var arr1 = arr[1].split(".");
			url = arr1[0];
		}
		var player = '<embed type="application/x-shockwave-flash" src="http://static.m1905.com/v1/playerv1/1905Player.swf" width="100%" height="'+height+'" style="" id="__M1905FlashPlayer__" name="__M1905FlashPlayer__" bgcolor="#00000" quality="high" allowscriptaccess="always" allownetworking="all" allowfullscreen="true" wmode="Opaque" flashvars="configUrl=http://www.1905.com/api/vip/play_0221-p-exp-1-rnd-490224697-id-'+unescape(url)+'.html&amp;LoGo=false&amp;wide=false&amp;hd=true&amp;light=true&amp;playAd=false&amp;autoPlay=true&amp;cdn=false">';
    }
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}




