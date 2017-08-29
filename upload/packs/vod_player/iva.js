function $Showhtml(){
    document.getElementById('playad').style.display = "none";
    player = '<iframe border="0" src="iva.html" width="100%" height="'+height+'" marginWidth="0" frameSpacing="0" marginHeight="0" frameBorder="0" scrolling="no" vspale="0" allowfullscreen></iframe>';
    document.getElementById('playlist').innerHTML = player;
}
setTimeout("$Showhtml();",parent.cs_adloadtime*1000);
