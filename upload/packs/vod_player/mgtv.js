function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=mgtv"></iframe>';
	}else{
		var url = unescape(url);
		if(url.substr(0,7).toLowerCase()=="http://"){
			var arr = url.split("/");
			var arr1 = arr[5].split(".");
			url = arr1[0];
		}
		var player = '<embed type="application/x-shockwave-flash" src="http://player.hunantv.com/mgtv_v5_main/main.swf?play_type=1&video_id='+url+'" id="movie_player" name="movie_player" type="application/x-shockwave-flash" menu="false" wmode="transparent" allowFullScreen="true" allowScriptAccess="never" allowNetworking="internal" pluginspage="http://www.macromedia.com/go/getflashplayer" width="100%" height="'+height+'"></embed>';
    }
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}




