/**
 * Cscms歌曲前台
 * www.chshcms.com
 */
var audio;
var Music=MusicList=MusicFav=[];
var did=oldi=newi=0;
var musicsign = 0;
var playSign = 0;
var dance = {
	init  : function(sign){
		if(sign=='mdplay'){
			audio = $('#audio')[0];
			//dance.mdPlay();
		}
		if(sign=='mpplay'){
			audio = $('#audio')[0];
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
            location.href = cscms_path+'index.php/dance/playsong?id=' + v.join(',');
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
	
	,mdPlay:function(sign){//歌曲播放或暂停
		playSign = 1;
		if(sign){
			audio.play();
			$('#mdplay').removeClass('md-pause').addClass('md-pnow');
			player = setInterval("dance.md_progress()","20");
			return;
		}
		if(audio.paused){//paused yes
			audio.play();
			$('#mdplay').removeClass('md-pause').addClass('md-pnow');
			player = setInterval("dance.md_progress()","20");
		}else{
			audio.pause();
			$('#mdplay').removeClass('md-pnow').addClass('md-pause');
			clearInterval(player);
		}
	}
	,md_progress : function(){//更新进度条播放进度
		if(audio.ended){
			audio.currentTime = 0;
			dance.mdPlay(1);
		}
		var nowtime = audio.currentTime;
		var alltime = audio.duration;
		$('#timeNow').html(dance.timetostr(audio.currentTime));
		$('#timeEnd').html(dance.timetostr(audio.duration));
		var play_jd = nowtime/alltime*100+'%';
		$('.md-pronow').css('width', play_jd);
	}
	,timetostr :function(second){//时间转换
		return [ parseInt(second / 60) % 60, parseInt(second % 60)].join(":").replace(/\b(\d)\b/g, "0$1");
	}
	,get_pro : function(){//点击歌曲播放进度条
		var l = $('.md-progress').offset().left;
		var e = event || window.event;
        var p = e.pageX;
        var len = parseInt($('.md-progress').width());
        var pro = (p-l)/len*100+'%';
        $('.md-pronow').css('width',pro);
        audio.currentTime = audio.duration*(p-l)/len;
        dance.mdPlay(1);
	}
	,mclass : function (){
		if($(".md-head-more").is(":hidden")){
            $(".md-head-more").show();
        }else{
            $(".md-head-more").hide();
        }
	}
	,intoList : function(sign){
		if(sign==0){
			if(MusicList.length==0) dance.get_data();
		}else{
			if(MusicFav.length==0) dance.get_fav();
		}
	}
	,get_data : function(){//获取列表数据
        $.getJSON(cscms_path+"index.php/dance/playsong/data?id=" + data_id+"&callback=?",function(data) {
        	if (data){
				Music = data;
				MusicList = data;
				dance.play_list(0);
			}else{
				cscms.layer.msg('获取播放列表信息错误，请刷新重试',{icon:2});
            }
    	});
	}
	,get_fav : function(){
		$.getJSON(cscms_path+"index.php/dance/playsong/favs?callback=?",function(data) {
			if(data['error']=='login'){
				cscms.layer.msg('抱歉，您还没有登录');
			}else{
				MusicFav=data;
				dance.play_list(1);
			}
      	});
	}
	,play_list : function(sign){//0播放列表1收藏列表
		var html = '';
		if(sign==0) var Music2 = MusicList;
		if(sign==1) var Music2 = MusicFav;
		for (var i = 0; i < Music2.length; i++) {
        	if(did==Music2[i]['id']){
        		var clss = 'style="color:green;"';
        	}else{
        		var clss = '';
        	}
        	if(i<3){
        		var cls2 = 'dance-pson';
        	}else{
        		var cls2 = '';
        	}
        	html += '<li class="dance-one songDel_'+sign+'_'+i+'" style="margin-bottom: 10px"><div class="dance-one-left"><a class="id-title playsong_'+sign+'_'+i+'" title="[dance:name]" href="javascript:dance.playSong('+i+','+sign+');"><span class="dance-ps '+cls2+'">'+(i+1)+'</span>'+Music2[i]['name']+'</a><div class="dance-one-more dance-one-more2"><span><a href="javascript:dance.playSong('+i+','+sign+');"><i class="layui-icon">&#xe6fc;</i>播放</a></span>';
        	if(sign==0) html +='<span class="md-spanml"><a href="javascript:dance.danceFav('+Music2[i]['id']+');"><i class="layui-icon">&#xe600;</i>收藏</a></span><span class="md-spanml"><a href="javascript:dance.songDel('+i+','+sign+');"><i class="layui-icon" style="font-size: 18px;position: relative;top: 1px">&#xe640;</i>删除</a></span>';
        	html += '</div></div><a class="dance-singer" href="javascript:;">'+Music2[i]['time']+'</a></li>';
        	
        }
        if(html==''){
        	html='<div class="zanwu">暂无相关记录</div>';
        }
        if(sign==0){
    		$('#md-playlist').html(html);
    	}else{
    		$('#md-favlist').html(html);
    	}
	}
	,mpPlayer:function(sign){//播放暂停
		if(audio.src==''){
			dance.playSong(0,musicsign);
			return;
		}
		playSign = 1;
		if(sign){
			audio.play();
			$('.mp-play').addClass('mp-pause');
			playsong = setInterval("dance.mp_progress()","20");
			return;
		}
		if(audio.paused){//paused yes
			audio.play();
			$('.mp-play').addClass('mp-pause');
			playsong = setInterval("dance.mp_progress()","20");
		}else{
			audio.pause();
			$('.mp-play').removeClass('mp-pause');
			clearInterval(playsong);
		}
	}
	,playSong:function(i,sign){
		if(sign==musicsign){
			audio.src = encodeURI(Music[i]['url']);
			$('#md-name').html(Music[i]['name']);
			$('#timeNow').html('00:00');
			$('#timeEnd').html(Music[i]['time']);
			$('.playsong_'+musicsign+'_'+newi).css('color', '#333');
			newi = i;
			$('.playsong_'+musicsign+'_'+i).css('color', 'green');
			dance.mpPlayer(1);
		}else{
			if(sign==0){
				var listid = 'md-playlist';
				Music = MusicList;
			} 
			if(sign==1){
				var listid = 'md-favlist';
				Music = MusicFav;
			} 
			audio.src = encodeURI(Music[i]['url']);
			$('#md-name').html(Music[i]['name']);
			$('#timeNow').html('00:00');
			$('#timeEnd').html(Music[i]['time']);
			$('.playsong_'+musicsign+'_'+newi).css('color', '#333');
			newi = i;musicsign = sign;
			$('.playsong_'+sign+'_'+i).css('color', 'green');
			dance.mpPlayer(1);
		}
	}
	,mp_progress:function(){
		if(audio.ended){
			dance.mpChange(1);
		}
		var nowtime = audio.currentTime;
		var alltime = audio.duration;
		$('#timeNow').html(dance.timetostr(audio.currentTime));
		$('#timeEnd').html(dance.timetostr(audio.duration));
		var play_jd = nowtime/alltime*100+'%';
		$('.md-nowpro').css('width', play_jd);
	}
	,get_mppro : function(){//点击音乐盒歌曲播放进度条
		var l = $('.md-pro').offset().left;
		var e = event || window.event;
        var p = e.pageX;
        var len = parseInt($('.md-pro').width());
        var pro = (p-l)/len*100+'%';
        $('.md-nowpro').css('width',pro);
        audio.currentTime = audio.duration*(p-l)/len;
        dance.mpPlayer(1);
	}
	,mpChange:function(sign){
		var len = Music.length-1;
		if(sign==0){
			if(newi==0){
				var i = len;
			}else{
				var i = newi-1;
			}
		}else{
			if(newi==len){
				var i = 0;
			}else{
				var i = newi+1;
			}
		}
		dance.playSong(i,musicsign);
	}
	,songDel:function(i,sign){
		$('.songDel_'+sign+'_'+i).remove();
		Music.splice(i,1);
		if(sign==0){
			MusicList.splice(i,1);
		}
		dance.play_list(0);
	}
}