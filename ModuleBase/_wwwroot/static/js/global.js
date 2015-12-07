
function bind(obj, ev, fn){
	if(obj.attachEvent)
		obj.attachEvent('on'+ev, function(){fn.call(obj)});
	else if(obj.addEventListener)
		obj.addEventListener(ev, fn, false);
	else
		obj['on'+ev] = fn;
}

//@form, a <form> dom object or an objects maps like form.elements
function formSubmitErr(form, inputErr){
	var elems = form.tagName && "FORM" == form.tagName ? form.elements : form, errctl, as, fnclk, elem;
	fnclk = function(inp, _err, _as){
		bind(inp, 'click', function(e){
			this.style.border = "";
			_err.style.display = "none";
			if(_as)
				_as.style.display = "";
		});
	}
	for(var k in inputErr){
		elem =  elems[k]; // some field's name is euqal to attribute like "name"
		if(typeof elem != "undefined"){
			elem.style.border = "1px solid red";

			as = elem.parentNode.getElementsByTagName("aside")[0];
			errctl = document.createElement("span");
			errctl.innerHTML = inputErr[k];
			errctl.className = "pure-form-message-inline";
			errctl.style.cssText = "color:red;";
			elem.parentNode.insertBefore(errctl, as);
			
			if(as)
				as.style.display = "none";

			fnclk(elem, errctl, as);
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

function switchRow(objtb, offset, length, overclass)
{
	if(!objtb || "TABLE" != objtb.tagName)
		return;
	offset = offset || 0;
	length = length || objtb.rows.length;
	length = length<0?objtb.rows.length+length : length;
	for(var i=offset; i<length;i++)
	{
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
				_fndechecked(prev_checked_btn);
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
	var _fndechecked = function(elem){
		elem.className = elem.className.replace(" pure-button-checked", "");
		elem.parentNode.removeChild(elem.previousSibling);
		elem.setAttribute("_checked", "0");
		if(!allow_multi_opt && prev_checked_btn == elem){
			prev_checked_btn = null;
		}
	}
	for(var i=0; i<list.length; i++){
		if(list[i].getAttribute("_checked") != null){
			allow_multi_opt = null == allow_multi_opt ? list[i].name.indexOf("[]") != -1 : allow_multi_opt;
			if('1' == list[i].getAttribute("_checked")){
				_fnchecked(list[i]);
			}
			bind(list[i], 'click', function(e){
				if('1' == this.getAttribute("_checked")){
					_fndechecked(this);
				}else{
					_fnchecked(this);
				}
			});
		}
	}
}

//opt:{max_files:5, file_name:"", onFileDel:fn(imgobj){}, container:string/obj }
function fileUpload(opt){
	var cntr = typeof opt.container == "Object" ? opt.container : document.getElementById(opt.container);
	if(!cntr){
		alert("container: '" + opt.container + "' invalid!");
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
		bind(_win, 'click', function(e){opt.onFileDel(file);_win.parentNode.removeChild(_win);num_of_files--;_add();});
		num_of_files++;
	}
	var _edit = function(inputFile){
		var _win = document.createElement("span");
		_win.id = "img-lab-bg";
		_win.appendChild(inputFile);
		inputFile.style.display = "none";
		cntr.insertBefore(_win, cntr.childNodes[0]);
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
		bind(_win, 'click', function(e){_win.parentNode.removeChild(_win);num_of_files--;_add();});
		num_of_files++;
	}
	for(var j=0; j<cntr.childNodes.length; j++){
		if( cntr.childNodes[j].tagName){
			_show(cntr.childNodes[j]);
			num_of_files++;
		}
	}
	if(num_of_files < opt.max_files) _add();
}

