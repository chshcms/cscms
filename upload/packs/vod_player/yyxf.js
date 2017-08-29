var playerw = "100%";
var playerh = height;
var newuri = '';
var xfuri = unescape(url);
var Player = {
    'Url': newuri,
    'Play': function () {
    }
}
Player.Play();

function $Showhtml() {
	   document.getElementById('playad').style.display = "none";
       Player.Url = xfuri;
       if(window.ActiveXObject || window.ActiveXObject !== undefined)
            var playhtml= $PlayerIe(xfuri);
       else
            var playhtml= $PlayerNt(xfuri);
	   document.getElementById('playlist').innerHTML = playhtml;
}

function $PlayerNt(xfuri){
	if (navigator.plugins) {
            var Install = false;
				for (i=0; i < navigator.plugins.length; i++ ) 
				{
					var n = navigator.plugins[i].name;
					if( navigator.plugins[n][0]['type'] == 'application/xfplay-plugin')
					{
						Install = true; break;
					}		
				} 

		if(Install){
		    return '<div style="text-align:center;"><table><embed type="application/xfplay-plugin" PARAM_URL="' + xfuri + '" PARAM_Status="1" width="' + playerw + '" height="' + playerh + '" id="Xfplay" name="Xfplay"></embed></table></div>';
		}
	}
	return $xfInstall(xfuri);
}

function $PlayerIe(xfuri){
    document.write('<div style="text-align:center;"><table><IFRAME id=xframe_mz name=xframe_mz style="MARGIN: 0px; DISPLAY: none" src="http://error.xfplay.com/error.htm" frameBorder=0 scrolling=no width="' + playerw + '" height="' + playerh + '"></IFRAME>');
         var player = '<object ID="Xfplay" name="Xfplay" width="'+playerw+'" height="'+playerh+'" onerror="$xf_IE_Install();" classid="clsid:E38F2429-07FE-464A-9DF6-C14EF88117DD" >';
         player += '<PARAM name="URL" value="'+xfuri+'">';
         player += '<PARAM name="Status" value="1"></object></table></div>';
         return player;
}

function $xfInstall(xfuri) {
    var ua = navigator.userAgent.toLowerCase();
    var s;
    if ((s = ua.match(/chrome\/([\d.]+)/))) {
        if (s[1].split('.')[0] >= 42)
            this.location.href = xfuri;
    }
    return '<div style="text-align:center;"><table><iframe border="0" src="http://error.xfplay.com/error.htm' + '" marginWidth="0" frameSpacing="0" marginHeight="0" frameBorder="0" noResize scrolling="no" width="' + playerw + '" height="' + playerh + '" vspale="0"></iframe></table></div>';
}

function $xf_IE_Install(){
  document.getElementById('Xfplay').style.display='none';document.getElementById('xframe_mz').style.display='';document.getElementById('xframe_mz').src='http://error.xfplay.com/error.htm';  
}

if(parent.cs_adloadtime){
	setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
}