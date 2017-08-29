/**
 * Cscms手机前台
 * www.chshcms.com
 */
var wap = {
    plutog : function(){
        if($(".plugins").is(":hidden")){
            $(".plugins").show();
            $('.more i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }else{
            $(".plugins").hide();
            $('.more i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    }
}