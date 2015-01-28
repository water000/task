Yee.ajax = function(opt)
{
	var defopt = {
		type:"GET",dataType:"xml",url:"",data:null,
		timeout:15,async:true,headers:{},
		success:function(rep){alert(rep);},
		error:function(str){alert(str);}
	},xhr;
	for(var k in defopt) opt[k] = opt[k] || defopt[k];
	if(!opt.url) return;
	if("function" != typeof(opt.success) || "function" != typeof(opt.error))
		return;
	var mt = opt.type.toUpperCase();
	if(mt != "GET" && mt != "POST"){alert("Unsurpported method type: "+mt); return;};
	if(window.XMLHttpRequest){xhr = new XMLHttpRequest();}
	else if (window.ActiveXObject) {try{xhr = new ActiveXObject("Msxml2.XMLHTTP");}catch(e){xhr = new ActiveXObject("Microsoft.XMLHTTP");}}
	if(!xhr)
	{
		alert("error!Unsurpported XMLHTTPRequest on your browser!");
		return false;
	}
	var pre_ok = function()
	{
		var d = opt.dataType=="xml"?xhr.responseXML:xhr.responseText;
		if(null===d){ opt.error("error on parsing or sending");} else {if(Yee.debug(d, opt)) opt.success(d);}
	}
	xhr.open(mt, opt.url, opt.async);
	var hd = opt.headers || {};
	if(mt == "POST")
		hd["Content-Type"] = "application/x-www-form-urlencoded";
	for(var k in hd)
		xhr.setRequestHeader(k, hd[k]);
	var aborted = false;
	if(opt.async)
	{
		xhr.onreadystatechange = function()
		{
			if(4 == xhr.readyState)
			{
				if(200 == xhr.status)
				{
					pre_ok();
				}
				else
				{
					opt.error("error!http status: "+xhr.status);
					xhr.abort();
					aborted = true;
				}
			}
		}
	}
	
	xhr.send(opt.data);
	setTimeout(function(){
		if(200 == xhr.status || aborted) return;
		xhr.abort();
		opt.error("error!timeout,http status: "+xhr.status);
	}, opt.timeout*1000);
	if(!opt.async && 200 == xhr.status)
	{
		pre_ok();
	}
}
Yee.formSubmit = function(conf){
	 var conf = conf || {}, mx = conf.maxlen || {}, mi = conf.minlen || {}, j, k, len, bLoad = conf.bLoad === false ? false : true,
	 	fd = conf.fldDesc || {}, req = conf.required || [], errorFunc = conf.errorFunc || $.error, submitName = conf.submitName || 'submit';
	 for(k in mx){
		 len = this[k].value.length;
		 if(0 == len)
			 continue;
		 if(len > mx[k]){
			 errorFunc("<u><b>"+fd[k]+"</b></u>的字数不能超过"+mx[k]+"个", this[k]);
			 return false;
		 }
	 }
	 for(k in mi){
		 if(this[k].value.length < mi[k]){
			 errorFunc("<u><b>"+fd[k]+"</b></u>的字数不能小于"+mi[k]+"个", this[k]);
			 return false;
		 }
	 }
	 for(j=0,k=req.length; j<k; j++){
		 if('' == this[req[j]].value){
			 errorFunc("<u><b>"+fd[req[j]]+"</b></u>不能为空", this[req[j]]);
			 return false;
		 }
	 }
	 if(bLoad) $.loading.show("Loading...", 0, "right");
	 var _this = this, submit = this.elements[submitName];
	 if(submit && "undefined" == typeof(submit.disabled))//the submit is a function in IE
		 submit = null;
	 if(conf.ajax && '' != this.action){
		 var postval = '', el = _this.elements, i, j, _url = this.action
		 ,cb = function(msg){$.loading.show($(GLOBALS.XML_ROOT_NAME, msg).text())}
		 ,unav = function(obj){
			 var t = obj.type.toLowerCase();
			 return ('' == obj.name.trim() || obj.disabled || t == "file" || t == "reset" ||
					 ((t == "checkbox" || t == "radio") && !obj.checked));
		 }
		 ,makepost = function(elem){
			 if(unav(elem)) return;
			 if("SELECT" == elem.tagName)
				 elem.setAttribute("value", elem.options[elem.selectedIndex].value);
			 postval += elem.name + "=" + encodeURIComponent(elem.value)+"&";
		 };
		 for(i=0; i<el.length; i++){
			 if(el[i].length){
				 for(j=0; j<el[i].length; j++)
					 makepost(el[i][j]);
			 }else{
				 makepost(el[i]);
			 }
		 }
		 if("GET" == _this.method.toUpperCase() && "" != postval){
			 _url += '&'+postval;
			 postval = '';
		 }
		 $.ajax({
			type: _this.method || "",url: _url,data: postval,
			headers:{"If-Modified-Since":"0"},
			error: function(errmsg){$.loading.hide();alert(errmsg);},
			success:function(ret){if(submit) submit.disabled = false;if(bLoad) $.loading.show("finished", 3000);(conf.ajax||cb)(ret)}
		 });
	 }
	 if(submit) submit.disabled = true;
	 $(window).bind("unload", function(){
		 if(bLoad) $.loading.hide();
		 if(submit) submit.disabled = false;
	 });
	 return true;
}