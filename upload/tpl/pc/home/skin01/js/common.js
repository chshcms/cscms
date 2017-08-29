//会员
var home = {
        init: function(){
				var eTimer = null,lTimer = null,$navBtn = $('.jsNavBtn'),$sNavMenu = $('.jsSNavMenu');
				$navBtn.hover(function() {
				  if (lTimer) clearTimeout(lTimer);
				  eTimer = setTimeout(function () {
					   $sNavMenu.show();
				  }, 100);
				}, function() {
				  if (eTimer) clearTimeout(eTimer);
				  lTimer = setTimeout(function () {
					   $sNavMenu.hide();
				  }, 100)
				});
				$sNavMenu.hover(function() {
				  if (lTimer) clearTimeout(lTimer);
				  $(this).show();
				}, function() {
				  $(this).hide();
				});
				$('.seh_list').hover(function() {
				  $('.seh_sort').show();
				}, function() {
				  $('.seh_sort').hide();
				});
				$('.seh_sort a').click(function() {
				  $('.seh_list_a').html($(this).text() + '<b class="icon icon_ser_arr"></b>');
				  $('#txtKey').attr('dir',$(this).attr('dir'));
				  $('.seh_sort').hide();
				});
				$('.seh_b').click(function() {
				  var key=$('#txtKey').val();
				  var dir=$('#txtKey').attr('dir');
				  if(key==''){
					  cscms.layer.msg('请输入要搜索的内容~');
				  }else{
				  var url=cscms_path+"index.php/"+dir+"/search?key="+encodeURIComponent(key);
				  window.open(url);
				  }
				});
				var eTimer2 = null,lTimer2 = null;
				$('.nav_up').hover(function() {
				  if (lTimer2) clearTimeout(lTimer2);
				  eTimer2 = setTimeout(function () {
					   $('.upload_box').show();
				  }, 100);
				}, function() {
				  if (eTimer2) clearTimeout(eTimer2);
				  lTimer2 = setTimeout(function () {
					   $('.upload_box').hide();
				  }, 100)
				});
				$('.upload_box').hover(function() {
				  if (lTimer2) clearTimeout(lTimer2);
				  $(this).show();
				}, function() {
				  $(this).hide();
				});
				var eTimer3 = null,lTimer3 = null;
				$('.userinfoshow').hover(function() {
				  if (lTimer3) clearTimeout(lTimer3);
				  eTimer3 = setTimeout(function () {
					   $('.head_box').show();
				  }, 100);
				}, function() {
				  if (eTimer3) clearTimeout(eTimer3);
				  lTimer3 = setTimeout(function () {
					   $('.head_box').hide();
				  }, 100)
				});
				$('.head_box').hover(function() {
				  if (lTimer3) clearTimeout(lTimer3);
				  $(this).show();
				}, function() {
				  $(this).hide();
				});
				//分享开关
				$('.action_share').click(function(){
				   var title=$(this).attr('data-name');
				   var url=$(this).attr('data-link');
				   $('#showbg').attr('data-title',title).attr('data-url',url);
				   $('#showbg').css({width:$(window).width(),height:document.body.scrollHeight});
				   $('#showbg').show(); 
				   $('.popup').show();     
				});
				//关闭分享
				$('.fancybox-close').click(function(){
				   $('#showbg').css({width:0,height:0});
				   $('#showbg').hide(); 
				   $('.popup').hide();     
				});
				//分享
				$('.share li').click(function(){
				   var title=$('#showbg').attr('data-title');
				   var url=$('#showbg').attr('data-url');
				   var ac=$(this).attr('class');
				   dance_share(ac,title,url);
				});
        },
        fav: function(uid,sid){
            var followBtns = $(".song_list > ul > li a.action_fav"), otherDIds = "";
            followBtns.each(function(){
                var did = parseInt($(this).attr("did"));
                if(!isNaN(did) && did > 0) {
                    otherDIds += did + ",";
                }
            });
            if(!!otherDIds){
                $.getJSON(cscms_path+"index.php/home/ajax/favinit?did="+otherDIds+"&callback=?",function(res1){
                    followBtns.each(function(){
                        var tmpUid = parseInt($(this).attr("did"));
                        var tmpFollorBtn = $(this);
                        if(tmpFollorBtn && !isNaN(tmpUid) && tmpUid)
                        {
                            if(!!res1.data[tmpUid]){
                                 tmpFollorBtn.addClass("action_fav_clo");
                            }
                        }
                    });
                });
            }
        },
        favadd: function(did){
            $.getJSON(cscms_path+"index.php/home/ajax/fav/"+did+"?random="+Math.random()+"&callback=?",function(data) {
                      if(data['error']=='ok'){ //成功
                           $('.fav'+did).addClass("action_fav_clo");
                           cscms.layer.msg('歌曲收藏成功');
                      } else if(data['error']=='del'){ //解除成功
                           $('.fav'+did).removeClass("action_fav_clo");
                           cscms.layer.msg('取消收藏成功');
                      } else {
                           cscms.layer.msg(data['error'],{icon:2});
                      }
            });
        },
        fans: function(uid,sid){
            var followBtns = $(".fans_items > ul > li.f_item h2.c_wap a.rt"), otherUserIds = "";
            followBtns.each(function(){
                var uid = parseInt($(this).attr("uid"));
                if(!isNaN(uid) && uid > 0) {
                    otherUserIds += uid + ",";
                }
            });
            if(!!otherUserIds){
                $.getJSON(cscms_path+"index.php/home/ajax/fansinit?uid="+otherUserIds+"&callback=?",function(res1){
                    followBtns.each(function(){
                        var tmpUid = parseInt($(this).attr("uid"));
                        var tmpFollorBtn = $(this);
                        if(tmpFollorBtn && !isNaN(tmpUid) && tmpUid)
                        {
                            if(!!res1.data[tmpUid]){
                                if(res1.data[tmpUid] == 2){
                                    tmpFollorBtn.removeClass("watch_btn").addClass("other_btn").html("<i></i>相互关注");
                                }else{
                                    tmpFollorBtn.removeClass("watch_btn").addClass("has_btn").html("<i></i>已关注");
                                }
                            }
                        }
                    });
                });
            }
        },
	fansadd: function(uid,sid){
              $.getJSON(cscms_path+"index.php/home/ajax/fans/"+uid+"/"+sid+"?random="+Math.random()+"&callback=?",function(data) {
                  if(data['error']=='ok'){ //关注成功
                       if(sid==2){
                           cscms.layer.msg('关注成功');
                           $('.gz'+uid).removeClass("watch_btn").addClass("has_btn").html("<i></i>已关注");
                       } else if(sid==1){
                           cscms.layer.msg('关注成功');
                           $('.gz'+uid).text('已关注');
                       } else {
                           $('.fol_btn').text('取消关注');
                       }
                  } else if(data['error']=='del'){ //解除成功
                       if(sid==2){
                           cscms.layer.msg('您已经关注了对方',{icon:2});
                       } else if(sid==1){
                           cscms.layer.msg('您已经关注了对方',{icon:2});
                           $('.gz'+uid).text('已关注');
                       } else {
                            $('.fol_btn').text('+关注');
                       }
                  } else {
                       cscms.layer.msg(data['error'],{icon:2});
                  }
              });
        },
        zan: function(uid){
            $.getJSON(cscms_path+"index.php/home/ajax/zan/"+uid+"?random="+Math.random()+"&callback=?",function(data) {
                      if(data['error']=='ok'){ //成功
                           var xhits=parseInt($('#zanhits').text())+1;
                           $('#zanhits').text(xhits);
                           cscms.layer.msg('谢谢您的支持~');
                      } else {
                           cscms.layer.msg(data['error'],{icon:2});
                      }
            });
        }
}
//歌曲全部播放
function playsong(n){
        var v = [];
        var nums=$('.song_list #song-'+n).length;
        for (var i = 0; i < nums; i++) {
              var did=$('.song_list #song-'+n+':eq('+i+')').attr('did');
              v.push(did);
        }
        window.open(cscms_path+'index.php/dance/playsong?id=' + v.join(','), 'play');
}
//歌曲分享
function dance_share(ac,title,url) {
	if(ac=='share_qzone'){
               var url="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url="+encodeURI(url)+"&title="+encodeURI('分享一首好听的音乐，--《'+title+'》播放地址：');
        } else if(ac=='share_qwei'){
               var url="http://share.v.t.qq.com/index.php?c=share&a=index&appkey=&url="+encodeURI(url)+"&title="+encodeURI('分享一首好听的音乐，--《'+title+'》播放地址：')+"&site=";
        } else if(ac=='share_weibo'){
               var url="http://service.weibo.com/share/share.php?appkey=&title="+encodeURI('分享一首好听的音乐，--《'+title+'》播放地址：')+"&url="+encodeURI(url);
        } else if(ac=='share_baidu'){
               var url="http://tieba.baidu.com/f/commit/share/openShareApi?url="+encodeURI(url)+"&title="+encodeURI('分享一首好听的音乐，--《'+title+'》播放地址：'+url)+"&desc="+encodeURI('分享一首好听的音乐，--《'+title+'》播放地址：'+url)+"&comment="+encodeURI('分享一首好听的音乐，--《'+title+'》播放地址：'+url);
        } else if(ac=='share_renren'){
               var url="http://widget.renren.com/dialog/share?resourceUrl="+encodeURI(url)+"&title="+encodeURI(title)+"&description="+encodeURI('分享一首好听的音乐，--《'+title+'》播放地址：'+url)+"&srcUrl="+encodeURI(url);
        } else if(ac=='share_kaixin'){
               var url="http://www.kaixin001.com/rest/records.php?url="+encodeURI(url)+"&style=11&content="+encodeURI('分享一首好听的音乐，--《'+title+'》播放地址：')+"&stime=&sig=";
	};
	window.open(url,'share');
}
