//select(), settings{vertical:false, num:0, className:"", cssText:"", 
//offsetLeft:0, offsetTop:-1, onchange:null, printOnZeror:true, scrollStart:3, scrollOffset:3, scrollStep:1}

//add(), value, html, opt{className, cssText, selected}
//Yee.pos()
Yee.select = function(settings)
{
	var CSelect = function(_set)
	{
		var settings = _set, top = 0, left = 0, offsetTop, offsetLeft
		, _div=null, _table=null, autoAppendTd=false, _this = this,total = 0
		, _dc = function(t){return document.createElement(t)}
		, _de = function(t){return document.getElementById(t)}
		, objroot , objsrc, objview= _dc("div") , objarrow = _dc("div") 
		, defset = {parent:null, className:"", cssText:"border:1px solid #D4D4D4;", arrowImg:"TAG_URL_IMG(user, arrow_down.png)"/*w:29,h:28*/};
		var init = function()
		{
			if(!settings.parent)
			{
				var rand = Math.random(), rid = "root_"+rand;
				document.write("<div id='"+rid+"'></div>");
				objsrc = objroot = _de(rid);
			}
			else
			{
				objroot = _dc("div");
				objsrc = settings.parent;
				objsrc.appendChild(objroot);
			}
		}
		init();
		var num, vertical, printOnZero, scrollStart, scrollOffset, scrollStep, bScrolled, _event ;
		this.reset = function(opt)
		{
			var opt = opt || settings;
			objroot.className = opt.className || defset.className;
			objroot.style.cssText = opt.cssText || defset.cssText;
			
			var borderHeight = 1
			, _roh = objsrc.offsetHeight-borderHeight*2
			, _row = objsrc.offsetWidth-borderHeight*2;
			objroot.style.height = _roh+"px"
			if(0 == objroot.childNodes.length)
			{
				objroot.appendChild(objview);
				objroot.appendChild(objarrow);
				//objview.innerHTML = "";
				objarrow.innerHTML = "<img src='"+(opt.arrowImg||defset.arrowImg)+"' />";
			}
			var dw = 29, _w=0, dh = 28, h = 28, img = objarrow.firstChild, img_base_margin = 2, wh_rate = dw/dh, img_margin = Math.round (wh_rate*img_base_margin);
			h = _roh-img_margin*2;
			_w = Math.round (wh_rate * h);
			img.style.cssText = "margin:0;padding:0;border:0;width:"+_w+"px;height:"+h+"px;"
			objview.style.cssText = "text-align:center;height:"+h+"px;line-height:"+h+"px;width:"+(_row-(_w+img_margin*2)-img_margin*2-(is_ie6?4:0))+"px;overflow:hidden;float:left;";
			objarrow.style.cssText = "width:"+_w+"px;height:"+h+"px;float:right;";
			objarrow.style.margin = objview.style.margin = img_margin + "px";
			
			this.selectedIndex = -1;
			this.options = [];
			_event = null;
			offsetLeft = opt.offsetLeft||0;
			offsetTop = typeof(opt.offsetTop) == "undefined" ? -1 : parseInt(opt.offsetTop);
			printOnZero = opt.printOnZero === false ? false : true;
			autoAppendTd=false;
			total = 0;
			if(null != _div)//by default, _div=_table=null
			{
				while(_table.rows.length)
					_table.deleteRow(_table.rows.length-1);
				var ch = _div.childNodes;
				if(3 == ch.length)//remove scroll bar
				{
					_div.removeChild(_div.firstChild);
					_div.removeChild(_div.lastChild);
				}
			}
			num = opt.num || 0;
			vertical = typeof(opt.vertical)=="undefined" ? true : opt.vertical;
			bScrolled = typeof(opt.scrollStart) == "undefined" ? false : true;
			scrollStart = opt.scrollStart || 0;
			scrollOffset = opt.scrollOffset || 1;
			scrollStep = opt.scrollStep || 1;
		}
		this.reset(settings);
		this.onchange = settings.onchange||null;
		this.onclick = settings.onclick || function(){};
		_div = document.createElement("div");
		document.body.appendChild(_div);
		_table = document.createElement("table");
		_div.appendChild(_table);
		_div.style.cssText = "position:absolute;display:none;";
		_table.style.cssText = "border:1px solid #D3DAED;background-color:white;";
		objroot.onclick = function(event){if(_this.hasShown())_this.hide();else _this.show(event||window.event);_this.onclick.call(this,event||window.event);}
		var b = document.body,fn = function(event){
			if(!_event)return;
			//event.clientX == _event._cx_ (FOR IE ONLY) 
			if(event == _event || event.clientX == _event._cx_)return;
			if(_this.hasShown())_this.hide();
		};
		$(b).bind("click", fn);
		$(window).bind("resize", function(){var a = Yee.pos(objsrc);top=a[0]+objsrc.offsetHeight;left=a[1];_this.resize(offsetLeft);})
		var preopt = null,
		overCss = {backgroundColor:"#1E5fd3", color:"white", cursor:"pointer", textAlign:"center", padding:"2px 3px"},
		outCss = {backgroundColor:"white", color:"black", textAlign:"center", padding:"2px 3px"},
		initBg = function(){
			if(-1 != _this.selectedIndex)
			{
				preopt = _this.options[_this.selectedIndex];
				$(preopt).css(overCss);
			}
		},
		_bg = function(obj, css){//initBg may be called before _bg
			css = css || 'over';
			if(css == 'over' && preopt)
			{
				$(preopt).css(outCss);
				preopt = null;
			}
			css = 'over' == css ? overCss : outCss;
			$(obj).css(css);
		},
		_change = function(event, cur){
			_this.hide();
			if(cur == _this.selectedIndex) return;
			_this.selectedIndex = cur;
			var opt = this;
			objview.innerHTML = opt.innerHTML;
			initBg();
			if(event != null)
				(_this.onchange||function(){}).call(_this,event);
		},
		_cell=function(cl, cur)
		{
			cl.onclick = function(event){_change.call(this, event||window.event, cur);};
			cl.onmouseover = function(){_bg(this, 'over');}
			cl.onmouseout = function(){if(_this.hasShown()) _bg(this, 'out')}//use the condition to prevent that the event was occured when "onchange" occuring
		};
		this.add = function(value, html, opt)
		{
			var r, cell;
			if(vertical)
			{
				r = (num != 0 && total >= num) ? _table.rows[total%num] : _table.insertRow(-1);
			}
			else
			{
				if(0 == total)
					r = _table.insertRow(-1);
				else
				{
					if(0 == num)
						r = _table.rows[0];
					else if(0 == total %num )
						r = _table.insertRow(-1);
					else
						r = _table.rows[Math.floor(total/num)];
				}
			}
			cell = r.insertCell(-1);
			_this.options[total] = cell;
			cell.style.cssText = (opt.cssText || "");
			$(cell).css(outCss);
			cell.className = opt.className || "";
			cell.innerHTML = html||"";
			cell.setAttribute("value", value||"");
			cell.value = value||"";
			_cell(cell, total);
			if(opt.selected)
				_change.call(cell, null, total);
			if(printOnZero && 0 == total)
				_change.call(cell, null, 0);
			total++;
			if(cell.offsetWidth > objview.offsetWidth)
				objview.style.width = cell.offsetWidth + "px";
		}
		var scroll = function()
		{
			if(!bScrolled)
				return;
			var cols = _table.rows[0].cells.length, rows = _table.rows.length, cur = scrollStart;
			if(vertical)
			{
				if(cols == scrollStart + scrollOffset)//no need to scroll
					return;
			}
			else
			{
				if(rows == scrollStart + scrollOffset)//no need to scroll
					return;
			}
			var _func = function()
			{
				var tmp;
				for(var i=0, j, r=_table.rows,c; i<rows; i++)
				{
					for(j=0, c=r[i].cells; j<cols; j++)
					{
						tmp = vertical ? j : i;
						c[j].style.display = (tmp<cur||tmp>=cur+scrollOffset) ? "none" : "";
					}
				}
			}
			_func();
			var back = _dc("div"), fwd = _dc("div"), bc = _dc("div"), fc = _dc("div"), rc, bmbg = _dc("div");
			_div.insertBefore(back, _table);
			_div.appendChild(fwd);
			back.appendChild(bc);
			fwd.appendChild(fc);
			if(vertical)
			{
				var h = _table.offsetHeight;
				fwd.style.cssText = back.style.cssText = "float:left;width:7px;height:"+h+"px;";
				fc.style.cssText = bc.style.cssText = "cursor:pointer;height:"+h+"px;line-height:"+h+"px;display:none;";
				_table.style.styleFloat = "left";
				_table.style.cssFloat = "left";
				bc.innerHTML =  "<b>&lt;</b>";
				fc.innerHTML = "<b>&gt;</b>";
				rc = cols;
				_div.appendChild(bmbg);
				bmbg.style.cssText = "height:4px;width:"+(_div.offsetWidth-3.2)+"px;margin:0 1.6px;background:#D3DAED;clear:both;";
			}
			else
			{
				fc.style.cssText = bc.style.cssText = "cursor:pointer;text-align:center;height:100%;line-height:100%;display:none;font-size:7px;";
				bc.innerHTML =  "¡Ä";
				fc.innerHTML = "¡Å";
				rc = rows;
				_table.style.borderWidth = "0 3px";
				_table.style.borderStyle = "solid";
				_table.style.borderColor = "#D3DAED";
				fwd.style.cssText = back.style.cssText = "height:7px;width:"+_div.offsetWidth+"px;";
			}
			fwd.style.backgroundColor = back.style.backgroundColor = "#D3DAED";
			bc.style.display = cur == 0 ? "none" : "";
			fc.style.display = cur + scrollOffset >= rc ? "none" : "";
			bc.onclick = function(event){
				cur = cur - scrollStep;
				cur = cur < 0 ? 0 : cur;
				bc.style.display = cur == 0 ? "none" : "";
				fc.style.display = cur + scrollOffset >= rc ? "none" : "";
				_func();
				_event = event||window.event;
				_event._cx_ = _event.clientX;
			}
			fc.onclick = function(event){
				cur = cur + scrollStep;
				cur = cur >= rc ? rc-1 : cur;
				bc.style.display = cur == 0 ? "none" : "";
				var to = cur+scrollOffset;
				if(to > rc)
					cur = cur-(to-rc);
				fc.style.display = to >= rc ? "none" : "";
				_func();
				_event = event||window.event;
				_event._cx_ = _event.clientX;
			}
		}
		var _auto = function()
		{
			if(total <= num)
				return;
			var s, i=0, j;
			if(vertical)
			{
				s = _table.rows[0].cells.length*num;
				for(j = s-total; i<j; i++)
					_table.rows[total%num+i].insertCell(-1);
			}
			else
			{
				s = _table.rows.length * num;
				var r = _table.rows[Math.floor(total/num)];
				for(j=s-total; i<j; i++)
					r.insertCell(-1);
			}
		}
		this.show = function(_ev)
		{
			initBg();
			_div.style.display = "";
			if(!autoAppendTd)
			{
				_auto();
				scroll();
				autoAppendTd = true;
				var arr = Yee.pos(objsrc);
				top = arr[0]+objsrc.offsetHeight;//used only for IE on reseting the top and left
				left = arr[1];
			}
			if(_ev)
			{
				_event = _ev;
				_event._cx_ = _event.clientX;//set the "_cx_" to prevent the confusion that the event object in IE is a global
			}
			this.resize(offsetLeft);
		}
		this.hide = function()
		{
			if(!_div)
				return;
			_div.style.display = "none";
			_event=null;
		}
		this.hasShown = function()
		{
			return (_div && _div.style.display == "");
		}
		this.resize = function(ol)
		{
			var dd = document.documentElement, cw = dd.clientWidth+dd.scrollLeft, _lf = left+ol;
			if(_lf+_table.offsetWidth>cw)
				_lf = cw-_table.offsetWidth;
			else if(_lf < dd.scrollLeft)
				_lf = dd.scrollLeft;
			_div.style.left = _lf+"px";
			_div.style.top = (top+offsetTop)+"px";
		}
	}
	return new CSelect(settings);
}