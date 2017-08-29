/**
 * Cscms视频前台
 * www.chshcms.com
 */
var vod = {
	show_list : function(id){
		var h = $('#vodoption'+id).css('height');
		console.log(h);
		if(h=='40px'){
			$('#vodoption'+id).css('height', 'auto');
			$('#arr'+id).html('&#xe619;');
		}else{
			$('#vodoption'+id).css('height', '40px');
			$('#arr'+id).html('&#xe61a;');
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