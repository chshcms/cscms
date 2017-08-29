/**
 * Cscms视频手机前台
 * www.chshcms.com
 **/
var vod = {
	init : function(sign){
		
	}
	,mclass : function (){
		if($(".md-head-more").is(":hidden")){
            $(".md-head-more").show();
        }else{
            $(".md-head-more").hide();
        }
	}
	,mzhan : function(){
		var h = parseInt($('.mv-neir').height());
		if(h==50){
			$('.mv-neir').css('height', 'auto');
			$('.mv-zhan').html('<i class="fa fa-angle-double-up"></i>点击收起');
		}else{
			$('.mv-neir').css('height', '50px');
			$('.mv-zhan').html('<i class="fa fa-angle-double-down"></i>点击展开');
		}
	}
	,vodFav : function(id){
		$.getJSON(cscms_path+"index.php/vod/ajax/vodfav/"+id+"?callback=?",function(data) {
           	if(data){
               	if(data['msg']=='ok'){
                   	$("#favnum").text(parseInt($("#favnum" ).text()) + 1);
               	}else{
                   	cscms.layer.msg(data['msg'],{icon:2});
               	}
           	} else {
                cscms.layer.msg('网络故障，连接失败!',{icon:2});
           	}
     	});
	}
	,vodZan : function(id){
		$.getJSON(cscms_path+"index.php/vod/ajax/vodding/"+id+"?callback=?",function(data) {
           	if(data){
               	if(data['msg']=='ok'){
                   	$("#zannum").text(parseInt($("#zannum" ).text()) + 1);
               	}else{
                   	cscms.layer.msg(data['msg'],{icon:2});
               	}
           	} else {
                cscms.layer.msg('网络故障，连接失败!',{icon:2});
           	}
     	});
	}
}