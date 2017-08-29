function $Showhtml(){
    document.getElementById('playad').style.display = "none";
	if(isWap){
		var player = '<iframe width="100%" height="'+height+'" allowTransparency="true" frameborder="0" scrolling="no" src="http://jx.api.163ren.com/vod.php?url='+unescape(url)+'&type=sina"></iframe>';
	}else{
		var player = '<embed allowfullscreen="true" src="http://p.you.video.sina.com.cn/swf/bokePlayer20130726_V4_1_42_23.swf?vid='+unescape(url)+'&clip_id=&imgurl=&auto=1&vblog=1&type=0&tabad=1&autoLoad=1&autoPlay=1&as=0&tjAD=0&tj=0&casualPlay=1&head=0&logo=0&share=0" quality="high" bgcolor="#000" width="100%" height="'+height+'" name="player" id="playerr" align="middle" allowscriptaccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>';
	}
	document.getElementById('playlist').innerHTML = player;
}
if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}




