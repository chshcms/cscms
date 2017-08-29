/**
 * Cscms歌手前台
 * www.chshcms.com
 */
var singer = {
	select_all : function(){
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
            window.open(cscms_path+'index.php/dance/playsong?id=' + v.join(','), 'play');
        }
	}
}