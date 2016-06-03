
function bind(obj, ev, fn){
	if(obj.attachEvent)
		obj.attachEvent('on'+ev, function(){fn.call(obj)});
	else if(obj.addEventListener)
		obj.addEventListener(ev, fn, false);
	else
		obj['on'+ev] = fn;
}

function formSubmitErr(form, inputErr){
	var elems = form.elements, errctl, as, fnclk, elem;
	fnclk = function(inp, _err, _as){
		bind(inp, 'click', function(e){
			this.style.border = "";
			_err.style.display = "none";
			if(_as)
				_as.style.display = "";
		});
	}
	var _rcur = function(node, name){
		if(node.childNodes){
			for(var i=0, ret; i<node.childNodes.length; i++){
				if(node.childNodes[i].tagName && node.childNodes[i].getAttribute("name") == name)
					return node.childNodes[i];
				else{
					ret = _rcur(node.childNodes[i], name);
					if(ret != null) return ret;
				}
			}
		}
		return null;
	}
	var _err = function(elem, as, msg){
		elem.style.border = "1px solid red";
		errctl = document.createElement("span");
		errctl.innerHTML = msg;
		errctl.className = "pure-form-message-inline";
		errctl.style.cssText = "color:red;";
		elem.parentNode.insertBefore(errctl, as);
		if(as)
			as.style.display = "none";
		fnclk(elem, errctl, as);
	}
	for(var k in inputErr){
		elem =  elems[k];
		if(typeof elem != "undefined"){
			if(elem.type != "hidden")
				_err(elem, elem.parentNode.getElementsByTagName("aside")[0] || null, inputErr[k]);
			else{
				var errfor = elem.getAttribute("_data-err-for");
				elem = null;
				if(errfor != null)
					elem = document.getElementById(errfor) || _rcur(form, errfor);
				if(null == elem) alert(inputErr[k]+'('+k+')');
				else _err(elem, elem.parentNode.getElementsByTagName("aside")[0] || null, inputErr[k]);
			}
		}
		else{
			node = _rcur(form, k);
			if(node != null) _err(node, node.parentNode.getElementsByTagName("aside")[0] || null, inputErr[k]);
			else alert(inputErr[k]+'('+k+')');
		}
	}
}

function submitForm(btn){
	btn.className +=' pure-input-disabled';
	btn.innerHTML+='...';
	btn.disabled=true;
	btn.form.submit();
	return true;
}

function switchRow(objtb, offset, length, overclass){
	if(!objtb || "TABLE" != objtb.tagName)
		return;
	offset = offset || 0;
	length = length || objtb.rows.length;
	length = length<0?objtb.rows.length+length : length;
	for(var i=offset; i<length;i++){
		bind(objtb.rows[i], 'mouseover', function(e){
			this.className += ' '+overclass;
		});
		bind(objtb.rows[i], 'mouseout', function(e){
			this.className = this.className.replace(overclass, '');
		});
	}
}

function btnlist(list){
	if(!list || !list[0]) return;
	var allow_multi_opt = null, prev_checked_btn = null;
	var _fnchecked = function(elem){
		var inph;
		if(!allow_multi_opt){
			if(prev_checked_btn != null)
				_fnunchecked(prev_checked_btn);
			prev_checked_btn = elem;
		}
		elem.className += " pure-button-checked";
		inph = document.createElement("input");
		inph.type = "hidden";
		inph.name = elem.name;
		inph.value = elem.getAttribute("_value");
		elem.parentNode.insertBefore(inph, elem);
		elem.setAttribute("_checked", "1");
	}
	var _fnunchecked = function(elem){
		elem.className = elem.className.replace(" pure-button-checked", "");
		elem.parentNode.removeChild(elem.previousSibling);
		elem.setAttribute("_checked", "0");
		if(!allow_multi_opt && prev_checked_btn == elem){
			prev_checked_btn = null;
		}
	}
	for(var i=0; i<list.length; i++){
		if(list[i].getAttribute("_value") != null){
			allow_multi_opt = null == allow_multi_opt ? list[i].name.indexOf("[]") != -1 : allow_multi_opt;
			if('1' == list[i].getAttribute("_checked")){
				_fnchecked(list[i]);
			}
			bind(list[i], 'click', function(e){
				if('1' == this.getAttribute("_checked")){
					_fnunchecked(this);
				}else{
					_fnchecked(this);
				}
			});
		}
	}
}

//opt:{max_files:5, file_name:"", onFileDel:fn(imgobj, onsuccess(){}){}, container:string/obj }
function fileUpload(opt){
	var cntr = typeof opt.container == "Object" ? opt.container : document.getElementById(opt.container);
	if(!cntr){
		alert("[fileUpload]container: '" + opt.container + "' invalid!");
		return false;
	}
	var idx_counter = 0, num_of_files = 0;
	var _add = function(before){
		var _win = document.createElement("span");
		_win.id = "img-lab-bg";
		_win.innerHTML = "<input id=IDI_IMG"+ idx_counter +" type='file' name='"+opt.file_name+"' />" +
				"<label title='添加' for=IDI_IMG"+ idx_counter +" class='img-lab-add' id='img-lab'>+</label><div class=img-name></div>";
		idx_counter++;
		cntr.insertBefore(_win, before||null);
		_win.childNodes[0].onchange = function(e){
			_edit(this);
			_win.parentNode.removeChild(_win);
			if(num_of_files < opt.max_files) _add(null);
		}
	}
	var _remove = function(_win){_win.parentNode.removeChild(_win);num_of_files--;if(num_of_files == opt.max_files-1) _add();}
	var _show = function(file){
		var _win = document.createElement("span");
		_win.id = "img-lab-bg";
		cntr.insertBefore(_win, file);
		_win.appendChild(file);
		var lab = document.createElement("label");
		lab.innerHTML = '-';
		lab.className = 'img-lab-del';
		lab.id = 'img-lab';
		lab.title = "删除";
		_win.appendChild(lab);
		bind(_win, 'mouseover', function(e){lab.style.visibility="visible";});
		bind(_win, 'mouseout', function(e){lab.style.visibility="hidden";});
		bind(_win, 'click', function(e){opt.onFileDel(file, function(){_remove(_win);});});
		num_of_files++;
	}
	var _edit = function(inputFile){
		var _win = document.createElement("span");
		_win.id = "img-lab-bg";
		_win.appendChild(inputFile);
		inputFile.style.display = "none";
		cntr.insertBefore(_win, cntr.childNodes.length>0 ? cntr.childNodes[cntr.childNodes.length-1] : null);
		_win.innerHTML = "<label title='删除' class='img-lab-del' id='img-lab'>-</label><div class=img-name></div>";
		inputFile.style.display = "none";
		_win.insertBefore(inputFile, _win.childNodes[0]);
		for(var c,n=inputFile.value.length-1; n>=0; n--){
			c = inputFile.value.charAt(n);
			if(c == '/' || c == '\\') break;
		}
		_win.childNodes[2].innerHTML = n>=0? inputFile.value.substr(n+1) : inputFile.value;
		var lab = _win.childNodes[1];
		bind(_win, 'mouseover', function(e){lab.style.visibility="visible";});
		bind(_win, 'mouseout', function(e){lab.style.visibility="hidden";});
		bind(_win, 'click', function(e){_remove(_win);});
		num_of_files++;
	}
	for(var j=0; j<cntr.childNodes.length; j++){
		if( cntr.childNodes[j].tagName){
			_show(cntr.childNodes[j]);
		}
	}
	if(num_of_files < opt.max_files) _add();
}

function childof(child, ancestor){
	for(;child!=ancestor && child.tagName!="BODY";child=child.parentNode); return child==ancestor;
}

function popwin(title, body){
	var pw = document.createElement("div"),mask_win;
	document.body.appendChild(pw);
	pw.className = "popwin";
	pw.style.display="none";
	pw.innerHTML = "<div class=pwtitle>"+title+"<a href='javascript:;'>&times</a></div><div class=bd></div>";
	pw.body=function(b){var bd=pw.childNodes[1];bd.innerHTML="";if("object" == typeof b) bd.appendChild(b); else bd.innerHTML = b;return pw;}
	pw.show=function(){pw.style.display="";if(mask_win) mask_win.style.display="";return pw;}
	pw.hide=function(){pw.style.display="none";if(mask_win) mask_win.style.display="none";return pw;}
	pw.remove=function(){pw.parentNode.removeChild(pw);mask_win.parentNode.removeChild(mask_win);}
	pw.onclose=function(){pw.hide();}
	pw.mask=function(){mask_win=document.createElement("div");mask_win.style.display="none";mask_win.className="popwin-mask";document.body.appendChild(mask_win);bind(mask_win, "click", function(){pw.hide();});return pw;}
	pw.bdheight=function(){pw.childNodes[1].style.height = (pw.offsetHeight-pw.childNodes[0].offsetHeight) + "px";}
	pw.autoclose=function(){bind(document.body, 'click', function(e){var ev=e||event, en=ev.target||ev.srcElement; if(childof(en, pw))return; pw.hide();}); return pw;}
	pw.around=function(node){var dd=document.documentElement, dw=dd.clientWidth-10, dh=dd.clientHeight-10, p=nodepos(node), x=p[1], y=p[0]; if(dw<x+pw.offsetWidth) x=dw-pw.offsetWidth;y=dh<y+node.offsetHeight+pw.offsetHeight?y-pw.offsetHeight:y+node.offsetHeight;pw.style.left=x+"px";pw.style.top=y+"px"; return pw;}
	pw.noclose=function(){pw.childNodes[0].style.display="none";return pw;}
	pw.body(body);
	pw.childNodes[0].getElementsByTagName("a")[0].onclick=function(e){pw.onclose();}
	bind(window, 'resize', function(e){pw.bdheight();});
	return pw;
}

function ajax(opt){
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
	if(!xhr){
		alert("error!Unsurpported XMLHTTPRequest on your browser!");
		return false;
	}
	var pre_ok = function(){
		var d = opt.dataType=="xml"?xhr.responseXML:xhr.responseText;
		if(null===d){ opt.error("error on parsing or sending");} 
		else {if("json"==opt.dataType)opt.success(eval('('+d+')')); else opt.success(d);}
	}
	xhr.open(mt, opt.url, opt.async);
	var hd = opt.headers || {};
	hd["Accept"] = "json" == opt.dataType ? "application/json" : "text/"+opt.dataType;
	if(mt == "POST") hd["Content-Type"] = "application/x-www-form-urlencoded";
	for(var k in hd) xhr.setRequestHeader(k, hd[k]);
	var aborted = false;
	if(opt.async){
		xhr.onreadystatechange = function(){
			if(4 == xhr.readyState){
				if(200 == xhr.status) pre_ok();
				else{
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
	if(!opt.async && 200 == xhr.status){
		pre_ok();
	}
}

function nodepos(obj){
	if(!obj || !obj.tagName) return false;
	var t=obj,x=0, y=0,p;
	do{
		x+=t.offsetLeft;
		y+=t.offsetTop;
		t=t.offsetParent;
		if(t){
			p=t.style.position;
			if(p=="relative" || p=="absolute") break;
		}
	}while(t);
	return [y,x];
}

function txtcounter(ctr){
	if(!ctr || "undefined" == typeof ctr.value) return false;
	var _pos = function(v){
		var pos=nodepos(ctr);
		v.style.right = (document.body.offsetWidth-(pos[1]+ctr.offsetWidth)+3)+"px";
		v.style.top = (pos[0]+ctr.offsetHeight-18)+"px";
	}
	var v = ctr["_txtcounter"];
	if(!v){
		v = document.createElement("div");
		v.className = "txt-counter";
		document.body.appendChild(v);
		ctr["_txtcounter"] = v;
		_pos(v);
		bind(window, 'resize', function(e){_pos(v)});
	}
	var maximum = ctr.getAttribute("_data-maximum") || 0;
	if(maximum > 0){if(ctr.value.length>maximum)ctr.value=ctr.value.substr(0,maximum);v.innerHTML=ctr.value.length+'/'+maximum;}
	else v.innerHTML = ctr.value.length;
	v.style.display="";
	ctr.setAttribute("_data-txtcounter-time", (new Date().getTime())/1000);
	setTimeout(function(){if((new Date().getTime())/1000-parseInt(ctr.getAttribute("_data-txtcounter-time")) > 4) v.style.display="none";}, 5000);
	return true;
}

