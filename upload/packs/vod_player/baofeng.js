function $Showhtml(){
    document.getElementById('playad').style.display = "none";
    player = '<embed allowfullscreen="true" wmode="transparent" quality="high" src="http://static.hd.baofeng.com/swf/player/o.swf?storm='+unescape(url)+'" quality="high" bgcolor="#000" width="100%" height="'+height+'" name="player" id="playerr" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>';
    document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}


