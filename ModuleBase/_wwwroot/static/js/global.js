
function bind(obj, ev, fn){
	if(obj.attachEvent)
		obj.attachEvent('on'+ev, fn);
	else if(obj.addEventListener)
		obj.addEventListener(ev, fn, false);
	else
		obj['on'+ev] = fn;
}

function formSubmitErr(form, inputErr){
	var elems = form.elements, errctl, as, fnclk;
	fnclk = function(inp, _err, _as){
		bind(inp, 'click', function(e){
			this.style.border = "";
			_err.style.display = "none";
			if(_as)
				_as.style.display = "";
		});
	}
	for(var k in inputErr){
		if(typeof elems[k] != "undefined"){
			elems[k].style.border = "1px solid red";

			as = elems[k].parentNode.getElementsByTagName("aside")[0];
			if(as)
				as.style.display = "none";
			
			errctl = document.createElement("span");
			errctl.innerHTML = inputErr[k];
			errctl.style.cssText = "color:red;font-size:12px;";
			elems[k].parentNode.insertBefore(errctl, as);

			fnclk(elems[k], errctl, as);
		}
	}
}

function submitForm(btn){
	btn.className +=' pure-input-disabled';
	btn.innerHTML+='...';
	btn.disabled=true;
	btn.form.submit();
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
	var allow_multi_opt = list[0].name.indexOf("[]") != -1, prev_checked_btn = null;
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
		if('1' == list[i].getAttribute("_checked")){
			_fnchecked(list[i]);
		}
		list[i].onclick = function(e){
			if('1' == this.getAttribute("_checked")){
				_fndechecked(this);
			}else{
				_fnchecked(this);
			}
		}
	}
}

