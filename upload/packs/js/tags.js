$(function(){
	popDiyTags(); 
});

//tag pop
function popDiyTags(){	 
	   //删除选中的标签
	   function removeTags(){
		   $(".selected-list01 .selected-del").click(function(){
			  var t1=$(this).parent().find("span").text();
			  $(this).parent().remove();
			   $(".selected-list02 li span").each(function(){
				   if($(this).text()==t1){
					   $(this).parent().remove();
					   }
			    })
				$(".popDiyTags-list dd a").each(function(){
				   if($(this).text()==t1){
					   $(this).removeClass("selected");
					   }
					})
			   $(".selected-index span").text($(".selected-list li").length/2);	
			  return false;
		  })
		  $(".selected-list02 .selected-del").click(function(){
			  var t2=$(this).parent().find("span").text();
			  $(this).parent().remove();
			   $(".selected-list01 li span").each(function(){
				   if($(this).text()==t2){
					   $(this).parent().remove();
					   }
			  })
			  $(".popDiyTags-list dd a").each(function(){
				   if($(this).text()==t2){
					   $(this).removeClass("selected");
					   }
					})
			  $(".selected-index span").text($(".selected-list li").length/2);	
			  return false;
		  })
	  }
	  //选择标签
	  $(".popDiyTags-list dd a").click(function(){
			var a=$(this).text();
			if($(this).hasClass("selected")){
				alert('该标签已选择');
				return false;
				}else{
					if($(".selected-list li").length<16){
						$(this).addClass("selected")
						$(".selected-index span").text($(".selected-list li").length/2+1);	  
						$(".selected-list01").append("<li><span>"+a+"</span><a class='selected-del' href='#'></a></li>")
						$(".selected-list02").append("<li><span>"+a+"</span><a class='selected-del' href='#'></a></li>")
						removeTags();
						return false;
					}else{
						alert('您已选择了8个标签，最多设置8个标签！');
						return false;
					}
				}
	 })
	 //保存标签
	$(".popDiyTags-title .btnTure").click(function(){
		if($(".selected-list01 li").length==0){
			popclose();
			return false;
		}else{
			var tagArraySave=new Array();
			$(".selected-list01 li").each(function(i){
				tagArraySave.push($(this).find("span").text())
			}) 
			$.each(tagArraySave,function(i){
				$(".div-info .tags").append("<a href='javascript:void(0);'>"+tagArraySave[i]+"</a>") 
			}); 
			$(".div-info .tips span").text($(".div-info .tags a").length)
			ReturnValue(tagArraySave);
			return false;
		}
	})
}
