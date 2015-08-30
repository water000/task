function topManuSearch(form){
	if("function" == typeof($.txtSelected))
		return;
	//inptxt, onkeyup, onchange, onclick
	$.loadFile.setFiles([{type:"js", url:"TAG_URL_JS(, txtSelect.js)", onload:function(){
		form.src = "USER";
		var onkeyup = function(str){
			str = str.trim().htmlspecialchars();
			return '' == str ? [] : ["名字为<span style='color:red;'>"+str+"</span>的人", "标签为<span style='color:red;'>"+str+"</span>的网页"];
		},
		onchange = function(str){form.src = "名字" == str.substr(0, 2) ? "USER" : "PAGE";},
		check = function(str){
			var txt = form.keyword.value, tm = txt.trim();
			if('' == tm) return false;
			form.keyword.value = tm;
			return true;
		},
		onclick = function(str){if(check(str)) form.submit();};
		form.onsubmit = check;
		$.txtSelected(form.keyword, onkeyup, onchage, onclick);
	}}]);
}