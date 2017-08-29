
var BdPlayer = new Array();
function $Showhtml(){


    document.getElementById('playad').style.display = "none";

BdPlayer['time'] = 8;//缓冲广告展示时间(如果设为0,则根据缓冲进度自动控制广告展示时间)

BdPlayer['buffer'] = 'http://player.baidu.com/lib/show.html?buffer';//贴片广告网页地址

BdPlayer['pause'] = 'http://player.baidu.com/lib/show.html?pause';//暂停广告网页地址

BdPlayer['end'] = 'http://player.baidu.com/lib/show.html?end';//影片播放完成后加载的广告

BdPlayer['tn'] = '12345678';//播放器下载地址渠道号

BdPlayer['width'] = '+width+';//播放器宽度(只能为数字)

BdPlayer['height'] = '+height+';//播放器高度(只能为数字)

BdPlayer['showclient'] = 1;//是否显示拉起拖盘按钮(1为显示 0为隐藏)

BdPlayer['url'] = ''+url+'';//当前播放任务播放地址

BdPlayer['nextcacheurl'] = '';//下一集播放地址(没有请留空)

BdPlayer['lastwebpage'] = ''+surl+'';//上一集网页地址(没有请留空)

BdPlayer['nextwebpage'] = ''+xurl+'';//下一集网页地址(没有请留空)

document.write('<script language="javascript" src="http://php.player.baidu.com/bdplayer/player.js" charset="utf-8"></scr'+'ipt>');

    //document.getElementById('playlist').innerHTML = player;
}

if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}
	
