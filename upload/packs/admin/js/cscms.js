/**
 * Cscms后台脚本
 * www.chshcms.com
 */
var CSCMSAPI   = 'http://upgrade.chshcms.com/';
var gc;
var layid = 1;//tab的ID
var vcls_id = 0;
var vcls_type ='';
var gctime = 1;
var cscms = {
	jq   		: null,
	wap  		: navigator.userAgent.match(/iPhone|Android|Linux|iPod/i) != null,
	element		: null,
	layer       : null,
	menu_json   : null,
	upload      : null,
	layedit     : null,
	laydate     : null,
	exitindex   : null,
	form        : null,
	init  : function(sign){
		//1->index侧边栏，2->板块检测，3->main网站信息
		//初始化
		layui.use(['element','jquery','form','layer','upload','layedit','laydate'], function(){
			var element = layui.element
			,jquery = layui.jquery
			,form = layui.form
			,upload = layui.upload
			,layer = layui.layer
			,laydate = layui.laydate
			,layedit = layui.layedit;
			if(!cscms.layedit) cscms.layedit = layedit;
			if(!cscms.element) cscms.element = element;
			if(!cscms.layer) cscms.layer = layer;
			if(!cscms.laydate) cscms.laydate = laydate;
			if(!cscms.upload) cscms.upload = upload;
			if(!cscms.form) cscms.form = form;
			if(sign==5){
				layedit.set({
				  uploadImage: {
				  	elem:'Filedata',
				    url: parent.web_path+parent.web_self+'/upload/up_save_json?dir=lrc'
				  }
				});
				gc = layedit.build('text',{height: 160 }); //建立编辑器
				form.verify({
				    content: function(value){
				      layedit.sync(gc);
				    }
				});
			}
			//监听登录表单提交
			form.on('submit(formDemo)', function(data){
				var index = layer.load(1);
				var backurl = $('.layui-form').attr('backurl');
				$.post(data.form.action, data.field, function(data) {
					if(data.error == 0){
						layer.msg('恭喜你，登陆成功！',{icon:1});
						if(backurl==''){
							setTimeout(function(){
								window.location.href = data.info.url;
							},1500);
						}else{
							setTimeout(function(){
								window.location.href = backurl;
							},1500);
						}
					}else{
						if(data.error==3){
							window.location.href = data.info.url;
						}else{
							layer.msg(data.info,{icon:2});
							if(typeof(data.info.url) != undefined && data.info.url != ''){
								setTimeout(function(){
									window.location.href = backurl;
								},1500);
							}
						}
					}
					layer.close(index);
				},"json");
				return false;
			});
			//监听网站配置/修改资料表单提交
			form.on('submit(setting)', function(data){
				var index = layer.load(1);
				if(sign==5){
					layedit.sync(gc);
					for (var u = 1; u < gctime; u++) {
						var text = layedit.getContent(eval('gc'+u));
						var gcname = eval('gc'+u+'_name');
						data.field[gcname] = text;
					}
				}
				$.post(data.form.action, data.field, function(data) {
					if(data.error == 0){
						if(typeof(data.info.iframe) != undefined && data.info.iframe == 1){
							layer.msg(data.info.msg,{icon:1});
							setTimeout(function(){
								var index2 = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
								parent.layer.close(index2); //再执行关闭   
							},1500);
						}else if(typeof(data.info.sign) != undefined && data.info.sign == 'mail'){
							layer.msg(data.info.msg,{icon:1});
							if(cscms.wap){
								var w = '90%';
							}else{
								var w = '500px'
							}
							layer.open({
								type: 1,
								title:'发送结果',
								area: [w, '300px'],
								content: '<div style="padding:10px">'+data.info.mailres+'</div>'
							});

						}else{
							layer.msg('恭喜你，操作成功！',{icon:1});
							if(typeof(data.info.url) != undefined && data.info.url != ''){
								if(typeof(data.info.time) != undefined && data.info.time !== ''){
									var times = data.info.time;
								}else{
									var times = 1500;
								}
								setTimeout(function(){
									if(typeof(data.info.parent) != undefined && data.info.parent == 1){
										parent.location.href = data.info.url;
									}else{
										location.href = data.info.url;
									}
								},times);
							}else{
								if(typeof(data.info.parent) != undefined && data.info.parent == 1){
									parent.location.href = data.info.url;
								}else{
									location.href = data.info.url;
								}
							}
						}
					}else{
						layer.msg(data.info,{icon:2});
					}
					layer.close(index);
				},"json");
				return false;
			});
			//监听批量删除提交
			form.on('submit(del_pl)', function(data){
				if(JSON.stringify(data.field).length<3){
					layer.msg('未选中要删除的数据...',{icon:7});return;
				}
				layer.confirm('确定要删除这些数据吗？', {
					title:'批量删除提示',
				    btn: ['确定', '取消'], //按钮
				    shade:0.001
				}, function(index) {
				    var index = layer.load(1);
					$.post(data.form.action, data.field, function(data) {
						if(data.error == 0){
							layer.msg('恭喜你，操作成功！',{icon:1});
							setTimeout(function(){
								if(typeof(data.info.parent) != undefined && data.info.parent == 1){
									parent.location.href = data.info.url;
								}else{
									location.href = data.info.url;
								}
							},1500);
						}else{
							layer.msg(data.info,{icon:2});
						}
						layer.close(index);
					},"json");
					return false;
				}, function(index) {
				    cscms.layer.close(index);
				});
			});
			//监听switch全反选
			form.on('checkbox(selall)', function(data){
			  	var val = data.value;
			  	var obj = $('#sys_'+val+' input');
			  	for (var i = 0; i < obj.length-1; i++) {
			  		obj[i].checked = (obj[i].checked) ? false : true;
			  	}
			  	form.render('checkbox');
			});
			//监听批量修改
			form.on('checkbox(pledit)', function(data){
		  		var name = data.value;
			  	if(data.elem.checked){
			  		$('[name='+name+']').removeAttr('disabled');
			  		$('[name='+name+']').addClass('ok');
			  	}else{
			  		var name = data.value;
			  		$('[name='+name+']').attr('disabled',"disabled");
			  		$('[name='+name+']').removeClass('ok');
			  	}
			  	form.render();
			});
			//监听select选择
			form.on('select(plugins)', function(data){
				var cls = data.elem.name;
				if(cls.indexOf('table_') != -1){
					$.get(parent.web_path+parent.web_self+'/label/fields?table='+data.value, function(data) {
						$('#field').html(data);
						form.render('select');
					});
					return false;
				}
			  	$('.'+cls).hide();
			  	$('.'+cls+data.value).show();
			});
			//监听模版标签选择
			form.on('select(table)', function(data){
				$('.tab-mx').hide();
			  	$('#tab-'+data.value).show();
			});
			//监听视频类型
			form.on('select(gettype)', function(data){
				var id = data.value;
				if(id>0 && id!=vcls_id){
					vcls_id = id;
					cscms.go_vod_type(id);
				}
			});
			//监听user-vip选择
			form.on('checkbox(uservip)', function(data){
			  	if(data.elem.checked){
			  		$('#uservip').show();
			  	}else{
			  		$('#uservip').hide();
			  	}
			}); 
			//监听自定义字段类型选择
			form.on('select(field)', function(data){
				var cls = data.value;
				$('.field').hide();
				$('.'+cls).show();
			});
			form.on('select(field_number)', function(data){
				var cls = data.value;
				if(cls!='float' && cls!='decimal'){
					$('.dot').hide();
				}else{
					$('.dot').show();
				}
				if(cls=='tinyint') $('#range_num').html('0~127');
				if(cls=='int') $('#range_num').html('0~2^31-1');
				if(cls=='float') $('#range_num').html('0~大于2^31');
				if(cls=='decimal') $('#range_num').html('0~大于2^31');
			});
			form.on('select(field_regexp)', function(data){
				var cls = data.value;
				$('#regexp').val(cls);
			});
			//监听左侧菜单点击
			element.on('nav(test)', function(data){
				var cls = data[0]['firstChild']['firstChild']['className'];
				var _href = data[0]['firstChild']['attributes']['_href']['nodeValue'];
				//边栏主页生成//静态板块主页生成
				if( (cls != undefined && cls.indexOf('tip') !== -1)){
					$.get(data[0]['firstChild']['attributes']['_href']['nodeValue'], function(data) {
						if(data.info.msg != undefined){
							cscms.layer.msg(data.info.msg,{
								icon:data.info.icon,time:1500
							});
						}
						if(data.info.url != undefined){
							setTimeout(function(){
								location.href = data.info.url;
							},1500);
						}
					},"json");
					return false;
				}
				var exist = 0;
				var len = $('.layui-tab-title').children('li').length;
				for (var i = 0; i < len; i++) {
					var src = $('.layui-tab-title').children('li').eq(i).children('a').attr('_href');
					var slayid = $('.layui-tab-title').children('li').eq(i).attr('lay-id');
					var def = data[0]['firstChild']['attributes']['_href']['nodeValue'];
					if(src == def){
						exist = slayid;
					}
				}
				if(exist == 0){
					element.tabAdd('demo',{
					 	title: data[0]['innerHTML']
					  	,content: '<iframe id="iframe_'+layid+'" src="'+data[0]['firstChild']['attributes']['_href']['nodeValue']+'" style="height: 159px;"></iframe>'
					  	,id:layid
					});
					element.tabChange('demo', layid);layid++;
				}else{
					element.tabChange('demo', exist);
					if(data[0]['firstChild']['attributes']['_href']['nodeValue'].indexOf('?')!=-1){
						$('#iframe_'+exist).attr('src', data[0]['firstChild']['attributes']['_href']['nodeValue']+'&v='+parseInt(Math.random()*1000));
					}else{
						$('#iframe_'+exist).attr('src', data[0]['firstChild']['attributes']['_href']['nodeValue']+'?v='+parseInt(Math.random()*1000));
					}
				}
				cscms.iframe_resize();
				if(cscms.wap){
					cscms.get_menu_hide();
				}
			});
			element.on('tab(demo)', function(data){
				var len = $('.layui-tab-title').children('li');
				var iframeid = len[data.index]['attributes']['lay-id']['value'];
			    cscms.iframe_resize();
			    $('.refresh_iframe').attr('iframe', iframeid);
			});
		});
		//页面加载完成
		window.onload=function(){
			if(sign==1){//默认加载边栏
				cscms.get_menu('index');
				if(plub_install == 0){
					if(cscms.wap){
						var w = '95%',h = '95%';
					}else{
						var w = '60%',h = '60%';
					}
					cscms.layer.open({
						title:'初始化板块，不要关闭窗口~!',
						type: 2,
						area: [w, h],
						closeBtn: 0, //不显示关闭按钮
						shade: 0.5,
						shadeClose: false, //开启遮罩关闭
						content: web_path+web_self+'/opt/init'
					});
				}
			    $.getScript(CSCMSAPI+"ajax/ver?code="+code);
			}
			//通用日期时间选择
			$('.datetime').on('click', function () {
				cscms.laydate.render({
					elem:this,
					type:'datetime'
				});
			});
		    //边栏菜单折叠设置
		    $('.toggle').click(function() {
		    	cscms.get_menu_hide();
		    });
		    //点击刷新按钮
		    $('.refresh_iframe').click(function(event) {
		    	var iframe_index = $(this).attr('iframe');
		    	var ifram_src = "";
		    	if($('#iframe_'+iframe_index).attr('src').indexOf('v=')!=-1){
		    		var vlen = $('#iframe_'+iframe_index).attr('src').indexOf('v=');
		    		ifram_src = $('#iframe_'+iframe_index).attr('src').substring(0,vlen+2)+parseInt(Math.random()*1000);
		    	}else{
		    		if($('#iframe_'+iframe_index).attr('src').indexOf('?')!=-1){
		    			ifram_src = $('#iframe_'+iframe_index).attr('src')+'&v='+parseInt(Math.random()*1000);
		    		}else{
		    			ifram_src = $('#iframe_'+iframe_index).attr('src')+'?v='+parseInt(Math.random()*1000);
		    		}
		    	}
		    	$('#iframe_'+iframe_index).attr('src', ifram_src);
		    });
		    $('.del_iframe').click(function(event) {
		    	cscms.element.tabChange('demo', 0);
		    	for (var i = 1; i < layid+1; i++) {
		    		cscms.element.tabDelete('demo', i);
		    	}
	    		$('#iframe_0').parent().siblings().remove();
		    	layid = 1;
		    });
		    //iframe 的高度
		   	cscms.iframe_resize();
		   	if(sign != 1){
				var bwidth = (parseInt(parent.$('#body-main').width())-30)+'px';
	            $('html,body').css('width', bwidth);
			}
			$(window).resize(function(){
				cscms.iframe_resize();
				if(sign != 1){
					var bwidth = (parseInt(parent.$('#body-main').width())-30)+'px';
		            $('html,body').css('width', bwidth);
				}
			});
			if(sign==2){
				$.getScript(CSCMSAPI+"ajax/news");
			}
			if(sign==4){
				var strs=mid.split("#");
				var strs2=v.split("#");
				for (var i = 1; i < strs.length; i++) {
				    cscms.get_skins_up(strs[i],strs2[i]);
				}
			}
			if(sign==6){
				cscms.layer.msg('没有操作权限',{icon:2,time:0});
			}
		}
	},
	//根据浏览器变化调整iframe框架大小
	iframe_resize: function(){
		if(cscms.wap){
			var sheight = window.screen.height-185;
		}else{
			var sheight = $(document).height()-185;
		}
		$('iframe').each(function(){
			$(this).css('height', sheight);
		});
	}
	//边栏菜单折叠设置
	,get_menu_hide : function(){
    	var sideWidth = $('.layui-side').width();
    	if(sideWidth === 180) {
    		if(cscms.wap){
    			$('.layui-body').animate({left: '0px'});
    		}else{
    			$('.layui-body').animate({left: '10px'});
    		}
      		$('.layui-footer').animate({left: '0'});
      		$('.layui-side').animate({width: '0'});
    	} else {
    		if(cscms.wap){
    			$('.layui-body').animate({left: '180px'});
    		}else{
    			$('.layui-body').animate({left: '190px'});
    		}
      		$('.layui-footer').animate({left: '180px'});
      		$('.layui-side').animate({width: '180px'});
    	}
	},
	//边栏数据
	get_menu  : function(dir){
		var jsons = menu_json[dir];
		var html;
		var len = jsons.length;
		for (var i = 0; i < len; i++) {
			var e = jsons[i];
			var ons = e.on ? ' layui-nav-itemed' : '';
			html+='<li class="layui-nav-item'+ons+'"><a href="javascript:;" class="left_li">'+e.title+'<span class="layui-nav-more"></span></a><dl class="layui-nav-child">  ';
			for (var j = 0; j < e['menu'].length; j++) {
				var k = e['menu'][j];
				html+='<dd lay-id="'+dir+'_'+i+'_'+j+'"><a href="javascript:;" _href="'+k.link+'">'+k.name+'</a></dd>';
			}
			html+='</dl></li>';
		}
		$('#left-menu').html(html);
  		cscms.element.init();
  		if(dir != 'index'){
  			$('#left-menu').children('li').eq(0).children('dl').children('dd').eq(0).click();
  		}else{
  			cscms.element.tabChange('demo', 0);
  		}
	},
	//顶部导航栏切换
	nav_dir : function(dir){
		$('#nav_li li').removeClass('layui-this');
		$('#pli_'+dir).addClass('layui-this');
		cscms.get_menu(dir);
	},
	/*板块管理function*/
	plub_opt : function(url,dir,msg){
		if(url==''||dir==''){
			layer.msg('抱歉，参数错误',{icon:2});return;
		}
		if(msg==''){
			$.post(url, {
				dir:dir
			}, function(data) {
				if(data.error==0){
					layer.msg('恭喜你，板块安装成功',{icon:1});
					cscms.get_mjson();
					var swidth = $(document).width();
					var nums = parent.$('#nav_li li').length - 6;

					if((swidth>1024 && nums>4) || (swidth>700 && swidth<1025 && nums>1) || swidth<700){//隐藏
						parent.$('#nav_more').before('<li id="pli_'+dir+'" onclick="cscms.nav_dir(\''+dir+'\')" class="layui-nav-item hide pli_'+nums+'"><a href="javascript:;">'+data.info.name+'</a></li>');
						parent.$('#nav_dd').append('<dd class="pdd_'+nums+'"  id="pdd_'+dir+'" onclick="cscms.nav_dir(\''+dir+'\')"><a href="javascript:;">'+data.info.name+'</a></dd>');
					}else{//显示
						parent.$('#nav_more').before('<li id="pli_'+dir+'" onclick="cscms.nav_dir(\''+dir+'\')" class="layui-nav-item pli_'+nums+'"><a href="javascript:;">'+data.info.name+'</a></li>');
						parent.$('#nav_dd').append('<dd  class="hide pdd_'+nums+'" id="pdd_'+dir+'" onclick="cscms.nav_dir(\''+dir+'\')"><a href="javascript:;">'+data.info.name+'</a></dd>');
					}
					cscms.element.init();
					setTimeout(function(){
						location.reload();
					},1000);
				}else{
					layer.msg(data.info,{icon:2});
				}
			},"json");
		}else{
			layer.confirm(msg, {
				shade:0.001,
			    btn: ['确定', '取消'] //按钮
			}, function() {
			    $.post(url, {
					dir:dir
				}, function(data) {
					if(data.error==0){
						//console.log(data.info);
						if(data.info.func=='clear'){
							layer.msg('恭喜你，板块初始化成功！',{icon:1});
						}
						if(data.info.func=='uninstall'){
							layer.msg('恭喜你，板块卸载成功！',{icon:1});
							parent.$('li[onclick="cscms.nav_dir(\''+dir+'\')"]').remove();
							parent.$('dd[onclick="cscms.nav_dir(\''+dir+'\')"]').remove();
							location.reload();
						}
						if(data.info.func=='del'){
							layer.msg('恭喜你，该板块已删除！',{icon:1});
							$('#'+dir+'_p').remove();
						}
					}else{
						layer.msg(data.info,{icon:2});
					}
				},"json");
			}, function(index) {
			    layer.close(index);
			});
		}
	},
	//获取视频类别
	go_vod_type: function (id){
		$('#type').html('<i class="fa fa-spin fa-spinner"></i>');
		$.get(parent.web_path+parent.web_self+"/vod/admin/vod/type_init?id="+id+"&type="+vcls_type, function(data) {
			if(data.error==0){
				$("#type").html('');
				r = data.info;
				//console.log(r);
				if(r.length>0){
                    $.each(r, function(i,row){
                        chk = row.chk=='true' ? 'checked' : '';
                        $("#type").append('<input type="checkbox" lay-skin="primary" name="type['+i+']" title="'+row.name+'" value="'+row.name+'" '+chk+' />&nbsp;');
                  	}); 
                }
                cscms.form.render('checkbox');
			}else{
				cscms.layer.msg(data.info,{icon:2});
			}
		},"json");
	},
	//获取边栏json数据
	get_mjson: function (){
	  	$.get(parent.web_path+parent.web_self+'/index/index/json', function(data) {
	  		cscms.menu_json = data;
	  	},"json");
	},
	//页码跳转
	goto_page : function (url){
		var page = $('#goto_page').val();
		location.href = url+page;
	},
	//系统更新提示
	upgrade : function(neir){
		cscms.layer.confirm(neir, {
			title:'系统更新提示',
		    btn: ['确定更新', '下次更新'] //按钮
		}, function(index) {
		    window.open(web_path+web_self+'/upgrade','iframe_0');
		    cscms.layer.close(index);
		}, function(index) {
		    cscms.layer.close(index);
		});
	},
	//编辑友情链接
	get_open : function(url,title,w,h){
		if(cscms.wap){
			w = '95%';
			h = arguments[4]?arguments[4]:'98%';
		}
		cscms.layer.open({
			title:title,
			type: 2,
			area: [w, h],
			closeBtn: 1, //不显示关闭按钮
			shade: false,
			shadeClose: true, //开启遮罩关闭
			content: url
		});
	},
	//全选/反选
	select_all : function(){
		var a=$(".xuan");  
	    for (var i = 0; i < a.length; i++) {
	        a[i].checked = (a[i].checked) ? false : true;
	    }
	},
	//单一删除
	del_one : function(url,id){
		if(arguments[2]){
			var title = arguments[2];
		}else{
			var title = '确定要删除该列数据吗？';
		}
		if(!id){
			cscms.layer.msg('参数错误，请刷新重试',{icon:2});
		}else{
			cscms.layer.confirm(title, {
				title:'删除提示',
			    btn: ['确定', '取消'], //按钮
			    shade:0.001
			}, function(index) {
			    $.post(url, {
					id:id
				}, function(data) {
					if(data.error == 0){
						layer.msg('恭喜你，删除成功',{icon:1});
						$('#row_'+id).remove();
						if(typeof(data.info.turn) != undefined && data.info.turn ==1){
							setTimeout(function(){
								location.href = data.info.url;
							},1500);
						}
					}else{
						layer.msg(data.info,{icon:2});
					}
				},"json");
			}, function(index) {
			    cscms.layer.close(index);
			});
		}
	}
	//修改状态sign->1-ok,0-no
	,ok_no :function(url,id,sign,td_){
		var sign2 = 1;
		var nxt = arguments[4] ? arguments[4] : 0;
		var td_src = $('#'+td_+id).html();
		$('#'+td_+id).html('<i class="fa fa-spin fa-spinner"></i>');
		$.post(url, {
			id:id,sign:sign
		}, function(data) {
			if(data.error==0){
				cscms.layer.msg('恭喜你，操作成功',{icon:1,time:1000});
				if(sign>0){
					sign2 = 0;
					if(td_=='zt_'){
						$('#'+td_+id).html('<i class="fa fa-check colorl" ></i>');
					}else{
						$('#'+td_+id).html('<i class="fa fa-close " style="color: red" ></i>');
					}
					
				}else{
					if(td_=='zt_'){
						$('#'+td_+id).html('<i class="fa fa-close " style="color: red" ></i>');
					}else{
						$('#'+td_+id).html('<i class="fa fa-check colorl" ></i>');
					}
					
				}
				if(nxt == 'label'){
					$('#'+td_+id).attr('onclick', '');
				}else{
					$('#'+td_+id).attr('onclick', 'cscms.ok_no(\''+url+'\','+id+','+sign2+',\''+td_+'\')');
				}
			}else{
				cscms.layer.msg(data.info,{
					icon:2
					,time: 2000
				});
				$('#'+td_+id).html(td_src);
			}
		},"json");
	}
	//sign: 0绿色对号，1红色叉号,2删除
	,ok_no2 :function(url,id,sign,td_){
		var td_src = $('#'+td_+id).html();
		$('#'+td_+id).html('<i class="fa fa-spin fa-spinner"></i>');
		$.post(url, {
			id:id,sign:sign
		}, function(data) {
			if(data.error==0){
				cscms.layer.msg('恭喜你，操作成功',{icon:1,time:1000});
				$('#row_'+id).remove();
			}else{
				cscms.layer.msg(data.info,{icon:2,time: 2000});
				$('#'+td_+id).html(td_src);
			}
		},"json");
	}
	//显示和隐藏
	,show_hide : function(cls,name){
		var sign = arguments[2]?arguments[2]:0;
		if(sign==0){
			var xuan = $('input[name='+name+']:checked').val();
		}else{
			var xuan = $('input[sign='+name+']:checked').val();
		}
		$('.'+cls).hide();
		$('.'+cls+xuan).show();
	}
	//手机短信跳转前判断提示
	,tips_goto : function(url){
		$.get(url, function(data) {
			if(data.error==0){
				location.href = url+'?sign=ok';
			}else{
				cscms.layer.msg(data.info,{icon:2});
			}
		},"json");
	}
	,go_url : function(url){
		$.get(url, function(data) {
			if(data.error==0){
				if(data.info.alert!=undefined && data.info.alert==1){
					cscms.layer.alert(data.info.msg, {
					    closeBtn: 0
					    ,shade:0.001
					});
				}else{
					cscms.layer.msg(data.info.msg,{icon:1,time:1000});
				}
				if(data.info.url != undefined && data.info.url != ''){
					setTimeout(function(){
						location.href = data.info.url;
					},1500);
				}
			}else{
				cscms.layer.msg(data.info,{icon:2});
			}
		},"json");
	}
	//操作提交数据反馈结果
	,go_confirm : function(url,title){
		var sign = arguments[2]?arguments[2]:0;
		cscms.layer.confirm(title,{
			title:'提示',
	    	btn: ['确定', '取消'], //按钮
	    	shade:0.001
		},function(){
			if(sign==1){
				cscms.sel_submit(url);
			}else{
				if(sign==2){
					cscms.sel_submit(url,sign);
				}else{
					cscms.go_url(url);
				}
			}
		},function(index){
			cscms.layer.close(index);
		});
	}
	//确认后提交数据
	,confirm_url : function(url,notice){
		cscms.layer.confirm(notice, {
			title:'提示',
		    btn: ['确定', '取消'], //按钮
		    shade:0.001
		}, function(index) {
			cscms.layer.confirm('<font color="red">确定备份过了吗？</font>',{
				title:'注意',
				btn: ['确定', '取消'], //按钮
			    shade:0.001
			},function(index2){
				var loading = layer.load(1);
			    $.get(url, function(data) {
					if(data.error == 0){
						layer.msg(data.info.msg,{icon:1});
						setTimeout(function(){
							location.href = data.info.url;
						},1500)
					}else{
						layer.msg(data.info,{icon:2});
					}
					cscms.layer.close(loading);
				},"json");
			},function(index2){
				cscms.layer.close(index2);
			});
		}, function(index) {
		    cscms.layer.close(index);
		});
	}
	//选中提交
	,sel_submit : function(url){
		var arr = new Array();
		var a = $(".xuan");  
	    for (var i = 0; i < a.length; i++) {
	    	if(a[i].checked){
	    		arr.push(a[i].value);
	    	}
	    }
	    var cid = 0;
	    if(arguments[1]){
	    	cid = $('#zhuan option:selected') .val();//选中的值
	    }
		$.post(url,{id:arr,cid:cid}, function(data) {
			if(data.error==0){
				if(data.info.alert!=undefined && data.info.alert==1){
					cscms.layer.alert(data.info.msg, {
					    closeBtn: 0
					    ,shade : 0.01
					},function(){
						location.href = data.info.url;
					});
				}else{
					cscms.layer.msg(data.info.msg,{icon:1,time:1500});
					setTimeout(function(){
						location.href = data.info.url;
					},1500);
				}
				
			}else{
				cscms.layer.msg(data.info,{icon:2});
			}
		},"json");
	}
	//选中生成
	,go_html : function(url){
		var arr = new Array();
		var a = $(".xuan");  
	    for (var i = 0; i < a.length; i++) {
	    	if(a[i].checked){
	    		arr.push(a[i].value);
	    	}
	    }
	    if (1 > arr.length){ 
            cscms.layer.msg('请选择要生成的数据！',{icon:2});
        }else{
			if($('#html').length > 0){
				$('#html').html('<a class="layui-btn layui-btn-primary layui-btn-small" href="">生成中...</a>');
			}
            location.href = url+'?ac=pc&ids='+arr.join(',');
        }
	}
	//备份表结构
	,backup : function(url){
		var arr = new Array();
		var a = $(".xuan");  
	    for (var i = 0; i < a.length; i++) {
	    	if(a[i].checked){
	    		arr.push(a[i].value);
	    	}
	    }
		$.post(url,{
			table:arr
		}, function(data) {
			if(data.error==0){
				cscms.layer.msg(data.info.msg,{icon:1,time:1500,shade:0.3});
				setTimeout(function(){
					cscms.backup_data(url,arr,data.info.bkdir,0,(arr.length-1));
				},1000);
			}else{
				cscms.layer.msg(data.info,{icon:2,shade:0.3});
			}
		},"json");
	}
	//备份表数据
	,backup_data : function(url,table,bkdir,len,nums){
		var ok = len==nums ? 1 : 0;
		$.post(url,{table:table[len],bkdir:bkdir,ok:ok}, function(data){
			if(data.error==1){
				cscms.layer.msg(data.info,{icon:2,shade:0.3});
			} else {
				cscms.layer.msg(data.info.msg,{icon:1,shade:0.3});
				if(ok==0){
					setTimeout(function(){
						cscms.backup_data(url,table,bkdir,(len+1),nums);
					},1000);
				}
				if(data.info.url != undefined && data.info.url != ''){
					setTimeout(function(){
						location.href = data.info.url;
					},1500);
				}
			}
		},"json");
	}
	//数据还原
	,restore : function(url,dir){
		cscms.layer.confirm('确定要还原该列数据吗？', {
			title:'还原提示',
		    btn: ['确定', '取消'], //按钮
		    shade:0.001
		}, function(index) {
			var loading = layer.load(1);
		    $.post(url, {
				dir:dir
			}, function(data) {
				if(data.error == 0){
					layer.msg('恭喜你，还原成功',{icon:1});
					setTimeout(function(){
						location.href = data.info.url;
					},1500);
				}else{
					layer.msg(data.info,{icon:2});
				}
				cscms.layer.close(loading);
			},"json");
		}, function(index) {
		    cscms.layer.close(index);
		});
	}
	//向id内填充内容
	,get_to_id : function(url,id){
		$.get(url, function(data) {
			$('#'+id).html(data);
		});
	}
	//获取版本信息
	,get_skins_up : function (_mid,_v){
     	$.getJSON(parent.yun_url+"skins/version?id="+_mid+"&v="+_v+"&callback=?",function(data) {
            if(data['str']=='no'){
				$('#update_'+_mid).html('已是最新版本');
            } else {
				$('#update_'+_mid).html('<a href="'+parent.web_path+parent.web_self+'/skin/update?mid='+_mid+'"><font color=red>发现新版本'+data['str']+'</font></a>');
            }
	    });
	}
	,tags_save : function(){
		var table = $('#table').val();
		var mx = $('#mx-'+table).val();
        var loop = $('#loop').val();
        var order = $('input:radio:checked').val();
		if(mx==''){
            cscms.layer.msg('请选择标签类型',{icon:2});
		}else{
			cscms.layer.open({
				title:'生成代码结果',
				type: 2,
				area: ['500px', '400px'],
				closeBtn: 1, //不显示关闭按钮
				shade: 0.01,
				shadeClose: true, //开启遮罩关闭
				content: parent.web_path+parent.web_self+'/skin/tags_save?mx='+mx+'&loop='+loop+'&order='+order
			});
		}
	}
	//采集按钮判断跳转按钮
	,caiji : function(url,url1){
		var loading = cscms.layer.load(1);
		$.get(url, function(data) {
			cscms.layer.close(loading);
			if(data.error==0){
				location.href = url1;
			}else{
				cscms.layer.msg(data.info,{icon:2});
			}
		},"json");
	}
	//删除会员图像
	,logo_del :function(url,id){
		$.get(url, function(data) {
			if(data.error==0){
				parent.$('#logo_'+id).html('-');
				cscms.layer.msg('恭喜你，删除成功',{icon:1});
				setTimeout(function(){
					var index = parent.layer.getFrameIndex(window.name);
					parent.layer.close(index);
				},1500);
			}else{
				cscms.layer.msg(data.info,{icon:2});
			}
		},"json");
	}
	//会员组排序提交
	,sort_submit :function(url){
		var arr = new Array();
		var a = $(".sort");  
	    for (var i = 0; i < a.length; i++) {
	    	var zuid = a[i].getAttribute('zuid');
	    	arr[zuid] = a[i].value;
 	    }
 	    if(arr.length==0){
 	    	cscms.layer.msg('暂无记录',{icon:2});return;
 	    }
 	    var loading = cscms.layer.load(1);
 	    $.post(url,{xid:arr}, function(data) {
 	    	cscms.layer.close(loading);
			if(data.error==0){
				cscms.layer.msg(data.info.msg,{icon:1,time:1500});
				if(data.info.url!=undefined && data.info.url !=''){
					setTimeout(function(){
						location.href = data.info.url;
					},1500);
				}
			}else{
				cscms.layer.msg(data.info,{icon:2});
			}
		},"json");
	}
	//歌曲推荐星级
	,get_tj : function(url,td_,id,sid){
		if(id==0||id=='') cscms.layer.msg('参数错误',{icon:2});
		$.post(url, {
			id:id,sid:sid
		}, function(data) {
			if(data.error==0){
				cscms.layer.msg('恭喜你，操作成功',{icon:1,time:1500});
				var html = '<a title="点击取消推荐" href="javascript:cscms.get_tj(\''+url+'\',\''+td_+'\','+id+',0);"><font color="#ff0033">×</font></a>';
				for(var i=1;i<sid+1;i++){
                    html+='<a title="推荐:'+i+'星" href="javascript:cscms.get_tj(\''+url+'\',\''+td_+'\','+id+','+i+');">★</a>';
                }
                for(var j=sid+1;j<=5;j++){
                    html+='<a title="推荐:'+i+'星" href="javascript:cscms.get_tj(\''+url+'\',\''+td_+'\','+id+','+j+');">☆</a>';
                }
                $('#'+td_+id).html(html);
			}else{
				cscms.layer.msg(data.info,{icon:2});
			}
		},"json");
	}
	//批量修改
	,pl_edit : function(url,title,w,h){
        var arr = new Array();
        var a = $(".xuan");
        for (var i = 0; i < a.length; i++) {
            if(a[i].checked){
                arr.push(a[i].value);
            }
        }
        var id = arr.join(",");
        cscms.get_open(url+id,title,w,h);
    }
    //视频地址校正
    ,repairUrl: function(i,sid){
		if(sid==2){
			var fromText=$("#m_downfrom"+i).val();
			var urlStr=$("#m_downurl"+i).val();
		}else{
			var fromText=$("#m_playfrom"+i).val();
			var urlStr=$("#m_playurl"+i).val();
		}
		if (urlStr.length==0){cscms.layer.msg('请填写地址',{icon:2});return false;}
		var urlArray=urlStr.split("\n");
		var newStr="";
		for(j=0;j<urlArray.length;j++){
			if(urlArray[j].length>0){
				t=urlArray[j].split('$'),flagCount=t.length-1
				switch(flagCount){
					case 0:
						urlArray[j]='第'+(j<9 ? '0' : '')+(j+1)+'集$'+urlArray[j]+'$'+fromText
						break;
					case 1:
						urlArray[j]=urlArray[j]+'$'+fromText
						break;
					case 2:
						if(t[2]==''){
							urlArray[j]=urlArray[j]+fromText
						}else{
						        urlArray[j]=t[0]+'$'+t[1]+'$'+fromText
	                                        }
						break;
				}
				if(urlArray[j].indexOf('qvod://')!=-1){
					var t=urlArray[j].split("$");
					t[1]=t[1].replace(/[\u3016\u3010\u005b]\w+(\.\w+)*[\u005d\u3011\u3017]/ig,'').replace(/(qvod:\/\/\d+\|\w+\|)(.+)$/i,'$1['+(window.siteurl || document.domain)+']$2').replace(/(^\s*)|(\s*$)/ig,'');
					urlArray[j]=t.join("$");
				}
				newStr+=urlArray[j]+"\n";
			}
		}
		if(sid==2){
		     $('#m_downurl'+i).val(cscms.trimOuterStr(newStr,"\n"));
		}else{
		     $("#m_playurl"+i).val(cscms.trimOuterStr(newStr,"\n"));
		}
	}
	,trimOuterStr:function(str,outerstr){
		var len1;
		len1=outerstr.length;
		if(str.substr(0,len1)==outerstr){str=str.substr(len1)}
		if(str.substr(str.length-len1)==outerstr){str=str.substr(0,str.length-len1)}
		return str;
	}
	,tagsSave : function(id,url){
		var xid = parseInt($('#add_xid_'+id).val());
		var name = $('#add_name_'+id).val();
		if(name==''){
			cscms.layer.msg('名称不能为空',{icon:2});return;
		}
		$.post(url, {
			xid: xid,name:name,fid:id
		}, function(data) {
			if(data.error==0){
				cscms.layer.msg('恭喜你，标签添加成功',{icon:1});
				setTimeout(function(){
					location.href = data.info.url;
				},1500);
			}else{
				cscms.layer.msg(data.info,{icon:2});
			}
		},"json");
	}
	,getUpload : function(url,id,elem,accept,sign){
		id = (id==''||id==undefined)?'#pic':'#'+id;
		elem = (elem==''||elem==undefined)?'#pics':'#'+elem;
		accept = (accept==''||accept==undefined)?'images':accept;
		sign = (sign==''||sign==undefined)?0:1;
		if(accept=='images') accept = 'gif|png|jpg|jpeg';
		if(accept=='audio') accept = 'mp3|ogg';
		if(accept=='video') accept = 'mp4|avi|mpg|flv';
		cscms.upload.render({
            url: url
            ,elem:elem
            ,exts: accept
            ,done: function(res){
                if(res.error==0){
                	if(sign==0){
                		$(id).val(res.info.url);
                	}else{
                		var html = $(id).val();
                		if(html==''){
                			html = res.info.url;
                		}else{
                			html += "\n" + res.info.url;
                		}
                		$(id).val(html);
                	}
                }else{
                    cscms.layer.msg(res.info);
                }
            }
        });
	}
	,getTime : function(elem,type){
		elem = (elem==''||elem==undefined)?'#kstime':'#'+elem;
		type = (type==''||type==undefined)?'date':type;
		cscms.laydate.render({
			elem:elem
			,type:type
		});
	}
	,mode : function(m) {
		var mctime = setInterval(function(){
			if(cscms.layer !=null && cscms.layer.tips != undefined){
				eval(m);
				clearInterval(mctime);
			}
		},100);
	}
};
//兼容IE7以下无placeholder属性
$(function(){
  var placeholderSupport = 'placeholder' in document.createElement('input');
  	if(!placeholderSupport){   // 判断浏览器是否支持 placeholder
      $('[placeholder]').focus(function() {
          var input = $(this);
          if (input.val() == input.attr('placeholder')) {
              input.val('');
              input.removeClass('placeholder');
          }
      }).blur(function() {
          var input = $(this);
          if (input.val() == '' || input.val() == input.attr('placeholder')) {
              input.addClass('placeholder');
              input.val(input.attr('placeholder'));
          }
      }).blur();
  	};
})
