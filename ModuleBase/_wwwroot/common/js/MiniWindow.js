var MiniWindow = {
	arrins:[],
	max_zindex:10000,
	create:function(topstyle, contentstyle, opt){
		var ins = new this._miniWindow("z-index:"+this.max_zindex++, topstyle+";z-index:"+this.max_zindex++, contentstyle, opt);
		this.arrins.push(ins);
		return ins;
	},
	del:function(ins){
		for(var i=0; i<this.arrins.length; i++){
			if(this.arrins[i] == ins){
				ins.del();
				this.arrins.splice(i, 1);
				return true;
			}
		}
		return false;
	},
	_miniWindow:function(bgstyle, topstyle, contentstyle, opt){
		var specTop,specLeft, defWidth = 540, defHeight = 500,contentHeight
		, _dc=function(t){return document.createElement(t)}, abscenter, bShowing = false
		, oBgRoot = _dc("div"), oTopRoot = _dc("div"), oTitle = null,_this = this,bInit = false
		, oBtnClose, oBtnSure, oBtnCancle, oContent = null, oCenterContent=null, oOperBar = null, btnbar = true,oContentP = null,
		oDefOpt = {title:"弹出框", content:"", btnbar:true, abscenter:0, onclose:function(){}, onsure:function(){return true;}, oncancle:function(){}};
		specTop = topstyle.match(/top\s*:\s*(\d+(?:px)?)\s*;?/i);
		specLeft = topstyle.match(/left\s*:\s*(\d+(?:px)?)\s*;?/i);
		specTop = specTop ? parseInt(specTop[1]):-1;
		specLeft = specLeft ? parseInt(specLeft[1]):-1;
		opt = opt || {};
		var shadowTopHeight = 3, shadowBottomHeight = 3, contentBorderWh = 5,
			shadowLeftWidth = shadowRightWidht = 3, btnbarPaddingTB = 3;
		var _bind = function(obj, evttype, handle){
			if(!obj) return;
			if(obj.attachEvent)
				obj.attachEvent("on" + evttype, handle);
			else
				obj.addEventListener(evttype, handle, false);
		}
		this.bgcss=function(opt){
			for(var k in opt)
				oBgRoot.style[k] = opt[k];
		}
		this.topcss=function(opt){
			for(var k in opt)
				oTopRoot.style[k] = opt[k];
		}
		this.show=function(top_opt){
			bShowing = true;
			oBgRoot.style.display = 'block';
			oTopRoot.style.display = 'block';
			if('' == oContentP.innerHTML)
				oContent.style.height = "0px";
			oOperBar.style.display = btnbar ? "block" : "none";
			var _h = oTopRoot.offsetHeight, _w = oTopRoot.offsetWidth, 
			dd = document.documentElement, dw = dd.scrollWidth, dh = dd.scrollHeight,
			t = -1 == specTop ? (dd.clientHeight/2-_h/2+dd.scrollTop) : specTop,
			l = -1 == specLeft ? (dd.clientWidth/2-_w/2+dd.scrollLeft) : specLeft;
			this.bgcss({width:dw+"px", height:dh+"px"});
			top_opt = top_opt || {};
			top_opt.top = top_opt.top || (t+"px");
			top_opt.left = top_opt.left || (l+"px");
			this.topcss(top_opt);
			setTimeout(function(){bShowing = false;}, 20);//prevent the window.onresize event fired in time
		}
		this.hide=function(){
			this.bgcss({display:"none"});
			this.topcss({display:"none"});
		}
		this.title = function(t){
			if(!oTitle)
				return;
			oTitle.innerHTML = t;
		}
		this.append=function(arg){
			if(!oContent)
				return;
			var t = typeof arg, cnt = oContentP || oContent;
			if(t == "object"){
				cnt.appendChild(arg);
				arg.style.display = "";
			}else if(t == "string"){
				cnt.innerHTML += arg;
			}
		}
		this.clear=function(){
			if(!oContent)
				return;
			var cnt = oContentP || oContent;
			cnt.innerHTML = '';
		}
		this.del=function(){
			document.body.removeChild(this.oTopRoot);
			document.body.removeChild(this.oBgRoot);
		}
		this.getWindow=function(){return oContent;}
		this.getTitle = function(){return oTitle;}
		this.getRoot = function(){return oTopRoot;}
		this.getContentHeight = function(){return contentHeight;}//oDefOpt = {title:"弹出框", content:"", btnbar:true, onclose:function(){}, onsure:function(){}, oncancle:function(){}};
		this.setOpt = function(opt){
			if(!this.bInit || "undefined" != typeof(opt.onsure))
				oBtnSure.onclick = function(e){if((opt.onsure||oDefOpt.onsure)()) _this.hide();}
			if(!this.bInit || "undefined" != typeof(opt.oncancle))
				oBtnCancle.onclick = function(e){_this.hide();(opt.oncancle||oDefOpt.oncancle)();}
			if(!this.bInit || "undefined" != typeof(opt.onclose))
				oBtnClose.onclick = function(e){_this.hide();(opt.onclose||oDefOpt.onclose)();}
			if(!this.bInit || "undefined" != typeof(opt.title))
				this.title(opt.title||oDefOpt.title);
			if(!this.bInit || "undefined" != typeof(opt.btnbar))
				btnbar = "undefined" == typeof opt.btnbar ? oDefOpt.btnbar : opt.btnbar;
			if(!this.bInit || "undefined" != typeof(opt.abscenter))
				abscenter = opt.abscenter || oDefOpt.abscenter;
		}
		contentstyle = contentstyle || "";
		topstyle = topstyle || "";
		oBgRoot.style.cssText = "top:0;left:0;width:0;height:0;"+bgstyle+";display:none;";
		oTopRoot.style.cssText = "top:0;left:0;"+topstyle+";display:none;";
		oBgRoot.id = "mini-window-screen";
		oTopRoot.id = "mini-window-window";
		
		//shadowTopHeight = 5, shadowBottomHeight = 3, shadowLeftWidth = shadowRightWidht = 3;
		var _d = document, db = _d.body, _fn=function(){
			db = _d.body;
			db.appendChild(oBgRoot);
			db.appendChild(oTopRoot);
			oTopRoot.innerHTML = '\
				<div class="up-border" style="height: '+shadowTopHeight+'px;">\
					<div class="mub-1"></div>\
					<div class="mub-2"></div>\
					<div class="mub-3"></div>\
				</div>\
				<div class="center-content">\
					<div class="title-bar">\
						<p class="title"></p><a name="close" href="javascript:;" class="close-btn" >×</a>\
					</div>\
					<div class="body-client">\
						<div class="content-client"></div>\
						<div class="content-oper">\
							<input type="button" value="确定" class="green button" /><input class="green button input-cancle" type="button" value="取消" />\
						</div>\
					</div>\
				</div>\
				<div class="down-border" style="height: '+shadowBottomHeight+'px;">\
					<div class="mub-4">&nbsp;</div>\
					<div class="mub-5">&nbsp;</div>\
					<div class="mub-1">&nbsp;</div>\
				</div>';
			var arr = oTopRoot.getElementsByTagName("div"), i,cn;
			for(i=0; i<arr.length; i++){
				cn = arr[i].className;
				if("title-bar" == cn){
					oTitle = arr[i].getElementsByTagName("p")[0];
				}
				else if("content-client" == cn){
					oContent = arr[i];
				}
				else if("content-oper" == cn){
					oOperBar = arr[i];
				}
				else if("center-content" == cn){
					oCenterContent = arr[i];
				}
			}
			arr = oTopRoot.getElementsByTagName("a");
			for(i=0; i<arr.length; i++){
				if("close" == arr[i].name){
					oBtnClose = arr[i];
					break;
				}
			}
			arr = oTopRoot.getElementsByTagName("input");
			oBtnSure = arr[0];
			oBtnCancle = arr[1];
			_this.setOpt(opt);
			
			oContentP = _dc("div");
			oContent.appendChild(oContentP);
			oContentP.style.cssText = contentstyle;
			
			_this.append(opt.content||oDefOpt.content);
			_bind(window, "resize", function(){if(bShowing)return;if(oTopRoot.style.display != 'none') _this.show();});
			_this.bInit = true;
		};

		if(db){
			_fn();
		}else{
			if(_d.attachEvent)
				_d.attachEvent("onreadystatechange", _fn);
			else
				_d.addEventListener("DOMContentLoaded", _fn, false);
		}
	}
}