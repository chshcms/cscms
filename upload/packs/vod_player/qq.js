function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=qq"></iframe>';
	}else{
		var url = unescape(url);
		if(url.substr(0,7).toLowerCase()=="http://"){
			var arr = url.split("/");
			var arr1 = arr[7].split(".");
			url = arr1[0];
		}
		var player = '<iframe frameborder="0" width="100%" height="'+height+'" src="https://v.qq.com/iframe/player.html?vid='+url+'&tiny=0&auto=0" allowfullscreen></iframe>';
    }
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}




