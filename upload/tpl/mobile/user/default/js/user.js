/**
 * Cscms会员中心前台
 * www.chshcms.com
 */
var dtid = 0; //0全部动态1我的动态2好友动态
var user = {
    init : function() {
        /*全部动态、好友动态、我的动态切换*/
        cscms.element.on('tab(dt1)', function(data) {
            dtid = data.index;
            cscms.element.tabChange('dt2', 'qu' + dtid);
            cscms.element.init();
        });
        /*不同板块间动态切换*/
        cscms.element.on('tab(dt2)', function(data) {
            $('#dt-list' + dtid).html('<div class="colorl" style="text-align:center;line-height:100px;font-size:30px"><i class="fa fa-spin fa-spinner"></i></div>');
            var myi = 0;
            if (data.index == 1) {
                myi = 100;
            } else {
                if (data.index != 0) myi = data.index - 1;
            }
            var dir = $('#dt' + dtid + '_' + myi).attr('dir');
            var dtid2 = 1;
            if(dtid==1) dtid2 = 3;
            if(dtid==2) dtid2 = 2;
            $.getJSON(cscms_path + "index.php/user/ajax/feed?dir=" + dir + "&cid=" + dtid2 + "&random=" + Math.random() + "&callback=?", function(data) {
                if (data['str']) {
                    $("#dt-list" + dtid).html(data['str']);
                } else {
                    $("#dt-list" + dtid).html('<div style="line-height:100px;text-align:center">您请求的页面出现异常错误</div>');
                }
            });
        });
        /*在线升级监听会员组选择*/
        cscms.form.on('select(selzu)', function(data) {
            var zu = data.value;
            console.log(zu);
            if (zu) {
                $('#time').show();
                var arr = zu.split('-');
                if (arr[1] == 1) {
                    $('#time1').text('升级天数');
                    $('#time2').val('30');
                } else if (arr[1] == 2) {
                    $('#time1').text('升级月数');
                    $('#time2').val('3');
                } else if (arr[1] == 3) {
                    $('#time1').text('升级年数');
                    $('#time2').val('1');
                }
                $('#zid').val(arr[0]);
                $('#type').val(arr[1]);
            }
        });
        /*视频模块播放地址类别*/
        cscms.form.on('select(vodListUser)', function(data) {
            var play=data.value;
            if(play=='flv' || play=='media'){
                $('#vodup').show();
            }else{
                $('#vodup').hide();
            }
        });
    }
    ,caidan : function(){
        if($(".mc-menu").is(":hidden")){
            $(".mc-menu").show();
            $(".mc-mask").show();
        }else{
            $(".mc-menu").hide();
            $(".mc-mask").hide();
        }
    }
    ,mcShow : function(i){
        $('.mc-dl').hide();
        $('.mcdl_'+i).show();
    }
    ,select_all: function() {
        var a = $(".xuan");
        for (var i = 0; i < a.length; i++) {
            a[i].checked = (a[i].checked) ? false : true;
        }
        setTimeout(function() {
            cscms.form.render();
        }, '50');
    }
    ,playsongs: function(n) { //专辑页歌曲播放
        var v = [];　　
        var a = $("input[name='check']");
        for (var i = 0; i < a.length; i++) {
            if (n == 1) {
                if (a[i].checked == true) {
                    var did = a[i].value;
                    v.push(did);
                }
            } else {
                var did = a[i].value;
                v.push(did);
            }
        }
        if (1 > v.length) {
            cscms.layer.msg('请选择要播放的歌曲！', {
                icon: 2
            });
            return;
        } else {
            window.open(cscms_path + 'index.php/dance/playsong?id=' + v.join(','), 'play');
        }
    }
    ,danceFavDel: function(link, i) { //删除歌曲收藏
        cscms.layer.confirm('确定要删除该条收藏吗？', {
            btn: ['确定', '取消'] //按钮
        }, function(index) {
            $.getJSON(link + "?random=" + Math.random() + "&callback=?", function(data) {
                if (data['error'] == 1002) {
                    cscms.layer.msg('您没有权限操作！', {
                        icon: 2
                    });
                    return false;
                } else {
                    cscms.layer.msg('删除成功~!', {
                        icon: 1
                    });
                    $('#myfav_' + i).remove();
                }
            });
            cscms.layer.close(index);
        }, function(index) {
            cscms.layer.close(index);
        });
    }
    ,qiandao: function() {
        $.getJSON(cscms_path + "index.php/user/ajax/qiandao?random=" + Math.random() + "&callback=?", function(data) {
            if (data['error'] == 1001) {
                cscms.layer.msg('您今天已经签到成功，请明天继续！', {
                    icon: 7
                });
                $('#cscms_qd').html('今天已签到');
                return false;
            } else if (data['error'] == 1000) {
                cscms.layer.msg('您登录已经超时！', {
                    icon: 2
                });
                return false;
            } else {
                cscms.layer.msg(data['str'], {
                    icon: 1
                });
                $('#cscms_qd').html('今天已签到');
            }
        });
    },
    blog: function() {
        var $note = $("#note"); //说说内容
        var noteContent = "发一条说说, 让大家知道你在做什么...";
        $note.emotEditor({
            emot: true,
            charCount: true,
            defaultText: noteContent,
            defaultCss: 'default_color'
        });
    },
    blog_send: function() { //会员中心发布说说
        var $note = $("#note"); //说说内容
        var validCharLength = $note.emotEditor("validCharLength");
        if (validCharLength < 1 || $note.emotEditor("content") == "") {
            cscms.layer.msg('请输入说说内容', {
                icon: 2
            });
            $note.emotEditor("focus");
            return false;
        }
        $.getJSON(cscms_path + "index.php/user/ajax/blog?neir=" + $note.emotEditor("content") + "&callback=?", function(data) {
            if (data['error'] == 1001) {
                cscms.layer.msg('请输入说说内容', {
                    icon: 2
                });
                $note.emotEditor("focus");
                return false;
            } else if (data['error'] == 1002) {
                cscms.layer.msg('说说内容不能超过140个字！', {
                    icon: 2
                });
                $note.emotEditor("focus");
                return false;
            } else if (data['error'] == 1000) {
                cscms.layer.msg('您已经登录超时！', {
                    icon: 2
                });
            } else if (data['error'] == 1003) {
                cscms.layer.msg('您操作的太频繁，请稍后再试！', {
                    icon: 2
                });
                $note.emotEditor("focus");
                return false;
            } else if (data['error'] == 1005) {
                cscms.layer.msg('抱歉，发表失败，请稍后再试！', {
                    icon: 2
                });
                $note.emotEditor("focus");
                return false;
            } else {
                cscms.layer.msg('说说发布成功！', {
                    icon: 1
                });
                $note.html('');
				$('.blogInput').val('');
                $note.emotEditor("focus");
                var op = $(".send").attr('op');
                if (op == 'all') {
                    cscms.element.tabChange('dt1', 'qu');
                    cscms.element.tabChange('dt2', 'qu0');
                } else {
                    setTimeout('location.replace(location);', 2000);
                }
            }
        });
        return false;
    },
    blog_del: function(id) { //会员中心，删除说说
        cscms.layer.confirm('确定删除该说说吗？', {
            btn: ['确定', '取消'] //按钮
        }, function(index) {
            cscms.layer.close(index);
            $.getJSON(cscms_path + "index.php/user/ajax/blog_del?id=" + id + "&callback=?", function(data) {
                if (data['error'] == 1001) {
                    cscms.layer.msg('参数错误！', {
                        icon: 2
                    });
                    return false;
                } else if (data['error'] == 1000) {
                    cscms.layer.msg('您登录已经超时！', {
                        icon: 2
                    });
                    return false;
                } else if (data['error'] == 1002) {
                    cscms.layer.msg('您没有权限操作！', {
                        icon: 2
                    });
                    return false;
                } else {
                    cscms.layer.msg('删除成功！', {
                        icon: 1
                    });
                    $('#del' + id).remove();
                }
            });
        }, function(index) {
            cscms.layer.close(index);
        });
    },
    yidu: function() { //全部标记为已读
        $.getJSON(cscms_path + "index.php/user/ajax/msg_du?random=" + Math.random() + "&callback=?", function(data) {
            if (data['error'] == 1000) {
                cscms.layer.msg('您登录已经超时！', {
                    icon: 2
                });
                return false;
            } else {
                cscms.layer.msg('全部标记成功！', {
                    icon: 1
                });
                setTimeout('location.replace(location);', 2000);
            }
        });
    },
    msg_del: function(url, title) { //消息删除按钮
        cscms.layer.confirm(title, {
            btn: ['确定', '取消'] //按钮
        }, function(index) {
            location.href = url;
            cscms.layer.close(index);
        }, function(index) {
            cscms.layer.close(index);
        });
    },
    gbook_init: function(id) { //回复留言开始
        var none = $('#gbook_' + id).css('display');
        if (none == 'none') {
            $("#neir_" + id).emotEditor({
                emot: true
            });
            $('#gbook_' + id).show();
        } else {
            $('#gbook_' + id).hide();
        }
    },
    gbook_add: function(id, uida) { //增加回复留言
        var neir = $("#neir_" + id).emotEditor("content");
        if (neir == "") {
            cscms.layer.msg('回复内容不能为空!', {
                icon: 2
            });
            return false;
        } else {
            $.getJSON(cscms_path + "index.php/user/ajax/gbook_hf?fid=" + id + "&uida=" + uida + "&neir=" + neir + "&random=" + Math.random() + "&callback=?", function(data) {
                if (data['error'] == 1001) {
                    cscms.layer.msg('参数错误！', {
                        icon: 2
                    });
                    return false;
                } else if (data['error'] == 1000) {
                    cscms.layer.msg('您登录已经超时！', {
                        icon: 2
                    });
                    return false;
                } else if (data['error'] == 1002) {
                    cscms.layer.msg('回复内容不能为空！', {
                        icon: 2
                    });
                    return false;
                } else if (data['error'] == 1003) {
                    cscms.layer.msg('该留言已经被删除！', {
                        icon: 2
                    });
                    return false;
                } else if (data['error'] == 1004) {
                    cscms.layer.msg('回复会员不存在！', {
                        icon: 2
                    });
                    return false;
                } else {
                    cscms.layer.msg('恭喜您，留言回复成功', {
                        icon: 1
                    });
                    setTimeout('location.replace(location);', 2000);
                }
            });
        }
    },
    gbook_del: function(id, sign) { //删除留言
        cscms.layer.confirm('确定要删除吗？', {
            btn: ['确定', '取消'] //按钮
        }, function(index) {
            $.getJSON(cscms_path + "index.php/user/ajax/gbook_del?id=" + id + "&random=" + Math.random() + "&callback=?", function(data) {
                if (data['error'] == 1001) {
                    cscms.layer.msg('参数错误！', {
                        icon: 2
                    });
                    return false;
                } else if (data['error'] == 1000) {
                    cscms.layer.msg('您登录已经超时！', {
                        icon: 2
                    });
                    return false;
                } else if (data['error'] == 1002) {
                    cscms.layer.msg('您没有权限操作！', {
                        icon: 2
                    });
                    return false;
                } else {
                    cscms.layer.msg('删除成功~!', {
                        icon: 1
                    });
                    if (sign == 0) { //删除留言以及下面的回复
                        $('#gbooks_' + id).remove();
                        $('#gtype_' + id).remove();
                    } else { //删除回复
                        $('#gbook2_' + id).remove();
                    }
                }
            });
            cscms.layer.close(index);
        }, function(index) {
            cscms.layer.close(index);
        });
    },
    fans_del: function(id, type) { //删除我的关注以及粉丝
        var tit = (type == 'fans') ? '删除' : '解除好友';
        type = (type != 'fans') ? 'friend' : 'fans';
        cscms.layer.confirm('你确定要' + tit + '吗？', {
            btn: ['确定', '取消'] //按钮
        }, function(index) {
            $.getJSON(cscms_path + "index.php/user/ajax/" + type + "_del?id=" + id + "&random=" + Math.random() + "&callback=?", function(data) {
                if (data['error'] == 1001) {
                    cscms.layer.msg('参数错误！', {
                        icon: 2
                    });
                    return false;
                } else if (data['error'] == 1000) {
                    cscms.layer.msg('您登录已经超时！', {
                        icon: 2
                    });
                    return false;
                } else if (data['error'] == 1002) {
                    cscms.layer.msg('您没有权限操作！', {
                        icon: 2
                    });
                    return false;
                } else {
                    cscms.layer.msg(tit + '成功~!', {
                        icon: 1
                    });
                    $('#del' + id).remove();
                }
            });
            cscms.layer.close(index);
        }, function(index) {
            cscms.layer.close(index);
        });
    },
    open_del: function(cid) { //解除第三方绑定
        cscms.layer.confirm('你确定要解除绑定吗？', {
            btn: ['确定', '取消'] //按钮
        }, function(index) {
            $.getJSON(cscms_path + "index.php/user/ajax/open_del?cid=" + cid + "&random=" + Math.random() + "&callback=?", function(data) {
                if (data['error'] == 1000) {
                    cscms.layer.msg('您登录已经超时！', {
                        icon: 2
                    });
                    return false;
                } else {
                    cscms.layer.msg('恭喜您，解除绑定成功~！', {
                        icon: 1
                    });
                    setTimeout('location.replace(location);', 2000);
                }
            });
        }, function(index) {
            cscms.layer.close(index);
        });
    },
    web: function(dir, cion) {
        if (cion > 0) {
            cscms.layer.confirm('使用该模板需要扣除' + cion + '个金币，确定使用吗？', {
                btn: ['确定', '取消'] //按钮
            }, function(index) {
                user.web_send(dir);
                cscms.layer.close(index);
            }, function(index) {
                cscms.layer.close(index);
            });
        } else {
            user.web_send(dir);
        }
    },
    web_send: function(dir) {
        $.getJSON(cscms_path + "index.php/user/ajax/web?dir=" + dir + "&random=" + Math.random() + "&callback=?", function(data) {
            if (data['error'] == 1001) {
                cscms.layer.msg('参数错误！', {
                    icon: 2
                });
                return false;
            } else if (data['error'] == 1000) {
                cscms.layer.msg('您登录已经超时！', {
                    icon: 2
                });
                return false;
            } else if (data['error'] == 1002) {
                cscms.layer.msg('您的级别不能使用该模板！', {
                    icon: 2
                });
                return false;
            } else if (data['error'] == 1003) {
                cscms.layer.msg('您的等级不能使用该模板！', {
                    icon: 2
                });
                return false;
            } else if (data['error'] == 1004) {
                cscms.layer.msg('您的金币不够使用该模板！', {
                    icon: 2
                });
                return false;
            } else {
                cscms.layer.msg('使用成功~!', {
                    icon: 1
                });
                setTimeout('location.replace(location);', 1000);
            }
        });
    }
    ,pic_intro : function(id){//会员中心修改图片介绍
        $.get(cscms_path+'index.php/pic/user/picadd/picContent/0?id='+id,  function(data) {
            if(data['error']==0){
                cscms.layer.prompt({
                    formType: 2,
                    value: data['info'],
                    title: '修改图片介绍',
                    area: ['250px', '150px'] //自定义文本域宽高
                }, function(value, index, elem) {
                    if(value==''){
                        cscms.layer.msg('请输入内容',{icon:2});
                    }else{
                        $.post(cscms_path+'index.php/pic/user/picadd/picContent/1', {id:id,content: value}, function(data) {
                            if(data['error']==0){
                                cscms.layer.msg('恭喜你，操作成功',{icon:1});
                            }else{
                                cscms.layer.msg(data['info'],{icon:2});
                            }
                        },"json");
                    }
                    layer.close(index);
                });
            }else{
                cscms.layer.msg(data['info'],{icon:2});
            }
                
        },"json"); 
    }
    ,delTips : function(link){//删除提示
        cscms.layer.confirm('确定要删除吗？', {
                btn: ['确定', '取消'] //按钮
        }, function(index) {
            location.href=link;
        }, function(index) {
            cscms.layer.close(index);
        });
    }
    ,get_code : function(id){//获取手机验证码
        var tel = $('#usertel').val(); 
        if(tel==''){
            cscms.layer.msg('请填写手机号码！',{icon:2});
        }else{
            $('#'+id).addClass('layui-btn-disabled');
            $.get("/index.php/user/reg/telinit?usertel="+tel,function(data) {
                if(data){
                    if(data=='addok'){
                        cscms.layer.msg('1分钟之内只能发送一次！',{icon:7});
                    } else if(data=='1'){
                        cscms.layer.msg('验证码发送成功！',{icon:1});
                        cscms.get_time(id);
                    }
                }else{
                    alert('网络连接失败！');
                }
            });
        }
    }
    ,get_time : function(id){
        if (wait == 0) {
            $('#'+id).removeClass('layui-btn-disabled');
            $('#'+id).val("免费获取验证码");
            wait = 60;
        } else {
            $('#'+id).addClass('layui-btn-disabled');
            $('#'+id).html("重新发送(" + wait + ")");
            wait--;
            setTimeout(function() {
                cscms.get_time(id);
            },1000)
        }
    }
    ,singerDel : function(id){
        cscms.layer.confirm('确定要删除该歌手吗？', {
            btn: ['确定', '取消'] //按钮
        }, function(index) {
            $.post(cscms_path+'index.php/singer/user/singer/del', {id: id}, function(data) {
                console.log(data);
                if(data.error==0){
                    cscms.layer.msg('恭喜您，删除成功',{icon:1});
                    $('#sdel_'+id).remove();
                }else{
                    cscms.layer.msg(data.msg,{icon:2});
                }
            },"json");
        },function(index){
            cscms.layer.close(index);
        });
    }
}
$(document).ready(function() {
    cscms.mode("user.init()");
});