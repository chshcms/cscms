function $Showhtml(){
    document.getElementById('playad').style.display = "none";
    player = '<embed pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" src="http://y0.ifengimg.com/swf/ifengFreePlayer_v5.0.70.swf" height="'+height+'" width="100%" id="js_playVideo" flashvars="guid='+unescape(url)+'&from=free&AutoPlay=true&adTag=ifeng&ADPlay=false&uid=1437714420795_209sho8809&sid=&locid=&startTime=0&parent=0&adType=5&preAdType=0&PlayerName=vFreePlayer" allowfullscreen="true" wmode="direct" allowscriptaccess="always">';
    document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}


