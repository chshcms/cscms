function Showhtml(){
       document.getElementById('playad').style.display='none';
       if(window.ActiveXObject || window.ActiveXObject !== undefined)
            player=PlayerIe();
       else
            player=PlayerNt();
       if(player==''){
		parent.document.getElementById('cscms_vodplay').src=parent.cs_root+'packs/vod_player/ffhd.html';
       }else{
                document.getElementById('playlist').innerHTML = player;
       }
}

function PlayerNt(){
       var player='';
       if (navigator.plugins) {
                var Install = false;
		for (i=0; i < navigator.plugins.length; i++ ){
			if(navigator.plugins[i].name == 'FFPlayer Plug-In'){	
				Install = true; break;
			}		
		} 
		if(Install){
			var player = '<div style="width:100%; height:'+height+'px;overflow:hidden;position:relative"><object id="FFHDPlayer" name="FFHDPlayer" type="application/npFFPlayer" width="100%" height="'+height+'" progid="XLIB.FFPlayer.1" url="'+unescape(url)+'" CurWebPage=""></object></div>';
		}
	}
        return player;
}

function PlayerIe(){
    player  = "<div style='position:relative'>";
    player += "<object classid='clsid:D154C77B-73C3-4096-ABC4-4AFAE87AB206' width='100%' height='"+height+"' id='FFHDPlayer' name='FFHDPlayer' onerror='ffhddown();'>";
    player += "<param name='url' value='"+unescape(url)+"'/>";
    player += "<param name='CurWebPage' value=''/>";
    player += "<param name='wmode' value='opaque'>";
    player += "</object></div>";
    return player;
}

function ffhddown(){
    parent.document.getElementById('cscms_vodplay').src=parent.cs_root+'packs/vod_player/ffhd.html';
}

if(parent.cs_adloadtime){
    setTimeout("Showhtml();",parent.cs_adloadtime*1000);
}