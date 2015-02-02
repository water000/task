var HandleDragDrop = function(bindedObj, cb_down, cb_move, cb_up){
	var _x, _y, _cx, _cy, _ox, _oy, _this = this, _bDown
	,_fn_d = function(ev){down(ev||event, cb_down||function(){});}
	,_fn_u = function(ev){up(ev||event, cb_up||function(){});}
	,_fn_m =function(ev){move(ev||event, cb_move||function(){});}
	,down = function(ev, func){
		if((ev.srcElement||ev.target) != bindedObj)
			return;
		_ox = ev.offsetX||ev.layerX;
		_oy = ev.offsetY||ev.layerY;
		if(document.attachEvent){
			_ox -= bindedObj.scrollLeft;
			_oy  -= bindedObj.scrollTop;
		}
		_cx = _x = ev.clientX;
		_cy = _y = ev.clientY;
		_bDown = true;
		func({cx:_cx, cy:_cy, ox:_ox, oy:_oy});
	}
	,move = function(ev, handle){
		if(!_bDown || !handle) return;
		var cx=ev.clientX, cy=ev.clientY, ox, oy;
		if((ev.srcElement||ev.target) != bindedObj)
			return;
		ox = ev.offsetX||ev.layerX;
		oy = ev.offsetY||ev.layerY;
		if(document.attachEvent){
			ox -= bindedObj.scrollLeft;
			oy  -= bindedObj.scrollTop;
		}
		handle({cx:cx, cy:cy, ox:ox, oy:oy, w:(cx-_x), h:(cy-_y)});
		_x = cx;
		_y = cy;
	}
	,up = function(ev, func){
		_bDown = false;
		var cx=ev.clientX, cy=ev.clientY, ox, oy;
		if((ev.srcElement||ev.target) != bindedObj)
			return;
		ox = ev.offsetX||ev.layerX;
		oy = ev.offsetY||ev.layerY;
		if(document.attachEvent){
			ox -= bindedObj.scrollLeft;
			oy  -= bindedObj.scrollTop;
		}
		func({cx:cx, cy:cy, ox:ox, oy:oy, w:(_cx-cx), h:(_cy-cy)});
	}
	,del = function(){
		var _fn_unbind = function(obj, eventType, hand){
			if(obj.detachEvent)
				obj.detachEvent('on'+eventType, hand);
			else if(obj.removeEventListener)
				obj.removeEventListener(eventType, hand, true);
		}
		_fn_unbind(bindedObj, "mousedown", _fn_d);
		_fn_unbind(bindedObj, "mouseup", _fn_u);
		//_fn_unbind(document.body, "mouseup", _fn_u);
		_fn_unbind(bindedObj, "mousemove", _fn_m);
	}
	,init = function(){
		x=y=cx=cy=ox=oy=0;
		_bDown = false;
		var _fn_bind = function(obj, eventType, hand){
			if(obj.attachEvent)
				obj.attachEvent('on'+eventType, hand);
			else if(obj.addEventListener)
				obj.addEventListener(eventType, hand, true);
		}
		_fn_bind(bindedObj, "mousedown", _fn_d);
		_fn_bind(bindedObj, "mouseup", _fn_u);
		//_fn_bind(document.body, "mouseup", _fn_u);
		_fn_bind(bindedObj, "mousemove", _fn_m);
	}
	init();
}