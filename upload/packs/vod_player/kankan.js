function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=kankan"></iframe>';
	}else{
		var player = '<embed allowfullscreen="true" wmode="transparent" quality="high" src="http://video.kankan.com/dt/swf/v_sina.swf?id='+unescape(url)+'&sid=406356&vtype=1&mtype=teleplay" quality="high" bgcolor="#000" width="100%" height="'+height+'" name="player" id="playerr" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>';
    }
	document.getElementById('playlist').innerHTML = player;
}

if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}




