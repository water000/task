<?php
$tail_line = isset($_REQUEST['tail_line']) ? intval($_REQUEST['tail_line']) : 10;
$log_path = ini_get('error_log');
$error = '';
$tail = _mbs_tail($log_path, $tail_line, $error, $read_lines);
if($read_lines < $tail_line) $error = 'only '.$read_lines.' lines exsits';

function _mbs_tail($path, $line, &$err, &$read_lines){
    $tail = '';
    $fp = fopen($path, 'rb');
    if(!$fp){
        $err = 'fail to open log using rb mod';
        return false;
    }
    for($n=1;$line>0;++$n){
        if(-1 == fseek($fp, -1024*$n, SEEK_END)){
            break;
        }
        $str = fread($fp, 1024);
        for($i=strlen($str)-1; $i>0; --$i){
            if("\n" == $str[$i]){
                $line--;
                ++$read_lines;
                if(0 == $line) break;
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
h1{text-align:center;margin-bottom:15px;letter-spacing:normal;}
h1 span{font-size:12px;}
#IDD_WIN{width:90%;margin: 20px auto; color:#333;display:block;}
</style>
</head>
<body>
<div id="IDD_WIN" class="pure-g">
    <h1>PHP ERROR LOG<span>(<?php echo $log_path?>)</span></h1>
    <div style="background-color:white; padding:10px;margin-bottom:20px;"><?php echo CStrTools::txt2html($tail)?></div>
    <?php if(!empty($error)){ ?><div class=error><?php echo $error;?></div><?php } ?>
    <div><form action="" method="post">tail number:<input type=text name=tail_line value=<?php echo $tail_line?> />
        <input style="margin-left: 10px;" type=submit></form></div>
</div>
</body>
</html>