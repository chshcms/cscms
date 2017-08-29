function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=pptv"></iframe>';
	}else{
		var url = unescape(url);
		if(url.substr(0,7).toLowerCase()=="http://"){
			var arr = url.split("/show/");
			var arr1 = arr[1].split(".");
			url = arr1[0];
		}
		var player = '<embed type="application/x-shockwave-flash" src="http://player.pptv.com/v/'+url+'.swf" id="Player" bgcolor="#FFFFFF" quality="high" allowfullscreen="true" allowNetworking="internal" allowscriptaccess="never" wmode="transparent" menu="false" always="false"  pluginspage="http://www.macromedia.com/go/getflashplayer" width="100%" height="'+height+'" flashvars="">';
    }
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}



