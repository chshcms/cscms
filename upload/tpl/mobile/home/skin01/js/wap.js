/**
 * Cscms手机前台
 * www.chshcms.com
 */
var wap = {
    plutog : function(){
        if($(".plugins").is(":hidden")){
            $(".plugins").show();
            $('.more i').html('&#xe619;');
        }else{
            $(".plugins").hide();
            $('.more i').html('&#xe61a;');
        }
    }
}