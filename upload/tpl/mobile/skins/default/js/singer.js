/**
 * Cscms新闻手机前台
 * www.chshcms.com
 **/
var singer = {
	init : function(sign){
		
	}
	,mclass : function (){
		if($(".md-head-more").is(":hidden")){
            $(".md-head-more").show();
        }else{
            $(".md-head-more").hide();
        }
	}
	,ding : function(_ac,_id){
        $.getJSON(cscms_path+"index.php/pic/ajax/picding/"+_ac+"/"+_id+"?callback=?",function(data) {
           if(data){
               if(data['msg']=='ok'){
                    cscms.layer.msg('恭喜你，操作成功',{icon:1});
                   if(_ac=='ding'){
                        $("#upCnt").text(parseInt($("#upCnt" ).text()) + 1);
                   }else{
                        $("#downCnt").text(parseInt($("#downCnt" ).text()) + 1);
                   }
               }else{
                   cscms.layer.msg(data['msg'],{icon:2});
               }
           } else {
                cscms.layer.msg('网络故障，连接失败!',{icon:7});
           }
        });
    }
}