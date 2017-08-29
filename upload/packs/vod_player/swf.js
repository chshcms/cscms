function $Showhtml(){
    document.getElementById('playad').style.display = "none";
    player = '<embed src="'+unescape(url)+'" allowFullScreen="true" quality="high" width="100%" height="'+height+'" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash"></embed>';
    document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}


