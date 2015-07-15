function setHeight(){
	var winH=$("body").height();
	$("#navBar,#wrap").css("height",(winH-59)+"px");
}
$(window).on("load",function(){
	var timer=null;
	setHeight();
	$(window).resize(function(){
		clearTimeout(timer);
		timer=setTimeout(setHeight,500);
	});
});