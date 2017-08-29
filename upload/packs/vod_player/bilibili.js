function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=bilibili"></iframe>';
	}else{
		var url = unescape(url);
		if(url.substr(0,7).toLowerCase()=="http://"){
			var arr = url.split("av");
			var arr1 = arr[1].split("/");
			url = arr1[0];
		}
		var player = '<embed height="'+height+'" width="100%" quality="high" allowfullscreen="true" type="application/x-shockwave-flash" src="//static.hdslb.com/miniloader.swf" flashvars="aid='+url+'&page=1" pluginspage="//www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash"></embed>';
    }
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}




