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
		$row['time'] = date('H:i:s');
		$out[] = $row;
	}
	$mbs_appenv->echoex($out);
	exit(0);
}
$ret = $dblog->read(time()-5*60);
?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
body{word-spacing:10px;}
h1{text-align:center;margin-bottom:15px;letter-spacing:normal;}
#IDD_WIN{width:99%;margin: 20px auto;color:#333;display:block;}
._title{font-weight:bold; font-size:120%;text-align:center;}
._title .pure-u-1-4{border:1px solid #ddd;padding:5px 0;}
.bg_gray{background-color:#eee;}
#STATUS_BAR{position:fixed;bottom:0;right:0;width:25%;font-size:80%;padding:0.3%;background-color:green;color:white;text-align:center;letter-spacing:normal;}
#STATUS_BAR a{color:yellow;}
.pure-u-1-4{width:27%;margin:1% 0 0 1%;word-break: break-word;}
.pure-u-1-10{width:9%;}
</style>
</head>
<body>
<div id="IDD_WIN" class="pure-g">
    	<h1>API LOG</h1>
    	<div class="pure-g _title">
    		<div class="pure-u-1-4">INPUT</div>
    		<div class="pure-u-1-4">OUTPUT</div>
    		<div class="pure-u-1-4">OTHER</div>
    		<div class="pure-u-1-4 pure-u-1-10">TIME</div>
    	</div>
    	<?php foreach($ret as $k => $row){?>
    	<div class="pure-g <?php echo 1== $k%2 ? ' bg_gray':'' ?>">
    		<div class="pure-u-1-4"><?php echo CStrTools::txt2html($row['input'])?></div>
    		<div class="pure-u-1-4"><?php echo CStrTools::txt2html($row['output'])?></div>
    		<div class="pure-u-1-4"><?php echo CStrTools::txt2html($row['other'])?></div>
    		<div class="pure-u-1-4 pure-u-1-10"><?php echo date('H:i:s')?></div>
    	</div>
    	<?php } ?>
</div>
<div id="STATUS_BAR"></div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('ajax.js')?>"></script>
<script type="text/javascript">
var g_num = <?php echo isset($k) ? $k : -1;?>, g_win=document.getElementById("IDD_WIN"), 
		g_status_bar = document.getElementById("STATUS_BAR"), 
		g_obefore = g_win.getElementsByTagName("div")[5]||null, g_timeline=<?php echo time()?>;
var _draw = function(list){
	var div, hr = document.createElement("hr");

	g_win.insertBefore(hr, g_obefore);
	g_obefore = hr;
	
	for(var i=0; i<list.length; i++){
		g_num++;
		div = document.createElement("div");
		div.className = "pure-g" + (1==g_num%2?" bg_gray":"");
		div.innerHTML += '<div class="pure-u-1-4">'+list[i]["input"].replace(/\n/g, "<br/>")+'</div';
		div.innerHTML += '<div class="pure-u-1-4">'+list[i]["output"].replace(/\n/g, "<br/>")+'</div';
		div.innerHTML += '<div class="pure-u-1-4">'+list[i]["other"].replace(/\n/g, "<br/>")+'</div';
		div.innerHTML += '<div class="pure-u-1-4 pure-u-1-10">'+list[i]["time"].replace(/\n/g, "<br/>")+'</div';
		g_win.insertBefore(div, g_obefore);
		g_obefore = div;
	}
}
var g_req_count = 0, g_sec_interval = 10, g_def_msg = 'waiting '+g_sec_interval+' seconds...';
g_status_bar.innerHTML = g_def_msg;
var g_thandle;
var _start = function(){
	g_thandle = setInterval(function(){
		g_status_bar.innerHTML = g_def_msg;
		var ret = ajax({
			url:"<?php echo $mbs_appenv->toURL('api_log')?>?timeline="+g_timeline, 
			dataType:"json", 
			success:function(list){
				eval("var _return="+list);
				if("SUCCESS" == _return.retcode && _return.data.length > 0){
					_draw(_return.data);
				}
				g_status_bar.innerHTML = "[" + ++g_req_count 
					+ "]&nbsp;"+g_status_bar.innerHTML+"(ret-num:"+(_return.data||[]).length
					+")&nbsp;<a href='javascript:;' onclick='_aclick(this)'>pause</a>";
			}
		});
		g_timeline += 10;
	}, 10000);
}
_start();
var _aclick = function(obj){
	if("pause" == obj.innerHTML){
		clearInterval(g_thandle);
		g_status_bar.innerHTML = "paused&nbsp;<a href='javascript:;' onclick='_aclick(this)'>start</a>";
		obj.innerHTML = "start";
	}else{
		_start();
		g_status_bar.innerHTML = g_def_msg;
		obj.innerHTML = "pause";
	}
}
</script>
</body>
</html>