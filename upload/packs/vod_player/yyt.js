function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=yinyuetai"></iframe>';
	}else{
		var player = "<embed src=\"http://www.yinyuetai.com/video/player/"+unescape(url)+"/a_0.swf\" wmode=\"opaque\" quality=\"high\" width=\"100%\" height=\""+height+"\" align=\"middle\"  allowScriptAccess=\"sameDomain\" type=\"application/x-shockwave-flash\"></embed>";
    }
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}




