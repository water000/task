<?php 
$mbs_appenv->setLogAPI(null); // close the log in current page

mbs_import('core', 'CLogAPI');

$dblog = new CDBLogAPI(CDbPool::getInstance()->getDefaultConnection());

if(isset($_REQUEST['timeline'])){
    $ret = $dblog->read(intval($_REQUEST['timeline']));
    $out = array();
    $ret->setFetchMode(PDO::FETCH_ASSOC);
    foreach($ret as $row){
        unset($row['id']);
        $row['time'] = date('H:i:s', $row['time']);
        $out[] = $row;
    }
    $mbs_appenv->echoex($out);
    exit(0);
}
?>
<!doctype html>
<html>
<head>
<title>api log</title>
<style type="text/css">
body{font-size:12px;}
h1{text-align:center;}
ul{margin:0;padding:0;}
.title{font-size:14px;font-weight:bold;}
li{display:inline-block;padding:6px 3px;border-bottom:1px solid #ddd;word-break: break-word;  max-height: 380px;  overflow-y: auto;}
.title li{background-color:#eee;border-right:1px solid #ccc;}
.input{width:27%;}
.output{width:40%;}
.other{width:22%;}
.time{width:8%;border-right-width:0 !important;}
.status-bar{text-align:center;}
.status-bar .status a{margin-left:10px;}
.req-time{width:60px;margin:10px auto;background-color:#eee;text-align:center;padding:5px 0;}
#IDS_STA_REQP{display:none;}
</style>
</head>
<body>
    <h1>API LOG</h1>
    <ul class=title><li class=input>Input</li><li class=output>Output</li><li class=other>Other</li><li class=time>Time</li></ul>
    <p class=status-bar>
        <span class=status>
            <span id=IDS_STA_WAIT>wait: <i style="font-weight: bold;"></i> sec<a href="javascript:;">pause</a></span>
            <span id=IDS_STA_REQP><span>request...</span><span>, response: <b>0</b> rows.</span></span>
        </span>
        <span class=filter>| Filters:<input id=IDI_FLT_IP type="text" name="ip" placeholder="IP" value="" /></span>
    </p>
    <div id=IDD_RES_LIST>
    </div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('ajax.js')?>"></script>
<script type="text/javascript">
var REQ_INTERVAL = g_req_interval = 10, 
	g_req_interval_max = 60,
	g_timeline=<?php echo time()-5*60?>,
	sta_wait=document.getElementById("IDS_STA_WAIT"), 
	itv_wait=sta_wait.getElementsByTagName("i")[0],
	sta_btn=sta_wait.getElementsByTagName("a")[0],
	sta_reqp=document.getElementById("IDS_STA_REQP"),
	resp_rows=sta_reqp.getElementsByTagName("b")[0],
	res_list = document.getElementById("IDD_RES_LIST"),
	flt_ip = document.getElementById("IDI_FLT_IP");

function _prestart(){
	var counter = 0;
	sta_wait.style.display = "";
	sta_reqp.style.display = "none";
	var _fn = function(){
		itv_wait.innerHTML = g_req_interval-++counter;
		if(counter == g_req_interval){
			clearInterval(itv);
			_req();
		}
	}
	var itv = setInterval(_fn, 1000);
	sta_btn.onclick = function(){
		if("pause" == this.innerHTML){
			clearInterval(itv);
			this.innerHTML = "start";
		}else{
			itv = setInterval(_fn, 1000);
			this.innerHTML = "pause";
		}
	}
}
function _err(err){
	sta_reqp.childNodes[1].style.display = "inline-block";
	sta_reqp.childNodes[1].innerHTML += "<span style='color:red'>"+err+"</span>";
}
function _req(){
	sta_wait.style.display = "none";
	sta_reqp.style.display = "inline-block";
	sta_reqp.childNodes[1].style.display = "none";
	resp_rows.innerHTML = 0;
	var ret = ajax({
		url:"<?php echo $mbs_appenv->toURL('api_log')?>?timeline="+g_timeline, 
		dataType:"json", 
		success:function(list){
			g_timeline = (new Date()).getTime()/1000;
			eval("var _return="+list);
			if("SUCCESS" == _return.retcode){
				_resp(_return.data);
			}else{
				_err(_return.error);
			}
		},
		error:_err
	});
}
function _resp(list){
	resp_rows.innerHTML = list.length;
	sta_reqp.childNodes[1].style.display = "inline-block";
	_draw(list);
	_clear();
	if(0 == list.length ){ 
		if(g_req_interval >= g_req_interval_max){
			sta_wait.style.display = "inline-block";
			sta_reqp.style.display = "none";
			sta_btn.innerHTML = "restart";
			sta_btn.onclick = function(){
				_prestart();
				this.innerHTML = "pause";
			}
			return;
		}
		g_req_interval *= 2;
	}else g_req_interval = REQ_INTERVAL;
	setTimeout(function(){_prestart();}, 5000);
}
var _txt2html = function(txt){
	return txt.replace(/\n/g, "<br/>").replace(/ /g, "&nbsp;");
}
function _draw(list){
	var req_time = document.createElement("p"), d=new Date(),
		before = res_list.childNodes.length>0?res_list.childNodes[0]:null, ul;
	req_time.className = "req-time";
	req_time.innerHTML = d.getHours()+':'+d.getMinutes()+'('+list.length+')';
	res_list.insertBefore(req_time, before);
	for(var i=0; i<list.length; i++){
		if(flt_ip.value.length> 0 && -1 == list[i]["input"].indexOf("REMOTE_ADDR:"+flt_ip.value)) continue;
		ul = document.createElement("ul");
		ul.innerHTML += '<li class="input">'+_txt2html(list[i]["input"])+'</li>';
		ul.innerHTML += '<li class="output">'+_txt2html(list[i]["output"])+'</li>';
		ul.innerHTML += '<li class="other">'+_txt2html(list[i]["other"])+'</li>';
		ul.innerHTML += '<li class="time">'+list[i]["time"].replace(/\n/g, "<br/>")+'</li>';
		res_list.insertBefore(ul, before);
	}
}
function _clear(){
	while(res_list.childNodes.length > 50){
		res_list.removeChild(res_list.childNodes[res_list.childNodes.length-1]);
	}
}
_prestart();
</script>
</body>
</html>