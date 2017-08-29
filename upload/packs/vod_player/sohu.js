function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=sohu"></iframe>';
	}else{
		var player = '<embed type="application/x-shockwave-flash" src="http://tv.sohu.com/upload/swf/20120621/PlayerShell.swf" id="Player" bgcolor="#FFFFFF" quality="high" allowfullscreen="true" allowNetworking="internal" allowscriptaccess="never" wmode="transparent" menu="false" always="false"  pluginspage="http://www.macromedia.com/go/getflashplayer" width="100%" height="'+height+'" flashvars="&type=Singleton&domain=inner&skin=4&isAutoPlay=true&id='+unescape(url)+'">';
	}
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}



