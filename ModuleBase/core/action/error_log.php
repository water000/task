<?php
$tail_line = isset($_REQUEST['tail_line']) ? intval($_REQUEST['tail_line']) : 20;
$log_path = ini_get('error_log');
$error = '';
$tail = _mbs_tail($log_path, $tail_line, $error, $read_lines);
if($read_lines < $tail_line) $error = 'only '.$read_lines.' lines exsits';

function _mbs_tail($path, $lines, &$err, &$read_lines){
    $tail = '';
    $fp = fopen($path, 'rb');
    if(!$fp){
        $err = 'fail to open log using rb mod';
        return false;
    }
    for($n=1;$lines>0;++$n){
        if(-1 == fseek($fp, -1024*$n, SEEK_END)){
            break;
        }
        $str = fread($fp, 1024);
        for($i=strlen($str)-1; $i>0; --$i){
            if("\n" == $str[$i]){
                --$lines;
                ++$read_lines;
                if(0 == $lines) break;
            }
            $tail = $str[$i] . $tail;
        }
    }
    fclose($fp);
    return $tail;
}



?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
body{word-spacing:10px;background-color:#eee;}
h1{text-align:center;margin-bottom:10px;letter-spacing:normal;}
h1 span{font-size:12px;}
#IDD_WIN{width:90%;margin: 20px auto; color:#333;display:block;}
</style>
</head>
<body>
<div id="IDD_WIN" class="pure-g">
    <h1>TAIL PHP ERROR LOG</h1>
    <div style="background-color:white; padding:8px 6px;margin:5px 0;word-break: break-all;"><?php echo CStrTools::txt2html($tail)?></div>
    <?php if(!empty($error)){ ?><div class=error><?php echo $error;?></div><?php } ?>
    <div>
        <span style="float: left;">path:<?php echo $log_path?></span>
        <form action="" method="post" style="float: right;">
        tail number:<input type=text name=tail_line style="width: 35px;" value=<?php echo $tail_line?> />
        <input style="margin-left: 3px;" type=submit></form>
    </div>
    <div style="clear: both;"></div>
</div>
</body>
</html>