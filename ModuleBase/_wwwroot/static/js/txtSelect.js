$.txtSelect = function(inptxt, onkeyup, onchange, onclick, widthOffset){
	onchange = onchange || function(a){inptxt.value = a==null ? keyval : a.innerHTML.stripTags();};
	onclick = onclick || function(a){};
	onkeyup = onkeyup || function(a){};
	widthOffset = widthOffset || 0;
	var view = document.createElement("div"), unsel,sellist, cur=null, prev=null,selfClicked=false,
	ret = {
		getCurNode:function(){return cur;},
		getList:function(){return sellist;},
		show:function(){view.style.display = "block";},
		hide:function(){view.style.display = "none";}
	};
	view.innerHTML = "<div class='unsel'></div><div class='sel'><ul></ul></div>";
	var pos = $.pos(inptxt),kc=-1, keyval = '', inpval = inptxt.value, done = true, waitVal='';
	document.body.appendChild(view);
	view.className = "txt-select";
	view.style.cssText = "top:"+(pos[0]+inptxt.offsetHeight+1)+"px;left:"+pos[1]+"px;";
	view.style.width = (inptxt.offsetWidth+widthOffset)+"px";
	unsel = view.firstChild;
	sellist = view.lastChild.firstChild;
	var _fnTo = function(target){if(null!=cur) cur.className='';prev=cur;cur=target;if(null!=cur) cur.className="selected";},
	_fnChange = function(target){onchange(target, keyval);},
	_fnDrawList = function(arr){
		ret.hide();
		cur=prev=null;
		var i=0,j=arr.length,node,cns=sellist.childNodes, cj = cns.length;
		if(j > 0){
			for(;i<j;i++){
				if(i<cj){
					node = cns[i];
					node.className = '';
				}else{
					node = document.createElement("li");
					sellist.appendChild(node);
				}
				if("object"==typeof(arr[i]))
					node.appendChild(arr[i]);
				else
					node.innerHTML = arr[i];
				node.onmouseover=function(){_fnTo(this);_fnChange(this);};
				node.onclick=function(){selfClicked=true;onclick(this.innerHTML);ret.hide();};
			}
			while(cj > j){sellist.removeChild(sellist.lastChild);cj--;}
			ret.show();
		}
	},
	_fnOnFinish = function(v, kc){if(waitVal != '' && waitVal != v){ _fnDo(waitVal, kc);waitVal='';}},
	_fnDo = function(v, kc){
		done = false;
		var did = function(arr){_fnDrawList(arr);done=true;_fnOnFinish(v, kc);},
		ret = onkeyup(inptxt.value, did, kc);
		inpval = v;
		if("[object Array]" === Object.prototype.toString.call(ret))
			did(ret);
	};
	$(inptxt).keyup(function(ev){
		kc = ev.keyCode;
		if(38 == kc || 40 == kc && sellist.length != 0){
			var target = 38 == kc ? (cur == null ? sellist.lastChild : cur.previousSibling) : 
				(cur == null ? sellist.firstChild : cur.nextSibling);
			_fnTo(target);
			_fnChange(target);
		}else{
			keyval = inptxt.value;
			if(done){
				if(inptxt.value != inpval)
					_fnDo(inptxt.value, kc);
			}else waitVal = inptxt.value;
		}
	});
	$(document.body).click(function(){if(!selfClicked) ret.hide(); else selfClicked=false;});
	$(inptxt).click(function(){selfClicked=true;});
	return ret;
}