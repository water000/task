
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
