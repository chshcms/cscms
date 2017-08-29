function $Showhtml(){
    document.getElementById('playad').style.display = "none";
    player = '<embed type="application/x-shockwave-flash" src="http://res.maxtv.cn/player/vod/vodplayer.swf" width="100%" height="'+height+'" style="undefined" id="dopvodflashplayer" name="dopvodflashplayer" bgcolor="#000000" quality="high" allowfullscreen="true" scale="showall" allowscriptaccess="always" flashvars="autostart=true&repeat=list&aboutlink=http://www.maxtv.cn&clienttitle=MaxTV迈视加速器&file=http://xml.vod.maxtv.cn/'+unescape(url)+'.xml&tags=movie">';
    document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}


