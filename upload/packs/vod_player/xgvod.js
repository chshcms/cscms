function isIE() {
    if (!!window.ActiveXObject || "ActiveXObject" in window)  {
		browser = "Microsoft Internet Explorer";
        return true;  
    }
    return false;  
}  
function Showhtml(){
	   browser = navigator.appName;
	   if(browser == "Netscape"|| browser == "Opera"){
		   if(/iPad|iPhone/i.test(navigator.userAgent)){
			    setTimeout(PlayerIOS,1000);
				return false;  
		   } else if(/Android/i.test(navigator.userAgent)){
			    PlayerAndroid();
				return false;  
		   } else {
		        if(isIE()){
		             player=PlayerIe();
		        }else{
			         player=PlayerNt();
			    }
		   }
	   }else if(browser == "Microsoft Internet Explorer"){
		   player=PlayerIe();
	   }

       if(player==''){
	      parent.document.getElementById('cscms_vodplay').src='http://static.xigua.com/installpage.html?cscms';
       }else{
            document.getElementById('playlist').innerHTML = player;
	        setInterval('vodstatus()','1000');
       }
}

function PlayerNt(){
       var player='';
	   if (navigator.plugins) {
		var install = true;
		    for (var i=0;i < navigator.plugins.length;i++) {
			    if(navigator.plugins[i].name == 'XiGua Yingshi Plugin'){
				    install = false;break;
			    }
		    }
		    if(!install){
				    var player = '<div style="width:100%; height:'+parent.cs_height+'px;overflow:hidden;position:relative"><object  width="100%" height="'+height+'" type="application/xgyingshi-activex" progid="xgax.player.1" param_URL="'+unescape(url)+'" param_NextCacheUrl="'+unescape(xpurl)+'" param_LastWebPage="'+surl+'" param_NextWebPage="'+xurl+'" param_Autoplay="1" id="xiguaPlayer" wmode="opaque" name="xiguaPlayer"></object></div>';
		        }
	    }
        return player;
}

function PlayerIe(){
    player  = "<div style='position:relative'>";
    player += "<object classid='clsid:BEF1C903-057D-435E-8223-8EC337C7D3D0' width='100%' height='"+height+"' id='xiguaPlayer' name='xiguaPlayer' onerror='xgvoddown();'>";
    player += "<param name='URL' value='"+unescape(url)+"'/>";
    player += "<param name='NextCacheUrl' value='"+unescape(xpurl)+"'>";
    player += "<param name='LastWebPage' value='"+surl+"'>";
    player += "<param name='NextWebPage' value='"+xurl+"'>";
    player += "<param name='Autoplay' value='1'/>";
    player += "</object></div>";
    return player;
}

function PlayerIOS(){
	installapp();
        url=unescape(url);
        url=url.replace('ftp://','xg://');
	location.href = url+"|http://'+window.location.host + parent.cs_root+'packs/vod_player/loading.html|"+xurl;
}

function PlayerAndroid(){
        url=unescape(url);
        url=url.replace('ftp://','xg://');
	var xuanjipage = xurl;
	var finalurl = url;
	
	// 通过iframe的方式试图打开APP，如果能正常打开，会直接切换到APP，并自动阻止a标签的默认行为
	// 否则打开a标签的href链接
	
    var timeout, t = 1000, hasApp = true;  
    setTimeout(function () {  
        if (hasApp) {  
            location.href=finalurl;
        } else {  
            top.location.href="http://static.xigua.com/xigua_v2.apk";
        }  
        document.body.removeChild(ifr);  
    }, 2000)  
  
    var t1 = Date.now();  
    var ifr = document.createElement("iframe");  
    ifr.setAttribute('src', finalurl);  
    ifr.setAttribute('style', 'display:none');  
    document.body.appendChild(ifr);  
    timeout = setTimeout(function () {  
         var t2 = Date.now();  
         if (!t1 || t2 - t1 < t + 100) {  
             hasApp = false;  
         }  
    }, t);  
	
}

function xgvoddown(){
	parent.document.getElementById('cscms_vodplay').src='http://static.xigua.com/installpage.html?cscms';
}

function vodstatus(){
      if(document.getElementById("playad")){
          document.getElementById('playad').style.display='none';
      }
}

//安装手机版本
function installapp(){  
		return function(){  
			var clickedAt = +new Date;  	
			setTimeout(function()
			{  
				try{if(isxg()){return;}}catch(e){;}
				  if (+new Date - clickedAt < 1500)
				  {
				    alert("即将为你转到苹果商店下载\"瓜瓜播放器\"，安装成功后，重新刷新本页面进行播放");
					setTimeout(function(){
						var surl="itms-services://?action=download-manifest&url=https://install.xiguaplayer.com/xigua.plist";
						top.location.href=surl;
					},3000);
				  } 
			}, 500);
		};  
}  

if(parent.cs_adloadtime){
	setTimeout("Showhtml();",parent.cs_adloadtime*1000);
}