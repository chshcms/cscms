function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=fun"></iframe>';
	}else{
		var url = unescape(url);
		if(url.substr(0,7).toLowerCase()=="http://"){
			var arr = url.split("v-");
			var arr1 = arr[1].split("/");
			url = arr1[0];
		}
		var player = '<embed allowscriptaccess="always" wmode="transparent" ver="10.2" allowfullscreen="true" width="100%" height="'+height+'" align="middle" flashvars="type=movie&amp;videoid='+url+'&amp;next=1&amp;startAd=1&amp;stoppage=1&amp;funshionSetup=0&amp;partner=0&amp;userMac=&amp;vmis=0&amp;gtype=1&amp;channelid=&amp;galleryid=313453&amp;mediaAp=c_wb&amp;pauseAp=c_wps&amp;h5=1&amp;vjjkey=V1b7myrXW&amp;vjjmedia=1&amp;showStop=1&amp;historytime=0&amp;nextinfo=12007385&amp;vodnum=video-player-1500953606366_0&amp;time=8&amp;uuid=1AB48F16-489D-BE28-08F2-C337F96E65F3" src="http://static.funshion.com/market/p2p/openplatform/master/2017-7-21/FunVodPlayer.swf" name="video-player-1500953606366_0" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">';
    }
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}




