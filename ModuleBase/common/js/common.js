String.prototype.isNumeric = function(){return /^[0-9a-f\.x]+$/i.test(this);}
String.prototype.trim = function(){return this.replace(/^\s*([\w\W]+)\s*$/, "$1");}
String.prototype.stripTags = function(){return this.replace(/<[^>]+>/g, "");}
String.prototype.htmlspecialchars = function(){return this.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\"/g, "&quot;").replace(/\'/g, "&#039;");}
String.prototype.htmlspecDecode = function(){return this.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, '"').replace(/&#039;/g, "'");}
Date.prototype.format = function(s){
	if(typeof s != "string" || 0 == s.length)
		s = "Y-m-d H:i:s";
	var _this = this, format = {
		Y:_this.getFullYear(),y:_this.getYear(),
		m:(_this.getMonth()<9?'0':'')+(_this.getMonth()+1),n:_this.getMonth()+1,
		d:(_this.getDate()<10?'0':'')+_this.getDate(),j:_this.getDate(),
		H:(_this.getHours()<10?'0':'')+_this.getHours(),G:_this.getHours(),
		i:_this.getMinutes()<10?'0'+_this.getMinutes():_this.getMinutes(),
		s:_this.getSeconds()<10?'0'+_this.getSeconds():_this.getSeconds(),
		l:_this.getMilliseconds(),w:_this.getDay()
	},cs = "", i=0, c;
	for(c=s.substr(0,1); i<s.length; i++, c=s.substr(i,1))
		cs += format[c] || c;
	return cs;
}
Date.prototype.compare = function(src){
	this.setHours(0);
	this.setMinutes(0);
	this.setSeconds(0);
	this.setMilliseconds(0);
	var ct = this.getTime(), st = new Date(src).getTime(), ret = '';
	if(st<ct){
		diff = Math.ceil((ct-st)/86400000);
		ret = 1 == diff ? '昨天' : (2 == diff ? '前天' : new Date(src).format('n月d日'));
	}
	return ret;
}
var GLOBALS = {XML_ROOT_NAME : "root"};
(function(window){
var agt = navigator.userAgent.toLowerCase(),
bsrs = ["opera","msie","mac","gecko","firefox","safari"], ptn,tmp,
bsrm = ["is_op","is_ie","is_mac","is_gk","is_ff","is_sf"], i=0, j=bsrs.length;
for(; i<j; i++){
	if(agt.indexOf(bsrs[i] ) != -1){
		window[bsrm[i]] = true;
		ptn = new RegExp(bsrs[i]+"\\s*/?\\s*(\\d+)", "i");
		tmp = agt.match(ptn);
		window.bsr_ver = tmp?tmp[1]:0;
	}else window[bsrm[i]] = false;
}
window.is_ie6 = (is_ie && 6==bsr_ver);
var Yee = function(selector, context)
{
	return selector instanceof _y ? selector : new _y(selector, context);
}
var _y = function(selector, context)
{
	var _context=null, _selector=[];
	var init = function()
	{
		if(!selector)
			return;
		var t = typeof(selector);
		if("object" == t)
		{
			if(!selector.length)
				_selector.push(selector);
			else
			{
				for(var i=0; i<selector.length; i++)
					_selector.push(selector[i]);
			}
		}
		else if("string" == t)
		{
			var ctx = context || document, sel=selector.split(" ");
			var inArray = function(arr, v){for(var i=0, j=arr.length; i<j; i++){ if(arr[i] == v) return true;}return false;}
			var _s = function(val, str)
			{
				var fc=str.substr(0,1), _name = str.substr(1);
				return ("#" == fc && val.id == _name)
				 || ("." == fc && val.className && inArray(val.className.split(/\s+/), _name))
				 || (str.toUpperCase()==val.nodeName.toUpperCase());
			}
			var ptr=0, sel_len = sel.length;
			var _rec = function(dom)
			{
				var b;
				for(var i=0, ch=dom.childNodes, j=ch.length; i<j; i++)
				{
					b = _s(ch[i], sel[ptr >= sel_len ? sel_len-1 : ptr]);
					if(b)
					{
						if(ptr >= sel_len-1)
							_selector.push(ch[i]);
						ptr++;
					}
					_rec(ch[i]);
					if(b)
						ptr--;
				}
			};
			if(typeof(ctx.nodeType) != "undefined"){
				_rec(ctx);
			}else if("number" == typeof(ctx.length)){
				for(var i=0, j=ctx.length; i<j; i++)
					_rec(ctx[i]);
			}
		}
	}
	this.each = function(callback){for(var i=0, j=_selector.length; i<j; i++){ if(callback(_selector[i], i)) break;}}
	this.bind = function(evttype, handle)
	{
		var _f=function(obj){
			if(obj.attachEvent){
				obj.attachEvent("on" + evttype, function(ev){handle.call(obj, event);});
			}
			else if(obj.addEventListener){
				obj.addEventListener(evttype, handle, false);
			}
			else{
				obj["on"+evttype] = function(ev){handle.call(obj, ev||event)};
			}
		};
		for(var i=0; i<_selector.length; i++)
			_f(_selector[i]);
	}
	this.unbind = function(evttype, handle)
	{
		var obj;
		for(var i=0; i<_selector.length; i++)
		{
			obj = _selector[i];
			if(obj.attachEvent)
				obj.detachEvent("on" + evttype, function(event){handle.call(obj, event)});
			else
				obj.removeEventListener(evttype, handle, false);
		}
	}
	this.ready = function(cb)//only valid for document
	{
		var _d = _selector[0], b = false;
		var _fn = function(e)
		{
			var fn = null;
			if(e.attachEvent){
				fn = function(ev){if("complete" == e.readyState) cb.call(e, ev||event);}
				e.detachEvent("onreadystatechange", fn);
				e.attachEvent("onreadystatechange", fn);
				//e.attachEvent("onload", fn);
			}
			else{
				fn = function(ev){cb.call(e, ev||event);}
				e.removeEventListener("DOMContentLoaded", fn, false);
				e.addEventListener("DOMContentLoaded", fn, false);
				//e.addEventListener("onload", fn, false);
			}
		}
		for(var i=0, j=_selector.length; i<j; i++)
			_fn(_selector[i]);
	}
	this.css = function(key, val)
	{
		var func , t = typeof(key), ret;
		if(1 == arguments.length)
		{
			if("string" == t)
				func = function(obj){return obj.style[key]}
			else if("object" == t)
				func = function(obj){for(var k in key) obj.style[k] = key[k];}
		}
		else if(2 == arguments.length)
		{
			func = function(obj){obj.style[key] = val;}
		}
		if(!func) return;
		for(var i=0, j=_selector.length; i<j; i++)
			ret += func(_selector[i]);
		return ret;
	}
	this.show = function(){this.css("display", '');}
	this.hide = function(){this.css("display", 'none');}
	this.text = function(stripBackspace)
	{
		stripBackspace = stripBackspace === false ? false : true;
		var _rec = function(node)
		{
			var  _text="";
			for(var i=0, nc=node.childNodes, j=nc.length; i<j; i++)
			{
				if(1 == nc[i].nodeType)
					_text += _rec(nc[i]);
				else/* if(3 == nc[i].nodeType)*/
				{
					if(stripBackspace && i!=0 && nc[i-1].nodeType == 1 
						&& i!=j-1 && nc[i+1].nodeType == 1 && /^\s+$/.test(nc[i].nodeValue))
						continue;
					_text += nc[i].nodeValue;
				}
			}
			return _text;
		}
		var str = "";
		for(var i=0, j=_selector.length; i<j; i++)
			str += _rec(_selector[i]);
		return str;
	}
	this.html = function()
	{
		var str = "";
		for(var i=0, j=_selector.length; i<j; i++)
			str += ( _selector[i].innerHTML||_selector[i].childNodes[0].nodeValue);
		return str;
	}
	this.append = function(arg)
	{
		for(var i=0; i<_selector.length; i++)
		{
			if(_selector[i].tagName == "")
				continue;
			if(typeof(arg) == "object")
				_selector[i].appendChild(arg);
			else
				_selector[i].innerHTML += arg;
		}
	}
	this.hasChild = function(obj)
	{
		var src = _selector[0];
		if(!src || !obj)
			return false;
		var arr = src.getElementsByTagName("*"), i=0, j=arr.length;
		for(; i<j; i++)
		{
			if(arr[i] == obj)
				return true;
		}
		return false;
	}
	this.elements = function(){return _selector;}
	var evttype = ("blur focus focusin focusout load resize scroll unload click dblclick "+
		"mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave " +
		"change select submit keydown keypress keyup error readystatechange").split(" "), _this = this;
	init();
	this.length = _selector.length;
	var evtfn = function(type){_this[type] = function(fn){_this.bind(type, fn);}};
	for(var i=0; i<evttype.length; i++)
		evtfn(evttype[i]);
	for(var i=0; i<_selector.length; i++)
		this[i] = _selector[i];
	var arrayFeture = ["concat","join","pop","push","reverse","shift","slice","sort","splice","toLocaleString","toString","unshift","valueOf"];
	for(i=0; i<arrayFeture.length; i++)
		this[arrayFeture[i]] = function(){try{Array.arrayFeture[i].call(_selector, arguments);}catch(e){};}
	return this;
}
Yee.pos=function(obj){
	if(!obj || !obj.tagName)
		return false;
	var t=obj,x=0, y=0,p;
	do{
		x+=t.offsetLeft;
		y+=t.offsetTop;
		t=t.offsetParent;
		if(t){
			p=t.style.position;
			if(p=="relative" || p=="absolute"){
				break;
			}
		}
	}while(t);
	return [y,x];
};
Yee.debug=function(data, opt)
{
	var err = $("error", data).html(), app = $("appdebug", data).html(), db = $("dbdebug", data).html(), mem = $("memdebug", data).html(), 
	total = $("total", data).html(), str = app + err + db + mem + total,div = document.createElement("div");
	document.body.appendChild(div);
	if('' != total){
		div.innerHTML = "<h4>"+opt.type+": "+opt.url+"</h4>"+"<h4>PARAM: "+(opt.data||"").htmlspecialchars()+"</h4>"+str;
	}
	return 0 == err.length;
}
Yee.switchRow=function(objtb, offset, length, overclass, outclass)
{
	if(!objtb || "TABLE" != objtb.tagName)
		return;
	if(objtb.getAttribute("__switichrow__"))
		return;
	objtb.setAttribute("__switichrow__", 1);
	offset = offset || 0;
	length = length || objtb.rows.length;
	length = length<0?objtb.rows.length+length : length;
	overclass = overclass || {backgroundColor:"#E3EEFB"};
	outclass = outclass || {backgroundColor:"white"};
	var prebg='',fn = function(event){if(this.tagName != "TR") return;var c = -1 != event.type.indexOf("mouseover") ? overclass : (outclass||{backgroundColor:(prebg||"white")});if(c==overclass) pregbg=this.cells[0].style.backgroundColor;for(var i=0, cs=this.cells, j=cs.length;i<j;i++) $(cs[i]).css(c)}
	for(var i=offset; i<length;i++)
	{
		$(objtb.rows[i]).bind("mouseover", fn);
		$(objtb.rows[i]).bind("mouseout", fn);
	}
}
Yee.loading = {
	obj:null,tid:0,
	init:function()
	{
		if(this.obj) return;
		this.obj = document.createElement("div");
		this.obj.className = 'g-loading';
		$(document.body).append(this.obj);
	},
	show:function(str, timeout, align, css, className)
	{
		str = str || "loading...";
		this.init();
		var dd = document.documentElement;
		//if(dd.scrollTop)
		//	this.obj.style.top = dd.scrollTop+"px";
		this.obj.innerHTML = '<span>'+str+'</span>';
		align = align || "right";
		var _this = this, obj = this.obj.firstChild, fo= "undefined" != typeof(obj.style.styleFloat)?"styleFloat":"cssFloat";
		if("left" == align)
		{
			obj.style[fo] = "left";
			obj.style.margin = "0";
		}
		else if("center" == align)
		{
			obj.style[fo] = "none";
			obj.style.margin = "0 auto";
		}
		else
		{
			obj.style[fo] = "right";
			obj.style.margin = "0";
		}
		if(this.tid != 0)
		{
			clearTimeout(this.tid);
			this.tid = 0;
		}
		if(typeof(timeout) == "number" && timeout > 0)
			this.tid = setTimeout(function(){_this.hide();}, timeout);
		if(css)
		{
			for(var k in css)
				this.obj.style[k] = css[k];
		}
		this.obj.className = className || 'g-loading';
		this.obj.style.display = "";
	},
	hide:function()
	{
		this.obj.style.display = "none";
		this.obj.style.top = "0";
	},
	isLoading:function()
	{
		return (this.obj && this.obj.style.display != "none");
	}
}
var error = null;
Yee.error = function(str, timeout)
{
	if(null == error)
	{
		error = {};
		$.loading.init();
		for(var k in $.loading)
			error[k] = $.loading[k];
		error.obj = null;
		error.init();
	}
	error.show(str, typeof(timeout)=="number"?timeout : 4000, "center", '', 'g-loading-error');
}
Yee.filter=function(array, func){
	var ret = [], i=0;
	for(; i<array.length; i++){
		if(func(i, array[i]))
			ret.push(array[i]);
	}
	return ret;
}
Yee.trim = function(str){return str.trim();}
Yee.fade = function(obj){
	var steps = [1,3,5,8,10],counter=0,intval,s=obj.style, MS=50;
	if(is_ie && bsr_ver<=7) obj.style.zoom = 1;
	return {
		getObject:function(){return obj;},
		in2:function(callback){
			counter=0;
			callback = callback || function(){};
			s.filter = "alpha(opacity=0)";
			s.opacity = "0";
			s.display = '';
			intval = setInterval(function(){
				s.filter = "alpha(opacity="+(steps[counter]*10)+")";
				s.opacity = 0.1*steps[counter];
				counter ++;
				if(steps.length == counter){clearInterval(intval);s.filter ='';s.opacity = '';callback();}
			}, MS);
		},
		out:function(callback, bRemove){
			counter=steps.length-1;
			bRemove = bRemove === false ? false : true;
			callback = callback || function(){};
			s.display = '';
			var _fn = function(num){s.filter = "alpha(opacity="+(num*10)+")";s.opacity = 0.1*num;}
			intval = setInterval(function(){
				_fn(steps[counter]);
				counter --;
				if(-1 == counter){if(bRemove) obj.parentNode.removeChild(obj); else{s.display = 'none';s.filter ='';s.opacity = '';}clearInterval(intval);callback();}
			}, MS);
		}
	};
}
Yee.remove = function(obj){Yee.fade(obj).out();}
Yee.shake = function(obj, top, bottom, end){
	var intval = 0, num = 8, cur=0, bTop = true, os = obj.style, st = "borderColor", bd = os[st], ret={},shaking = false,
	top = top||function(){os[st] = "#91C88C"}, bottom = bottom||function(){os[st] = "white"}, end=end||function(){os[st]=bd;};
	ret = {
		start:function(){
			if(shaking) return;
			shaking = true;
			intval = setInterval(function(){
				if(bTop) top(); else bottom();
				bTop = !bTop;
				cur++;
				if(cur == num)
					ret.end();
			}, 90);
		},
		end:function(){
			if(intval != 0){
				shaking = false;
				cur = 0;
				clearInterval(intval);
				end();
			}
		}
	}
	return ret;
}
Yee.backToTop = function(leftObj, absBottom, relBottom){
	var d = window.document, dd = d.documentElement, db = d.body,
	btt = null,ybtt,yw = $(window),absBottom = absBottom || 20, relBottom = relBottom || absBottom, maxTop,curTop,
	_top = function(){curTop = dd.clientHeight-btt.offsetHeight+(dd.scrollTop||db.scrollTop)-absBottom;maxTop = dd.scrollHeight - relBottom-btt.offsetHeight;},
	topos = is_ie6 ? function(){_top();var top = curTop > maxTop ? maxTop : curTop; ybtt.css("top", top+"px");}
		: function(){_top();var bottom = absBottom+(curTop > maxTop ? curTop-maxTop:0);ybtt.css("bottom", bottom+"px");},
	re = function(){var lol = $.pos(leftObj)[1]+leftObj.offsetWidth; ybtt.css("left", lol+"px");topos();},
	init = function(){
		btt = d.createElement("div");
		btt.className = "back_to_top";
		btt.style.marginLeft = "5px";
		btt.innerHTML = "<a>返回顶部</a>";
		btt.title = "返回顶部";
		db.appendChild(btt);
		ybtt = $(btt);
		re();
		btt.onclick = function(){dd.scrollTop = db.scrollTop = 0;}
		yw.resize(re);
	},
	sc = function(){
		var st = dd.scrollTop||db.scrollTop;
		if(null == btt){init();return}
		if(0 == st){ybtt.hide();return;}
		ybtt.show();
		topos();
	}
	yw.scroll(sc);
}
Yee.loadFile = (function(){
	var bInitCache = false, jsUrl, jsNode, cssUrl, cssNode, jsLoaded, cssLoaded,
	initCache = function(){
		var js = document.getElementsByTagName("script"), css= document.getElementsByTagName("link"),i,j;
		for(i=0,j=js.length; i<j; i++){
			if("string" == typeof(js[i].src)){
				jsUrl.push(js[i].src);
				jsNode.push(js[i]);
				jsLoaded.push(true);
			}
		}
		for(i=0,j=css.length; i<j; i++){
			if("text/css" == css[i].type.toLowerCase() && "string" == typeof(css[i].href)){
				cssUrl.push(css[i].href);
				cssNode.push(css[i]);
				cssLoaded.push(true);
			}
		}
	},
ret = {
	setCache:function(b){if(b===true){jsUrl=[];jsNode=[];cssUrl=[];cssNode=[];jsLoaded = [];cssLoaded = [];}else{jsUrl=null;jsNode=null;cssUrl=null;csNode=null;jsLoaded=null;cssLoaded=null;}},
	inCache:function(type,url){if(null != jsUrl){var dest=type.toLowerCase()=="js"?jsUrl:cssUrl,i,j;for(i=0,j=dest.length;i<j;i++){if(dest[i] == url)return i;}}return false;},
	addCache:function(type, url, node){if(null != jsUrl){if(type.toLowerCase()=="js"){jsUrl.push(url);jsNode.push(node);jsLoaded.push(false);return jsNode.length-1;}else{cssUrl.push(url);cssNode.push(node);cssLoaded.push(false);return cssNode.length-1;}}},
	delCache:function(type, url){var id = this.inCache(type,url);if(false !== id){if(type.toLowerCase()=="js"){jsUrl[id]=jsNode[id]=jsLoaded[id]='';}else{cssUrl[id]=cssNode[id]=cssLoaded[id]='';}}},
	setFiles:function(files, callback){//files:[{type:js|css|img, url:str, onload:function,parent:null},...]
		callback = callback||null;
		if(null != jsUrl && !bInitCache){initCache();bInitCache=true;}
		var _dc = function(t){return document.createElement(t)}, nodes = [], filesLoaded = [],
		_oncssload = function(scr,cb, id, cacheId){if(typeof(scr.sheet) == "undefined")return;var h=setInterval(function(){if(scr.sheet !==null){clearInterval(h);_ready(cb, id, cacheId, callback, filesLoaded);}}, 50)},
		_ready = function(cb, id, cacheId, callback, filesLoaded){
			cb();
			filesLoaded[id] = true;
			if("number" == typeof(cacheId)){
				var type = files[id].type.toLowerCase();
				if("js" == type)
					jsLoaded[cacheId] = true;
				else if("css" == type)
					cssLoaded[cacheId] = true;
			}
			if(callback != null && filesLoaded.length == files.length){
				var allLoaded = true;
				for(var i=0, arr= filesLoaded, j=arr.length; i<j; i++)
					allLoaded = arr[i] && allLoaded;
				if(allLoaded){callback();callback=null;}
			}
		},
		_loaded = function(scr, cb, id, cacheId){
			if(scr.readyState){
				$(scr).readystatechange(function(){if(this.readyState == "loaded" || this.readyState == "complete") _ready(cb, id,cacheId,callback,filesLoaded);});
			}else{
				$(scr).load(function(){setTimeout(function(){_ready(cb, id,cacheId, callback,filesLoaded);}, Math.random()*10);});
			}
		},head=document.getElementsByTagName("head")[0];
		var _js = function(url){var file = _dc("script");file.type = "text/javascript";file.src = url;return file;},
		_css = function(url){var file = _dc("link");file.type = "text/css";file.rel = "stylesheet";file.href = url;return file;}
		for(var i=0, j=files.length,type,file,idc; i<j; i++)
		{
			filesLoaded[i] = false;
			file=null;idc=null; 
			type = files[i].type.toLowerCase();
			if("js" == type){
				if(jsUrl != null){
					if(false !== (idc = this.inCache("js", files[i].url))){
						if(jsLoaded[idc]) _ready(files[i].onload||function(){}, i, null, callback,filesLoaded);
						else _loaded(jsNode[idc], files[i].onload||function(){}, i);
					}else{
						file = _js(files[i].url);
						_loaded(file, files[i].onload||function(){}, i, this.addCache(type, files[i].url, file));
					}
				}else{
					file = _js(files[i].url);
					_loaded(file, files[i].onload||function(){}, i);
				}
			}
			else if("css" == type){
				if(jsUrl != null){
					if(false !== (idc = this.inCache("css", files[i].url))){
						if(cssLoaded[idc]) _ready(files[i].onload||function(){}, i, null, callback,filesLoaded);
						else ("undefined" == typeof(cssNode[idc].sheet)?_loaded:_oncssload)(cssNode[idc], files[i].onload||function(){}, i);
					}else{
						file = _css(files[i].url);
						("undefined" == typeof(file.sheet)?_loaded:_oncssload)(file, files[i].onload||function(){}, i, this.addCache(type, files[i].url, file));
					}
				}else{
					file = _css(files[i].url);
					("undefined" == typeof(file.sheet)?_loaded:_oncssload)(file, files[i].onload||function(){}, i);
				}
			}
			else if("img" == type){
				file = _dc("img");
				file.src = files[i].url;
				_loaded(file, files[i].onload||function(){}, i);
			}
			if(file != null) (files[i].parent||head).appendChild(file);
			nodes.push(file);
		}
		return nodes;
	}
};ret.setCache(true);return ret;})();
window.Yee = window.$ = Yee;
})(window);