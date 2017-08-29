function $Showhtml(){
        document.getElementById('playad').style.display = "none";
	player = '<object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="100%" height="'+height+'" id="mdediaplayer">';
	player += '<param name="URL" value="'+unescape(url)+'">';
	player += '<param name="stretchToFit" value="-1">';
	player += '<embed filename="'+unescape(url)+'" ShowStatusBar="1" type="application/x-mplayer2" width="100%" height="'+height+'">';
	player += '</object>';
	player += '<div align="right" style="margin-right:69px;margin-top:-30px"><input type="submit" value="全屏观看" onclick="setfullscreen()"></div>';	
        document.getElementById('playlist').innerHTML = player;
}
function setfullscreen(){
     if(mdediaplayer.playstate==3){
     	mdediaplayer.fullScreen=true;
	 }
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}


