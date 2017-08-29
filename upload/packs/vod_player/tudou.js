function $Showhtml(){
    document.getElementById('playad').style.display = "none";
    var tdurl = unescape(url);
	if(!isNaN(tdurl)){
		var player = '<embed type="application/x-shockwave-flash" src="http://www.tudou.com/v/'+unescape(url)+'/dW5pb25faWQ9MTAyMTk1XzEwMDAwMV8wMV8wMQ/&videoClickNavigate=false&withRecommendList=false&withFirstFrame=false&autoPlay=true/v.swf" id="Player" name="Player" bgcolor="#FFFFFF" quality="high" allowfullscreen="true" allowNetworking="internal" allowscriptaccess="never" wmode="transparent" menu="false" always="false"  pluginspage="http://www.macromedia.com/go/getflashplayer" width="100%" height="'+height+'" flashvars="">';
	}else{
		if(isWap){
			var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=tudou"></iframe>';
		}else{
			if(tdurl.indexOf("youku.com") > -1){
				var arr = tdurl.split("id_");
				var arr1 = arr[1].split(".");
				tdurl = arr1[0];
			} else if(tdurl.indexOf("tudou.com") > -1){
				var arr = tdurl.split("/v/");
				var arr1 = arr[1].split(".");
				tdurl = arr1[0];
			}
			var player = "<iframe height='"+height+"'' width='100%' src='http://player.youku.com/player.php/sid/"+tdurl+"/v.swf' frameborder=0 'allowfullscreen'></iframe>';";
		}
	}
    document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}

