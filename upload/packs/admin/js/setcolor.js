<!-- 
//chengshi 04/05/2013
var c_current=null,elements=null;
function initcolor(){
	var ColorHex=new Array('00','33','66','99','CC','FF') 
	var SpColorHex=new Array('FF0000','00FF00','0000FF','FFFF00','00FFFF','FF00FF') 
	var colorpanel=document.getElementById("colorpanel");
	if(colorpanel){document.body.removeChild(colorpanel);}
	var colorTable='';//alert(bobj);
	elements=new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string') element = document.getElementById(element);
		if (element) {} else {
			element = null;
		}
		elements.push(element);
	}
	for (var i=0;i<2;i++){ 
		for (var j=0;j<6;j++){ 
			colorTable=colorTable+'<tr height=12>' 
			colorTable=colorTable+'<td width=11 style="background-color:#000000">' 
			if (i==0){ 
				colorTable=colorTable+'<td width=11 style="border: 1px solid rgb(0, 0, 0);background-color:#'+ColorHex[j]+ColorHex[j]+ColorHex[j]+'">'
			}else{ 
				colorTable=colorTable+'<td width=11 style="border: 1px solid rgb(0, 0, 0);background-color:#'+SpColorHex[j]+'">'
			} 
			colorTable=colorTable+'<td width=11 style="border: 1px solid rgb(0, 0, 0);background-color:#000000">' 
			for (var k=0;k<3;k++){ 
				for (var l=0;l<6;l++){ 
				colorTable=colorTable+'<td width=11 style="border: 1px solid rgb(0, 0, 0);background-color:#'+ColorHex[k+i*3]+ColorHex[l]+ColorHex[j]+'">' 
				} 
			} 
		} 
	} 
	colorTable='<table border="0" cellspacing="0" cellpadding="0" style="border:1px #000000 solid;border-bottom:none;border-collapse: collapse;width:253px;" bordercolor="#000000">' 
	+'<tr><td width="70" height=20 bgcolor=#ffffff>CMS调色器<td>'
	+'<td bgcolor=#ffffff><input type="text" name="DisColor" id="DisColor" size="6" disabled style="border:solid 0px #000000;background-color: #FFFFFF;"></td>'
	+'<td bgcolor=#ffffff><input type="text" name="HexColor" id="HexColor" size="7" style="border:1px #FFFFFF;font-family:Arial;"></td>'
	+'<td bgcolor=#ffffff style="font:12px tahoma;padding-left:2px;">' 
	+'<span style="float:left;color:#999999;cursor:pointer;" onclick="colorremove()">清除</span>' 
	+'<span style="float:right;padding-right:5px;cursor:pointer;" onclick="colorclose()" title="关闭">×</span>' 
	+'</td></tr></table>' 
	+'<table border="1" cellspacing="0" cellpadding="0" style="border-collapse: collapse" bordercolor="#000000"'
	+' onmouseover="doOver(event)" onmouseout="doOut(event)" onclick="doclick(event)">' 
	+colorTable+'</table><iframe style="display:none;_display:block;position:absolute;top:0;left:0px;z-index:-1; filter:mask(); width:260px;height:172px;"></iframe>'; 
	var current = __ABS(elements[0]);
        var o = document.createElement("div");
	o.id  = "colorpanel";
        o.style.display  = "block";
        o.style.position = "absolute";
        o.style.zindex   = "999";
	o.style.left = current.x + "px"; 
	o.style.top = (current.y+22) + "px"; 
        o.innerHTML = colorTable;
        document.body.appendChild(o);
	document.onmouseup = function(e){ 	
		e=window.event?window.event:e;
  		var srcE=e.srcElement?e.srcElement:e.target;
    		if (o && srcE.id != "colorpanel"){ 
         		o.style.display = "none";
    		} 
	}
} 

function doOver(evt) {
	var e=window.event?window.event:evt;
  	var srcE=e.srcElement?e.srcElement:e.target;
	if ((srcE.tagName=="TD") && (c_current!=srcE)) {
		colorValue=srcE.style.backgroundColor;
		if (colorValue!=''&&colorValue.charAt(0) != '#'){
			var ds = colorValue.split(/\D+/); 
			var decimal = Number(ds[1]) * 65536 + Number(ds[2]) * 256 + Number(ds[3]); 
			colorValue="#" + rgb2hex(decimal, 6);
 		}
		srcE.style.border='1px solid #FFFFFF';
		document.getElementById("DisColor").style.backgroundColor = colorValue;
		document.getElementById("HexColor").value = colorValue.toUpperCase();
		c_current = srcE
	}
}

function doOut(evt) {
	var e=window.event?window.event:evt;
  	var srcE=e.srcElement?e.srcElement:e.target;
	srcE.style.border='1px solid #000000';
	document.getElementById("DisColor").style.backgroundColor = "#FFFFFF";
	document.getElementById("HexColor").value = "";
}

function doclick(evt){
	var e=window.event?window.event:evt;
  	var srcE=e.srcElement?e.srcElement:e.target;
	colorValue=srcE.style.backgroundColor;
	if (colorValue!=''&&colorValue.charAt(0) != '#'){
		var ds = colorValue.split(/\D+/); 
		var decimal = Number(ds[1]) * 65536 + Number(ds[2]) * 256 + Number(ds[3]); 
		colorValue="#" + rgb2hex(decimal, 6);
 	}
	if (elements[0].id.charAt(0) == 'p'){colorValue=colorValue.replace(/#/g,'');}
	elements[0].value = colorValue.toUpperCase();
	if (elements[1]!=null){
		elements[1].style.backgroundColor = colorValue;
	}
	if (elements[2]!=null){
		elements[2].style.color = colorValue;
	}
	document.getElementById("colorpanel").style.display = "none";
} 

function colorremove(){
	elements[0].value = "";
	if(elements[1]){elements[1].style.backgroundColor = "";}
	if(elements[2]){elements[2].style.color = "";}
	document.getElementById("colorpanel").style.display = "none";
} 

function colorclose(){ 
	document.getElementById("colorpanel").style.display = "none"; 
} 

function rgb2hex(num, digits) { 
	var s = num.toString(16); 
	while (s.length < digits) 
	s = "0" + s; 
	return s; 
}

function __ABS(a) {
	if (typeof a == 'string') a = document.getElementById(a);
	var b = {x: a.offsetLeft,y: a.offsetTop};
	a = a.offsetParent;
	while (a) {b.x += a.offsetLeft;b.y += a.offsetTop;a = a.offsetParent}
	return b
}

