/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2017-04-01
 */
var cscms_zd = 0;//意见留言
var wait = 60;//手机验证码发送时间间隔
var cscms_share_url,cscms_share_id,cscms_share_title;
var cscms = {
	wap  		: navigator.userAgent.match(/iPad|iPhone|Android|Linux|iPod/i) != null,
	element		: null,
	layer       : null,
	form        : null,
	upload      : null,
	layedit     : null,
	laydate     : null,
	init  : function(){
		cscms.get_host();
		//初始化
		layui.use(['element','jquery','form','layer','upload','layedit','laydate'], function(){
			cscms.element = layui.element
			,cscms.jquery = layui.jquery
			,cscms.form = layui.form
			,cscms.upload = layui.upload
			,cscms.laydate = layui.laydate
			,cscms.layer = layui.layer
			,cscms.layedit = layui.layedit;
			/*搜索提交*/
			cscms.form.on('submit(search)',function(data){
				var key =$('#key').val();
				if(key==''){
					cscms.layer.msg('请填写要搜索的关键词~！',{icon:2});return false;
				}
			});
			//console.log(cscms.laydate);
			/*postData的表单提交*/
			cscms.form.on('submit(postData)', function(data){
				var index = cscms.layer.load(1);
				var backurl = $('.layui-form').attr('backurl');
				$.post(data.form.action, data.field, function(data) {
					if(data.error==0){
						var msg = '恭喜您,操作成功';
						if(typeof(data.info.msg) != undefined && data.info.msg != ''){
							msg = data.info.msg;
						}
						layer.msg(msg,{icon:1});
						if(typeof(data.info.url) != undefined){//加载页面动作
							if(typeof(data.info.url)==''){//重新加载
								if(typeof(data.info.parent)!=undefined){//父操作
									setTimeout(function(){
										parent.location.reload();
									},2000);
								}else{//当前页
									setTimeout(function(){
										location.reload();
									},2000);
								}
							}else{//跳转
								if(typeof(data.info.parent)!=undefined){//父操作
									setTimeout(function(){
										parent.location.href = data.info.url;
									},2000);
								}else{//当前页
									setTimeout(function(){
										location.href = data.info.url;
									},2000);
								}
							}
						}
						if(data.info.sign=='login'){
							$('#userLogin').html(data.info);
						}
					}else{
						cscms.layer.msg(data.info,{icon:2});
					}
				},"json");
				return false;
			});
		});
	}
	//获取当前主域名
	,get_host : function (){
		var host=window.location.host;
		var DomainUrl = window.location.host.match(/[^.]*\.(com\.cn|gov\.cn|net\.cn|cn\.com)(\.[^.]*)?/ig);
		var reip = /^([0-9]|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.([0-9]|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.([0-9]|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.([0-9]|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])$/;
		var hostip=host.split(":");//去掉IP端口
		if(DomainUrl==null && host!='localhost' && host!='localhost' && !reip.test(hostip[0])){
			var host_arr=host.split("."); 
			var nums=host_arr.length;
			DomainUrl=host_arr[nums-2]+'.'+host_arr[nums-1];
		}
		//设置域名
		if(DomainUrl!=null){
		    document.domain = DomainUrl;
		}
	}
	//弹出层
	,get_open : function(url,title,w,h){
		cscms.layer.open({
			title:title,
			type: 2,
			area: [w, h],
			closeBtn: 1, //不显示关闭按钮
			shade: 0.01,
			shadeClose: true, //开启遮罩关闭
			content: url
		});
	}
	//上传文件
	,get_upurl : function(dir,fid,type,tsid){
		if(cscms.wap){
			var url = cscms_path+'index.php/upload_wap';
			var w = '90%';
			var h = '350px';
		}else{
			var url = cscms_path+'index.php/upload';
			var w = '500px';
			var h = '350px';
		}
		url += '?dir='+dir+'&fid='+fid+'&type='+type+'&tsid='+tsid;
		cscms.layer.open({
			title:'上传文件',
			type: 2,
			area: [w, h],
			closeBtn: 1, //不显示关闭按钮
			shade: 0.01,
			shadeClose: false, //开启遮罩关闭
			content: url
		});
	}
	//登录框
	,login : function(){//获取登录状态
		$.getJSON(cscms_loginlink+"?random="+Math.random()+"&callback=?",function(data) {
           if(data['str']){
                $("#cscms_login").html(data['str']);
           } else {
                $("#cscms_login").html("您请求的页面出现异常错误");
           }
     	});
	}
	//会员登陆
	,loginAdd : function(){//提交登陆信息
		var name=$("#cscms_name").val();
		var pass=$("#cscms_pass").val();
		if(name=='' || pass==""){
			cscms.layer.msg('帐号、密码不能为空!',{icon:2});
		}else{
			$.getJSON(cscms_loginaddlink+"?username="+encodeURIComponent(name)+"&userpass="+encodeURIComponent(pass)+"&random="+Math.random()+"&callback=?",function(data) {
				if(data['msg']=='ok'){ //登入成功
					cscms.login();
					$('#loginmask').hide();
					$('#plKuang').show();
					$('#plLogin').hide();
				} else { 
					cscms.layer.msg(data['msg'],{icon:2});
				}
			});
		}
	}
	//退出登录
	,logOut : function(){
	     $.getJSON(cscms_logoutlink+"?callback=?",function(data) {
	           if(data['msg']=='ok'){
	                cscms.login();
	           } else {
	                cscms.layer.msg('网络故障，连接失败!',{icon:2});
	           }
	     });
	}
	//评论列表
	,pl : function(_pages,_id,_fid){
	    $.getJSON(cscms_path+"index.php/pl/index/"+dir+"/"+did+"/"+cid+"/"+_pages+"?random="+Math.random()+"&callback=?",function(data) {
            if(data['str']){
                $("#cscms_pl").html(data['str']);
                if(cscms_zd>0){
                   cscms.scroll('cscms_pl');
                }
                cscms_zd=1;
            } else {
                $("#cscms_pl").html("您请求的页面出现异常错误");
            }
	    });
	}
	//提交评论
	,plAdd : function(){//评论
		var neir=$("#cscms_pl_content").val();
		var token=$("#pl_token").val();
		if(neir==""){
			cscms.layer.msg('评论内容不能为空!',{icon:2});
		}else{
			$.getJSON(cscms_path+"index.php/pl/add?dir="+dir+"&token="+token+"&did="+did+"&cid="+cid+"&neir="+encodeURIComponent(neir)+"&random="+Math.random()+"&callback=?",function(data) {
				var msg=data['msg'];
				if(msg == "ok"){
					cscms.pl(1,0,0);
				} else {
					cscms.layer.msg(msg,{icon:2});
				}
			});
		}
	}
	//评论回复
	,plhfAdd : function (_id,_text){
		var neir=$("#cscms_pl_hf_"+_id).val();
		if(neir==undefined){
			neir=$("#cscms_pl_content").val();
		}
		var token=$("#pl_token").val();
		if(neir=="" || neir==_text){
			cscms.layer.msg('评论回复内容不能为空!',{icon:2});
		}else{
			var len = _text.length;
			neir = neir.substring(len);
			$.getJSON(cscms_path+"index.php/pl/add?dir="+dir+"&token="+token+"&fid="+_id+"&did="+did+"&cid="+cid+"&neir="+encodeURIComponent(neir)+"&random="+Math.random()+"&callback=?",function(data) {
				var msg=data['msg'];
				if(msg == "ok"){
					cscms.pl(1,0,0);
				} else {
					cscms.layer.msg(msg,{icon:2});
				}
           });
		}
	}
	//删除自己的评论
	,plDel : function(_id){
		var token=$("#pl_token").val();
		cscms.layer.confirm('确定要删除该条评论吗？', {
		    btn: ['确定', '取消'] //按钮
		}, function(index) {
		    $.getJSON(cscms_path+"index.php/pl/del?id="+_id+"&token="+token+"&callback=?",function(data) {
           		var msg=data['msg'];
				if(msg == "ok"){
					cscms.pl(1,0,0);
				} else {
					cscms.layer.msg(msg,{icon:2});
				}
			});
			cscms.layer.close(index);
		}, function(index) {
		    cscms.layer.close(index);
		});
	}
	//提交留言
	,postGbook : function(){
		var token = $("#gbook_token").val();
		var neir = $("#cscms_gbook_content").val();
		if(neir == ""){
			cscms.layer.msg('内容不能为空!',{icon:2});
		} else {
			$.post(cscms_path+"index.php/gbook/add",{token: token,neir: encodeURIComponent(neir)},function(data) {
				var msg=data['msg'];
				if(msg == "ok"){
					cscms.getlGbook(1,0,0);
				} else {
					cscms.layer.msg(msg);
				}
			},"json");
		}
	}
	//留言列表
	,getlGbook : function(_pages,_id,_fid){
	    $.getJSON(cscms_path+"index.php/gbook/lists/"+_pages+"?random="+Math.random()+"&callback=?",function(data) {
			if(data['str']){
				$("#cscms_gbook").html(data['str']);
				if(cscms_zd>0){
					cscms.scroll('cscms_gbook');
				}
				cscms_zd=1;
			} else {
				$("#cscms_gbook").html("您请求的页面出现异常错误");
			}
	    });
	}
	//会员主页留言列表
	,home_gbook : function(_pages){
		$.getJSON(cscms_path+"index.php/home/gbook/ajax/"+uid+"/"+_pages+"?random="+Math.random()+"&callback=?",function(data) {
			if(data['str']){
				$("#cscms_gbook").html(data['str']);
				if(cscms_zd>0){
				   cscms.scroll('cscms_gbook');
				}
				cscms_zd=1;
			} else {
				$("#cscms_gbook").html("您请求的页面出现异常错误");
			}
		});
	}
	//提交会员主页留言
	,home_gbookadd : function (){
		var neir=$("#cscms_gbook_content").val();
		var token=$("#gbook_token").val();
		if(neir==""){
			cscms.layer.msg('留言内容不能为空!',{icon:2});
		}else{
			$.getJSON(cscms_path+"index.php/home/gbook/add?token="+token+"&uid="+uid+"&neir="+encodeURIComponent(neir)+"&random="+Math.random()+"&callback=?",function(data) {
			    if(data['msg'] == 'ok'){
			       cscms.layer.msg('恭喜您，留言成功！',{icon:1});
			       cscms.home_gbook(1);
			    } else {
			       cscms.layer.msg(data['msg'],{icon:2});
			    }
			});
		}
	}
	//会员主页留言回复
	,home_gbookhf : function(_id,_text){
		var neir=$("#cscms_gbook_hf_"+_id).val();
		var token=$("#gbook_token").val();
		if(neir=="" || neir==_text){
			cscms.layer.msg('回复内容不能为空!',{icon:2});
		}else{
		    $.getJSON(cscms_path+"index.php/home/gbook/add?token="+token+"&fid="+_id+"&uid="+uid+"&neir="+encodeURIComponent(neir)+"&random="+Math.random()+"&callback=?",function(data) {
			    if(data['msg'] == 'ok'){
			       cscms.layer.msg('恭喜您，回复成功！',{icon:1});
			       cscms.home_gbook(1);
			    } else {
			       cscms.layer.msg(data['msg'],{icon:2});
			    }
		    });
		}
	}
	//删除会员主页留言
	,home_gbookdel : function(_id){
	    var token=$("#gbook_token").val();
		cscms.layer.confirm('确定要删除该条留言吗？', {
		    btn: ['确定', '取消'] //按钮
		}, function(index) {
		    $.getJSON(cscms_path+"index.php/home/gbook/del?id="+_id+"&token="+token+"&callback=?",function(data) {
			    if(data['msg'] =='ok'){
			       cscms.layer.msg('恭喜您，删除成功',{icon:1});
			       cscms.home_gbook(1);
			    } else {
			       cscms.layer.msg(data['msg'],{icon:2});
			    }
				cscms.layer.close(index);
			});
		}, function(index) {
		    cscms.layer.close(index);
		});
	}
	//滚动至指定位置
	,scroll : function(id){
		//得到pos这个div层的offset，包含两个值，top和left
	    var scroll_offset = $("#"+id+"").offset();    
		$("body,html").animate({
		   scrollTop:scroll_offset.top  //让body的scrollTop等于pos的top，就实现了滚动   
		},0);
	}
	//复制
	,copy : function (url,id,title){
		cscms_share_url=url;
		cscms_share_id=id;
		cscms_share_title=title;
		cscms.inc_js(cscms_path+'packs/js/jquery.zclip.min.js');
		setTimeout("cscms.copy_to();",500);
	}
	,copy_to : function() {
		var clip = new ZeroClipboard.Client(); // 新建一个对象
		clip.setHandCursor( true );
		clip.setText(cscms_share_url); // 设置要复制的文本。
		clip.addEventListener( "mouseUp", function(client) {
			cscms.layer.msg(cscms_share_title);
		});
		clip.glue(cscms_share_id); // 和上一句位置不可调换
		return true;
	}
	//异步加载JS
	,inc_js : function(path) { 
		var sobj = document.createElement('script'); 
		sobj.type = "text/javascript"; 
		sobj.src = path; 
		var headobj = document.getElementsByTagName('head')[0]; 
		headobj.appendChild(sobj); 
	}
	//上传
	,getUpload : function(url,id,elem,accept,sign){
		id = (id==''||id==undefined)?'#pic':'#'+id;
		elem = (elem==''||elem==undefined)?'#pics':'#'+elem;
		accept = (accept==''||accept==undefined)?'jpg|jpeg|png':accept;
		if(accept=='images') accept = 'gif|png|jpg|jpeg';
		if(accept=='audio') accept = 'mp3|ogg';
		if(accept=='video') accept = 'mp4|avi|mpg|flv';
		sign = (sign==''||sign==undefined)?0:sign;
		var myfile = 'file';
		if(sign==2) myfile = 'Filedata';
		cscms.upload.render({
            url: url
            ,elem:elem
            ,field:myfile
            ,exts: accept
            ,done: function(res){
                if(res.error==0){
                	if(sign==0){
                		$(id).val(res.info.url);
                	}else if(sign==1){
                		var html = $(id).val();
                		if(html==''){
                			html = res.info.url;
                		}else{
                			html += "\n" + res.info.url;
                		}
                		$(id).val(html);
                	}else{
                		$('#ms-logo').attr('src', res.info.url);
                        $('#mylogo').attr('src', res.info.url);
                	}
                }else{
                    cscms.layer.msg(res.info);
                }
            }
        });
	}
	//时间选择
	,getTime : function(elem,type){
		elem = (elem==''||elem==undefined)?'#kstime':'#'+elem;
		type = (type==''||type==undefined)?'date':type;
		cscms.laydate.render({
			elem:elem
			,type:type
		});
	}
	//自定义字段富文本上传
	,getEditup : function(dir,ac,upkey){
		cscms.layedit.set({
			uploadImage: {
				elem: 'Filedata',
				url: cscms_path+'index.php/upload/up_save_json?dir='+dir+'&key='+upkey
			}
		});
		var gca = cscms.layedit.build(ac, {
			height: 160
		});
		cscms.form.verify({
			content: function(value) {
				layedit.sync(gca);
			}
		});
	}
	//异步执行函数
	,mode : function(m) {
		var mctime = setInterval(function(){
			if(cscms.layer !=null && cscms.layer.tips != undefined){
				eval(m);
				clearInterval(mctime);
			}
		},100);
	}
}
//默认加载全局
$(document).ready(function() {
	if(typeof(cscms_path) !== "undefined"){
		cscms.init();
	}
	$('.share li').click(function(){
		var title=$('#showbg').attr('data-title');
		var url=$('#showbg').attr('data-url');
		var ac=$(this).attr('class');
		url_share(ac,title,url);
	});
});

function shareOpen(title,url){
	$('#showbg').attr('data-title',title).attr('data-url',url);
	$('#showbg').css({width:$(window).width(),height:document.body.scrollHeight});
	$('#showbg').show(); 
	$('.popup').show();
}
function shareClose(){
	$('#showbg').css({width:0,height:0});
	$('#showbg').hide(); 
	$('.popup').hide();
}

function url_share(ac,title,url) {
	if(ac=='share_qzone'){
        var url="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url="+encodeURI(url)+"&title="+encodeURI('好东西分享给你，--《'+title+'》地址：');
        } else if(ac=='share_qwei'){
        	var url="http://share.v.t.qq.com/index.php?c=share&a=index&appkey=&url="+encodeURI(url)+"&title="+encodeURI('好东西分享给你，--《'+title+'》地址：')+"&site=";
        } else if(ac=='share_weibo'){
        	var url="http://service.weibo.com/share/share.php?appkey=&title="+encodeURI('好东西分享给你，--《'+title+'》地址：')+"&url="+encodeURI(url);
        } else if(ac=='share_baidu'){
        	var url="http://tieba.baidu.com/f/commit/share/openShareApi?url="+encodeURI(url)+"&title="+encodeURI('好东西分享给你，--《'+title+'》地址：'+url)+"&desc="+encodeURI('好东西分享给你，--《'+title+'》地址：'+url)+"&comment="+encodeURI('好东西分享给你，--《'+title+'》地址：'+url);
        } else if(ac=='share_renren'){
        	var url="http://widget.renren.com/dialog/share?resourceUrl="+encodeURI(url)+"&title="+encodeURI(title)+"&description="+encodeURI('好东西分享给你，--《'+title+'》地址：'+url)+"&srcUrl="+encodeURI(url);
        } else if(ac=='share_kaixin'){
        	var url="http://www.kaixin001.com/rest/records.php?url="+encodeURI(url)+"&style=11&content="+encodeURI('好东西分享给你，--《'+title+'》地址：')+"&stime=&sig=";
	};
	window.open(url,'share');
}
