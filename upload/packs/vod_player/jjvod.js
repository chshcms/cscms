function Showhtml(){
       document.getElementById('playad').style.display='none';
       if(window.ActiveXObject || window.ActiveXObject !== undefined)
            player=PlayerIe();
       else
            player=PlayerNt();

       if(player==''){
		parent.document.getElementById('cscms_vodplay').src='http://player.jjvod.com/js/jjplayer_install.html?v=1&c=cscms';
       }else{
            document.getElementById('playlist').innerHTML = player;
	        setInterval('vodstatus()','1000');
       }
}

function PlayerNt(){
       var player='';
	   if (navigator.plugins) {
                var Install = false;
				for (i=0; i < navigator.plugins.length; i++ ) 
				{
					if(navigator.plugins[i].name == 'JJvod Plugin'){	
						Install = true; break;
					}		
				} 

		        if(Install){
					var player = '<div style="width:100%;height:'+height+'px;overflow:hidden;position:relative"><object id="jjvodPlayer" name="jjvodPlayer" TYPE="application/x-itst-activex" ALIGN="baseline" BORDER="0" WIDTH="'+width+'" HEIGHT="'+height+'" progid="WEBPLAYER.WebPlayerCtrl.2" param_URL="'+unescape(url)+'" wmode="opaque" param_WEB_URL="'+unescape(parent.window.location.href)+'"></object></div>';
		        }
	    }
        return player;
}

function PlayerIe(){
    player  = "<div style='position:relative'>";
    player += "<object classid='clsid:C56A576C-CC4F-4414-8CB1-9AAC2F535837' width='100%' height='"+height+"' id='jjvodPlayer' name='jjvodPlayer' onerror='jjvoddown();'>";
    player += "<PARAM NAME='URL' VALUE='"+unescape(url)+"'>";
    player += "<PARAM NAME='WEB_URL' VALUE='"+unescape(parent.window.location.href)+"'>";
    player += "<param name='Autoplay' value='1'>";
    player += "<param name='wmode' value='opaque'>";
    player += "</object></div>";

    return player;
}

function vodstatus(){
    if(document.getElementById('jjvodPlayer').PlayState==12){//播放完成进入下一集
	if(xurl!=''){
		top.location.href=xurl;
	}
    }
}

function jjvoddown(){
    parent.document.getElementById('cscms_vodplay').src='http://player.jjvod.com/js/jjplayer_install.html?v=1&c=cscms';
}

if(parent.cs_adloadtime){
	setTimeout("Showhtml();",parent.cs_adloadtime*1000);
}
