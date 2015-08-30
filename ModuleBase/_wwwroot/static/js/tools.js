Yee.tablePlace=function(objtb, tbstyle, tdtitles, tdcnts)
{
	if(!objtb || "TABLE" != objtb.tagName)
		return;
	var rows = objtb.rows, shape=shape || "ver", rl = rows.length, tbp = objtb.parentNode;
	if(rl < 2)
		return;
	ot = objtb.getAttribute("__tbshape__");
	if(!ot)
	{
		var cs = rows[0].cells, cl = cs.length, i=0, j=0, ntb=null, ntr, td1, td2,time=new Date().getTime(), ntc,nstyle;
		tbstyle = tbstyle || {};
		tdtitles = tdtitles || {};
		tdcnts = tdcnts || {};
		for(i=1; i<rl; i++)
		{
			ntb = document.createElement("table");
			ntb.setAttribute("__ver_idx__", time+"_"+i);
			
			$(ntb).css(tbstyle);
			ntb.style.margin = "5px 0";
			if("string" == typeof tbstyle)
				ntb.className = tbstyle+' '+ntb.className;
			else if("object" == typeof tbstyle)
				$(ntb).css(tbstyle);
			tbp.appendChild(ntb);
			ntc = rows[i].cells;
			for(j=0; j<cl; j++)
			{
				ntr = ntb.insertRow(j);
				td1 = ntr.insertCell(0);
				td2 = ntr.insertCell(1);
				$(td1).html(cs[j].innerHTML);
				$(td2).html(ntc[j].innerHTML);
				$(td1).css(tdtitles);
				$(td2).css(tdcnts);
				if("string" == typeof tdtitles)
					td1.className = tdtitles+' '+td1.className;
				else if("object" == typeof tdtitles)
					$(td1).css(tdtitles);
				if("string" == typeof tdcnts)
					td2.className = tdcnts+' '+td2.className;
				else if("object" == typeof tdcnts)
					$(td2).css(tdcnts);
			}
		}
		objtb.setAttribute("__tbshape__", time);
	}
	else
	{
		var chd = tbp.childNodes, i=0, del=[];
		for(; i<chd.length; i++)
		{
			if("TABLE" == chd[i].tagName && chd[i].getAttribute("__ver_idx__") 
					&&-1 != chd[i].getAttribute("__ver_idx__").indexOf(ot+"_"))
				del.push(chd[i]);
		}
		for(i=0; i<del.length; i++)
			tbp.removeChild(del[i]);
		objtb.setAttribute("__tbshape__", "");
	}
	
}