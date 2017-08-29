/**
 * Cscms歌曲前台
 * www.chshcms.com
 */
(function(a){typeof a.CMP=="undefined"&&(a.CMP=function(){var b=/msie/.test(navigator.userAgent.toLowerCase()),c=function(a,b){if(b&&typeof b=="object")for(var c in b)a[c]=b[c];return a},d=function(a,d,e,f,g,h,i){i=c({width:d,height:e,id:a},i),h=c({allowfullscreen:"true",allowscriptaccess:"always"},h);var j,k,l=[];if(g){j=g;if(typeof g=="object"){for(var m in g)l.push(m+"="+encodeURIComponent(g[m]));j=l.join("&")}h.flashvars=j}k="<object ",k+=b?'classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" ':'type="application/x-shockwave-flash" data="'+f+'" ';for(var m in i)k+=m+'="'+i[m]+'" ';k+=b?'><param name="movie" value="'+f+'" />':">";for(m in h)k+='<param name="'+m+'" value="'+h[m]+'" />';return k+="</object>",k},e=function(c){var d=document.getElementById(c);if(!d||d.nodeName.toLowerCase()!="object")d=b?a[c]:document[c];return d},f=function(a){if(a){for(var b in a)typeof a[b]=="function"&&(a[b]=null);a.parentNode.removeChild(a)}},g=function(a){if(a){var c=typeof a=="string"?e(a):a;if(c&&c.nodeName.toLowerCase()=="object")return b?(c.style.display="none",function(){c.readyState==4?f(c):setTimeout(arguments.callee,15)}()):c.parentNode.removeChild(c),!0}return!1};return{create:function(){return d.apply(this,arguments)},write:function(){var a=d.apply(this,arguments);return document.write(a),a},get:function(a){return e(a)},remove:function(a){return g(a)}}}());var b=function(b){b=b||a.event;var c=b.target||b.srcElement;if(c&&typeof c.cmp_version=="function"){var d=c.skin("list.tree","maxVerticalScrollPosition");if(d>0)return c.focus(),b.preventDefault&&b.preventDefault(),!1}};a.addEventListener&&a.addEventListener("DOMMouseScroll",b,!1),a.onmousewheel=document.onmousewheel=b})(window);
var Music=MusicList=Musicfavor=Musicrand=Musiccai=[];
var bid=0;//当前播放歌曲的i
var did=0;//播放歌曲的id
var loop=0;//0循环1单曲2随机
var curlist=0;//0播放列表1收藏列表2随便听听
var curdance=0;//0播放列表1收藏列表2随便听听
var audio;
var lrcTime=[];
var lrcText=[];
var dance = {
	init  : function(sign){
		if(sign=='dancelrc'){
			cscms.layer.tips('点击查看歌词', '#danceLrc', {
			  tips: [3, '#3595CC'],
			  time: 2000
			});
		}
		if(sign=='playsong'){
			audio = $('#audio')[0];
			dance.get_data(data_id,0);
			$('.height').slimScroll({
				width: '634px',
				height: '460px',
				size: '4px',
				position: 'right',
				color: 'gray',
				alwaysVisible: false,
				distance: '1px',
				railColor: '#eee',
				wheelStep: 10,
				allowPageScroll: true,
				disableFadeOut: false
			});
			$('.cailist').slimScroll({
				height: '285px',
				size: '4px',
				position: 'right',
				color: 'gray',
				alwaysVisible: false,
				distance: '1px',
				railColor: '#eee',
				wheelStep: 10,
				allowPageScroll: true,
				disableFadeOut: false
			});
			$('.mb-lrc-wrap').slimScroll({
				height: '480px',
				width:'400px',
				size: '4px',
				position: 'right',
				color: 'gray',
				alwaysVisible: false,
				distance: '1px',
				railColor: '#eee',
				wheelStep: 10,
				allowPageScroll: true,
				disableFadeOut: false
			});
		}
	}
	,album_fav : function(id){//专辑收藏
		$.getJSON(cscms_path+"index.php/dance/ajax/albumfav/"+id+"?callback=?",function(data) {
           	if(data){
           		if(data['msg']=='ok'){
           			$("#shits").text(parseInt($("#shits" ).text()) + 1);
           			cscms.layer.msg('专辑收藏成功!',{icon:1});
           		}else{
           			cscms.layer.msg(data['msg'],{icon:2});
           		}
           	}else{
           		cscms.layer.msg('网络故障，连接失败!',{icon:2});
           	}
     	});
	}
	,select_all : function(){
		var a=$(".xuan");  
	    for (var i = 0; i < a.length; i++) {
	        a[i].checked = (a[i].checked) ? false : true;
	    }
	    setTimeout(function(){
	    	cscms.form.render();
	    },'50');
	}
	,playsongs : function(n){//专辑页歌曲播放
        var v = [];
　　    var a=$("input[name='check']"); 
        for (var i = 0; i < a.length; i++) {
            if(n==1){
                if(a[i].checked==true){
                    var did=a[i].value;
                    v.push(did);
                }
            }else{
                var did=a[i].value;
                v.push(did);
            }
        }
        if(1 > v.length){ 
            cscms.layer.msg('请选择要播放的歌曲！',{icon:2});return; 
        }else{
            window.open(cscms_path+'index.php/dance/playsong?id=' + v.join(','), 'play');
        }
	}
	,lookUp : function(str,cls,sign,len){//展开收起
		sign=parseInt(sign)?1:0;
		len=parseInt(len)?len:100;
		sign2=sign?0:1;
		if(str=='') return;
		if(sign==0){
			$('.'+cls).html(str);
			$('#'+cls).attr('title','收起');
			$('#'+cls).attr('href', 'javascript:dance.lookUp(\''+str+'\',\''+cls+'\','+sign2+','+len+');');
			$('#'+cls+' i').html('&#xe619;');
		}else{
			var str2 = str.substring(0,len);
			$('.'+cls).html(str2);
			$('#'+cls).attr('href', 'javascript:dance.lookUp(\''+str+'\',\''+cls+'\','+sign2+','+len+');');
			$('#'+cls).attr('title', '展开');
			$('#'+cls+' i').html('&#xe61a;');
		}
	}
	,strJie : function(str,len){//截取固定长度
		if(str=='') document.write('暂无相关记录');;
		var strl = str.length;
		if(strl>len){
			document.write(str.substring(0,len)+'...');
		}else{
			document.write(str);
		}
	}
	,danceDing : function(id){//歌曲赞
		$.getJSON(cscms_path+"index.php/dance/ajax/danceding/ding/"+id+"?callback=?",function(data) {
           if(data){
               if(data['msg']=='ok'){
                   $("#dhits").text(parseInt($("#dhits" ).text()) + 1);
                   cscms.layer.msg('感谢您的支持!',{icon:6});
               	}else{
                   cscms.layer.msg(data['msg'],{icon:5});
               	}
           	}else{
                cscms.layer.msg('网络故障，连接失败!',{icon:2});
           	}
     	});
	}
	,danceFav : function(id){//歌曲收藏
		$.getJSON(cscms_path+"index.php/dance/ajax/dancefav/"+id+"?callback=?",function(data) {
           	if(data){
           	    if(data['msg']=='ok'){
                   $("#shits").text(parseInt($("#shits" ).text()) + 1);
                   cscms.layer.msg('歌曲收藏成功!',{icon:6});
               	}else{
                   cscms.layer.msg(data['msg'],{icon:5});
               	}
           	} else {
                cscms.layer.msg('网络故障，连接失败!',{icon:2});
           	}
     	});
	}
	,get_change : function(){
		if(Music.length){
			dance.get_cai(bid);
		}
	}
	,get_cai : function(_id){
		$('.cai-list').html('');
		var cid = Music.length?Music[_id]['id']:1;
		$.getJSON(cscms_path+"index.php/dance/playsong/cais?id="+cid+"&callback=?",function(data) {
			if(data){
				Musiccai = data;
				for (var i = 0; i < data.length; i++) {
					var cls = (i<3)?'':'layui-btn-primary'
					var html='<tr align="left"><td><a class="listLeft3" href="javascript:dance.cai_add('+i+',1);"><i class="layui-btn layui-btn-mini '+cls+' iwidth">'+(i+1)+'</i>'+data[i]['name']+'</a></td><td><i onclick="dance.cai_add('+i+');" class="layui-icon">&#xe608;</i></td></tr>';
					$('.cai-list').append(html);
				}
			}else{
				cscms.layer.msg('网络连接失败！',{icon:2});
			}
		});
	}
	,cai_add : function(id){
		var newid=0;
		if(id!=undefined){
			var sign=0;
			for (var i = 0; i <MusicList.length; i++) {
				if(MusicList[i]['id']==Musiccai[id]['id']){
					newid=i;
					sign=1;break;
				}
			}
			if(sign==0){
				MusicList.unshift(Musiccai[id]);
				if(curlist==0){
					dance.play_list();
				}
				cscms.layer.msg('恭喜你,加入成功~!',{icon:1});
			}else{
				if(arguments[1]==1 && curlist==0){
					dance.player(newid);
				}else{
					cscms.layer.msg('抱歉,歌曲已存在列表中~!',{icon:2});
				}
			}
			if(arguments[1]==1 && curlist==0){
				dance.player(newid);
			}
		}else{
			for (var j = Musiccai.length-1; j >=0; j--) {
				var sid=0;
				for (var i = MusicList.length-1; i >=0; i--) {
					if(MusicList[i]['id']==Musiccai[j]['id']){
						sid=1;break;
					}
				}
				if(sid==0){
					MusicList.unshift(Musiccai[j]);
					if(curlist==0){
						dance.play_list();
					}
				}
			}
			cscms.layer.msg('恭喜你,加入成功~!',{icon:1});
		}
	}
	,get_data : function(_id,_sid){
		if(MusicList.length==0){
            $.getJSON(cscms_path+"index.php/dance/playsong/data?id=" + data_id+"&callback=?",function(data) {
            	if (data){
					Music = data;
					MusicList = data;
					dance.play_list(0);
					if(_sid==0 && Music.length){
			        	dance.get_cai(bid);
			        }
				}else{
					cscms.layer.msg('获取播放列表信息错误，请刷新重试',{icon:2});
                }
	    	});
	    	
        }else{
            Music = MusicList;
            dance.play_list();
        }
        curlist=0;
        $('.li_rand').removeClass('on');
        $('.li_fav').removeClass('on');
        $('.li_list').addClass('on');
        $('.title-list').html('播放列表');
	}
	,get_rand : function(){
		if(Musicrand.length==0){
			$.getJSON(cscms_path+"index.php/dance/playsong/rand?callback=?",function(data) {
				Music = data;
				Musicrand = data;
				dance.play_list();
	     	});
        }else{
            Music = Musicrand;
            dance.play_list();
        }
        curlist=2;
        $('.li_list').removeClass('on');
        $('.li_fav').removeClass('on');
        $('.li_rand').addClass('on');
        $('.title-list').html('随便听听');
	}
	,get_fav : function(){
		$.getJSON(cscms_path+"index.php/dance/playsong/favs?callback=?",function(data) {
			if(data['error']=='login'){
				$('#loginkuang').animate({
                    height: 'toggle'
                },"fast");
                $('#loginmask').toggle();
			}else{
				Music=data;
				Musicfavor=data;
				dance.play_list();
				curlist=1;
				$('.li_list').removeClass('on');
		        $('.li_rand').removeClass('on');
		        $('.li_fav').addClass('on');
		        $('.title-list').html('我的收藏');
			}
      	});
	}
	,play_list : function(n){
		$('#play_list').html('');
		var html = '';
        for (var i = 0; i < Music.length; i++) {
        	var singer = (Music[i]['singer']=='')?'佚名':Music[i]['singer'];
        	if(did==Music[i]['id']){
        		var clss = 'style="color:green;"';
        		bid=i;
        		curdance=curlist;
        	}else{
        		var clss = '';
        	}
        	html += '<tr id="m_'+i+'"><td align="left"><a id="dn'+i+'" '+clss+' href="javascript:dance.player('+i+');" title="点击播放">'+(i+1)+'、'+Music[i]['name']+'</a></td><td class="td-icon"><div class="disno"><a href="'+Music[i]['downlink']+'" target="_blank" title="下载"><i class="layui-icon">&#xe601;</i></a><a href="javascript:dance.danceFav('+Music[i]['id']+');" title="收藏"><i class="layui-icon">&#xe600;</i></a><a href="javascript:dance.player('+i+');" title="试听"><i class="layui-icon">&#xe6fc;</i></a><a href="javascript:dance.play_del('+i+');" title="删除"><i class="layui-icon">&#x1006;</i></a></div></td><td><a target="_blank" href="'+Music[i]['singerlink']+'">'+singer+'</a></td><td>'+Music[i]['time']+'</td></tr>';
        }
        $('#play_list').append(html);
        if(Music.length!=0 && n!=undefined){
        	dance.player(n);
        }
	}
	,play_del : function(i){
		if(i==bid && curlist==curdance){
			cscms.layer.msg('该歌曲正在播放，不能删除',{icon:5});return;
		}
		if(curlist==1){
			cscms.layer.msg('收藏歌曲,请勿删除',{icon:7});return;
		}
		$('#m_'+i).remove();
		Music.splice(i,1);
		if(curlist==0){
			MusicList.splice(i,1);
		}
		if(curlist==2){
			Musicrand.splice(i,1);
		}
		dance.play_list();
	}
	,player : function(n){
		curdance = curlist;
		audio.src = encodeURI(Music[n]['url']);
		$('#playImg').attr('src', Music[n]['tpic']);
		if(Music[n]['singer']!=''){
			$('#playSinger').html(Music[n]['singer']+'&nbsp;-&nbsp;');
		}
		$('#playSong').html(Music[n]['name']);
		$('#timeNow').html('00:00');
		$('#timeEnd').html(Music[n]['time']);
		$('#playFav').attr('href', 'javascript:dance.danceFav('+Music[n]['id']+');');
		$('#playDown').attr('href', Music[n]['downlink']);
		bid = n;did=Music[n]['id'];
		for (var i = Music.length - 1; i >= 0; i--) {
			$('#dn'+i).css('color', '#333');
		}
		$('#dn'+bid).css('color', 'green');
		$('.layui-progress-bar').css('width', '0%');
		dance.play_pause(1);
		dance.get_lrc(Music[n]['lrc']);
		$('#gc-title').html('《'+Music[n]['name'].substring(0,30)+'...》歌词');
	}
	,play_pre : function(){
		var tempid = Music.length-1;
		if(curlist!=curdance){
			dance.player(0);return;
		}
		if(tempid==-1) return;
		if(loop==0){//循环
			if(bid==0){
				dance.player(tempid);
			}else{
				tempid = bid-1;
				dance.player(tempid);
			}
		}else{
			if(loop==1){//单曲
				dance.player(bid);
			}else{
				tempid = parseInt(Math.random()*tempid);
				dance.player(tempid);
			}
		}
		curdance = curlist;
	}
	,play_nxt : function(){
		if(curlist!=curdance){
			dance.player(0);return;
		}
		var tempid = Music.length-1;
		if(tempid==-1) return;
		if(loop==0){
			if(bid==tempid){
				dance.player(0);
			}else{
				tempid = bid+1;
				dance.player(tempid);
			}
		}else{
			if(loop==1){//单曲
				dance.player(bid);
			}else{
				tempid = parseInt(Math.random()*tempid);
				dance.player(tempid);
			}
		}
		curdance = curlist;
	}
	,play_loop : function(){
		if(loop==0){
			loop = 1;
			$('#playLoop').removeClass('loop').addClass('one');
		}else{
			if(loop==1){
				loop = 2;
				$('#playLoop').removeClass('one').addClass('random');
			}else{
				loop = 0;
				$('#playLoop').removeClass('random').addClass('loop');
			}
		}
	}
	,play_vol : function(){
		if(audio.muted){
			$('#playVol').removeClass('offvol').addClass('onvol');
			audio.muted = false;
			audio.volume = 1;
		}else{
			$('#playVol').removeClass('onvol').addClass('offvol');
			audio.muted = true;
			audio.volume = 0;
		}
	}
	,play_pause : function(sign){
		if(sign){
			$('#playNow').removeClass('playNow1').addClass('playNow2');
			audio.play();
			player = setInterval("dance.play_progress()","20");

			showlrc = setInterval(function(){
				var nowtime = audio.currentTime;
				dance.get_lrclist(nowtime*1000);
			},"1000");
		}else{
			if(audio.paused){//paused yes
				audio.play();
				$('#playNow').removeClass('playNow1').addClass('playNow2');
				player = setInterval("dance.play_progress()","20");
				showlrc = setInterval(function(){
					var nowtime = audio.currentTime;
					dance.get_lrclist(nowtime*1000);
				},"1000");
			}else{
				audio.pause();
				$('#playNow').removeClass('playNow2').addClass('playNow1');
				clearInterval(player); 
				clearInterval(showlrc); 
			}
		}
	}
	,play_progress : function(){
		if(audio.ended){
			dance.play_nxt();
		}
		var nowtime = audio.currentTime;
		var alltime = audio.duration;
		$('#timeNow').html(dance.timetostr(audio.currentTime));
		$('#timeEnd').html(dance.timetostr(audio.duration));
		var play_jd = nowtime/alltime*100+'%';
		$('.playDot').css('left',play_jd);
		$('.layui-progress-bar1').css('width', play_jd);
	}
	,timetostr :function(second){
		return [ parseInt(second / 60) % 60, parseInt(second % 60)].join(":").replace(/\b(\d)\b/g, "0$1");
	}
	,get_pro : function(){
		var l = $('.layui-progress1').offset().left;
		var e = event || window.event;
        var p = e.pageX;
        var pro = (p-l)/460*100+'%';
        $('.playDot').css('left',pro);
        $('.layui-progress-bar1').css('width',pro);
        audio.currentTime = audio.duration*(p-l)/460;
	}
	,show_lrc : function(){//歌词显示和隐藏
		$('#lrcKuang').toggle();
	}
	,get_lrc : function(src){//给lrc编码
		var lrc1 = src.split("[");
		lrcTime=[];lrcText=[];
		for (var i = 0; i < lrc1.length; i++) {
			var t = lrc1[i].split("]");
			var temp = dance.get_ltime(t[0]);
			if (isNaN(temp)) continue;
			lrcTime.push(temp);
			if(t[1]==""){
				lrcText.push(dance.get_text(i,lrc1));
			}else{
				lrcText.push(t[1]);
			}
		}
	}
	,get_text : function(i,lrc1){
		var result = "";
		var i = i + 1;
		if (lrc1[i]) {
			var t = lrc1[i].split("]");
			if (t[1] == "") result = dance.get_text(i, lrc1);
			else result = t[1];
		}
		return result;
	}
	,set_ptime : function(time){//点击歌词跳转
		audio.currentTime = time/1000;
		dance.get_lrclist(time);
	}
	,get_lrclist : function(time){//打印歌词列表
		if(lrcTime.length>1){
			$('.js-lrc-no').hide();
			$('#lyric').show();
		}else{
			$('#lyric').hide();
			$('.js-lrc-no').show();return;
		}
		for (var i = 0; i < lrcTime.length; i++) {
			if(time>lrcTime[i] && time<lrcTime[i+1]){
				for (var j = 0; j < 21; j++) {
					var mylrc=lrcText[i+j-6]?lrcText[i+j-6]:'';
					if(j==6){
						var html = '<a href="javascript:dance.set_ptime('+lrcTime[i+j-7]+');" style="color:green;font-weight:bold">'+mylrc+'</a>';
					}else{
						var html = '<a  href="javascript:dance.set_ptime('+lrcTime[i+j-7]+');">'+mylrc+'</a>';
					}
					$('#LR'+j).html(html);
				}
			}
		}
	}
	,get_ltime : function(tn){//计算毫秒数
		var time = 0;
		var ta = tn.split(":");
		if (ta.length < 2) return time;
		if (ta[1].indexOf(".") > 0) {
			var tb = ta[1].split(".");
			time = ta[0] * 60 * 1000 + tb[0] * 1000 + tb[1] * 10
		} else time = ta[0] * 60 * 1000 + ta[1] * 1000;
		return time;
	}
    //cmp音频播放器
	,mp3_play : function () {
	     var flashvars = { 
			api : "cmp_loaded",
			skins : mp3_t+"packs/vod_player/cmp/mp3.swf",
			auto_play : "1",
	                play_mode : "1",
	                play_id   : "1",
			lists     : mp3_p+'/url/cmp/'+mp3_i+'?dance.mp3'
	     };
	     var html = CMP.create("cmp", mp3_w+"px", mp3_h+"px", mp3_t+"packs/vod_player/cmp/cmp.swf", flashvars, {wmode:"transparent"});
	     document.writeln(html);
	}
	//jp音频播放器带LRC
	,mp3_jplayer : function() {
	     cscms.inc_js(mp3_p+'/url/jp/'+mp3_i);
	     document.write('<link href="'+cscms_path+'packs/vod_player/jplayer/skin/lrc/css.css" rel="stylesheet" type="text/css" />');
	     document.write('<script type="text/javascript" src="'+cscms_path+'packs/vod_player/jplayer/js/lrc.js"></script>');
	     document.write('<script type="text/javascript" src="'+cscms_path+'packs/vod_player/jplayer/js/jquery.jplayer.min.js"></script>');
	     document.write('<div id="cscms_lyric" onmouseover="$(\'.seegc\').show();" onmouseout="$(\'.seegc\').hide();"><div class="seegc" style="display: none;"><a href="'+mp3_l+'" target="_blank">下<br>载<br>歌<br>词</a></div><p id="LR1"></p><p id="LR2"></p><p id="LR3"></p><p id="LR4"></p><p id="LR5"></p><p id="LR6"></p><p id="LR7"></p></div><div class="cscms_jplayer"><div id="radioPlayer"class="jp-jplayer"></div><div id="jp_container_1"class="jp-audio"><div class="jp-type-single"><div class="jp-interface clearfix"><div class="playerMain-01"><p><span id="PlayStateTxt">正在播放:</span><span id="play_musicname">'+mp3_n+'</span></p><div class="jp-time-holder"><div class="jp-current-time">00:00</div>/<div class="jp-duration">00:00</div></div></div><div class="playerMain-02"><div class="jp-progress"><div class="jp-seek-bar"><div class="jp-play-bar"></div></div></div></div>');
	     document.write('<div class="playerMain-03"><div class="fl"><ul class="jp-controls"><li><a href="javascript:;"class="jp-play"tabindex="1">播放</a></li><li><a style="display:none;"href="javascript:;"class="jp-pause"tabindex="1">暂停</a></li></ul></div><div class="fr"><ul class="ku-volume"><li><a href="javascript:{};"class="jp-mute"tabindex="1"title="静音">静音</a></li><li><a href="javascript:{};"class="jp-unmute"style="display:none;"tabindex="1"title="取消静音">取消静音</a></li><li class="volume-bar-wrap"><div class="jp-volume-bar"><div class="jp-volume-bar-value"></div></div></li><li><a href="javascript:;"class="jp-volume-max"tabindex="1"title="最大音量">最大音量</a></li></ul></div><p class="ringDown"><a href="'+mp3_x+'"target="_blank">歌曲下载</a></p></div></div>');
	     document.write('<div class="jp-no-solution"><span>播放出现故障,您需要更新！</span>对不起，您需要更新您的浏览器到最新版本或更新您的flash播放器版本！<br/><a href="http://get.adobe.com/flashplayer/"target="_blank">点击下载Flash plugin>></a></div></div></div></div>');
	     playtimes=setInterval("dance.get_jpplay();",1000);
	}
	//JP播放器播放
	,get_jpplay : function() {
	     if($("#radioPlayer").length>0 && typeof(mp3_u)!='undefined'){
	          clearInterval(playtimes);
	          $("#radioPlayer").jPlayer({
	             supplied: "mp3,m4a",
	             swfPath: cscms_path+"packs/vod_player/jplayer/js",
	             wmode: "window",
	             ready:function (event){ 
	                  if(mp3_u.indexOf(".m4a")>0){
		              $("#radioPlayer").jPlayer("setMedia", {m4a:mp3_u}).jPlayer("play");
	                  }else{
		              $("#radioPlayer").jPlayer("setMedia", {mp3:mp3_u}).jPlayer("play");
	                  }
	                  pu.downloadlrc(0);
	                  pu.PlayLrc(0);
	             },
	             ended: function () {
	                  if(mp3_u.indexOf(".m4a")>0){
		              $("#radioPlayer").jPlayer("setMedia", {m4a:mp3_u}).jPlayer("play");
	                  }else{
		              $("#radioPlayer").jPlayer("setMedia", {mp3:mp3_u}).jPlayer("play");
	                  }
	                  pu.downloadlrc(0);
	                  pu.PlayLrc(0);
	             }
	          });
	     }
	}
}