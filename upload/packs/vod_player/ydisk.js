function $Showhtml(){
    document.getElementById('playad').style.display = "none";
    player = '<iframe id="ydisk" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'" width="100%" height="' + height + '" frameborder="0" scrolling="no"></iframe>';
    document.getElementById('playlist').innerHTML = player;
}

if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}
