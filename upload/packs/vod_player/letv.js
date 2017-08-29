function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=letv"></iframe>';
	}else{
		var leurl = unescape(url);
		if(isNaN(leurl)){
			var arr = leurl.split("/vplay/");
			var arr1 = arr[1].split(".");
			leurl = arr1[0];
		}
		var player = '<embed allowfullscreen="true" wmode="transparent" quality="high" src="http://www.letv.com/player/x'+leurl+'.swf" quality="high" bgcolor="#000" width="100%" height="'+height+'" name="player" id="playerr" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>';
    }
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}





