var jpurl=unescape(url);
document.write('<link href="'+parent.cs_root+'packs/vod_player/jplayer/skin/blue/css.css" rel="stylesheet" type="text/css" />');
document.write('<script type="text/javascript" src="'+parent.cs_root+'packs/vod_player/jplayer/js/jquery.js"></script>');
document.write('<script type="text/javascript" src="'+parent.cs_root+'packs/vod_player/jplayer/js/jquery.jplayer.min.js"></script>');

function $Showhtml(){
    document.getElementById('playad').style.display = "none";
    var playhtml='<div class="oldPlayer"><div id="cscms_jplayer"class="jp-jplayer"></div><div id="jp_container_1"class="jp-audio"><div class="jp-type-single"><div class="jp-interface clearfix"><div class="playerMain-01"><p><span id="PlayStateTxt">正在播放:</span><span id="play_musicname">'+urlname+'</span></p><div class="jp-time-holder"><div class="jp-current-time">00:00</div>/<div class="jp-duration">00:00</div></div></div><div class="playerMain-02"><div class="jp-progress"><div class="jp-seek-bar"><div class="jp-play-bar"></div></div></div></div><div class="playerMain-03"><div class="fl"><ul class="jp-controls"><li><a href="'+surl+'"class="jp-previous"tabindex="1">上一首</a></li><li><a href="javascript:{};"class="jp-play"tabindex="1">播放</a></li><li><a style="display:none;"href="javascript:{};"class="jp-pause"tabindex="1">暂停</a></li><li><a href="'+xurl+'"class="jp-next"tabindex="1">下一首</a></li></ul></div>';
    playhtml+='<div class="fr"><ul class="ku-volume"><li><a href="javascript:{};"class="jp-mute"tabindex="1"title="静音">静音</a></li><li><a href="javascript:{};"class="jp-unmute"style="display:none;"tabindex="1"title="取消静音">取消静音</a></li><li class="volume-bar-wrap"><div class="jp-volume-bar"><div class="jp-volume-bar-value"></div></div></li><li><a href="javascript:{};"class="jp-volume-max"tabindex="1"title="最大音量">最大音量</a></li></ul></div></div></div><div class="jp-no-solution"><span>播放出现故障,您需要更新！</span>对不起，您需要更新您的浏览器到最新版本或更新您的flash播放器版本！<br/><a href="http://get.adobe.com/flashplayer/"target="_blank">点击下载Flash plugin>></a></div></div></div></div></div>';
    document.getElementById('playlist').innerHTML = playhtml;
    $("#cscms_jplayer").jPlayer({
        supplied: "mp3,m4a",
        swfPath: parent.cs_root+"packs/vod_player/jplayer/js",
        wmode: "window",
        ready:function (event){ getjp_play(); },
        ended: function () { getjp_next(); }
    });
}

if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}


function getjp_next() {
        if( xurl != "") {
              parent.window.location =  xurl;
        }else{
              getjp_play();
        }
}
function getjp_play() {
	if(jpurl.indexOf(".m4a")>0){
		$("#cscms_jplayer").jPlayer("setMedia", {m4a:jpurl}).jPlayer("play");
	}else{
		$("#cscms_jplayer").jPlayer("setMedia", {mp3:jpurl}).jPlayer("play");
	}
}

