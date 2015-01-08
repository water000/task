<?php
header('Content-Type: text/html; charset=utf-8');

$valid = true;
$notice = array();

$dependlib = array('zip_open', 'iconv');
$dependclass = array('PDO');
if(!class_exists('Memcached'))
	$dependclass[] = 'Memcache';
$notInstalledMod = array();
foreach($dependlib as $lib){ 
	if(!function_exists($lib)){
		$notInstalledMod[] = $lib;
		$valid = false;
	}
}
foreach($dependclass as $class){ 
	if(!class_exists($class)){
		$notInstalledMod[] = $class;
		$valid = false;
	}
}
if(count($notInstalledMod) > 0){
	$notice[] = '<b>未安装模块：</b>'.implode(',', $notInstalledMod);
}

if(!is_writable('.')){
	$valid = false;
	$notice[] = '<b>需要目录可写：</b>'.dirname(__FILE__);
}

define('MOD_PATH', '_mod/%s.zip');

$defmod = array('core', 'common');
$loser = array();
foreach($defmod as $mod){
	if(!file_exists(sprintf(MOD_PATH, $mod))){
		$valid = false;
		$loser[] = $mod;
	}
}
if(count($loser) > 0){
	$notice[] = '<b>丢失安装模块文件：</b>'.implode(',', $loser);
}

$error = array();
if($valid && isset($_REQUEST['sysroot'])){
	$sysroot = trim($_REQUEST['sysroot']);
	if(!file_exists($sysroot))
		$error[] = sprintf('sysroot "%s" not exists', $sysroot);
	else if(!is_writable($sysroot) || !is_readable($sysroot))
		$error[] = sprintf('sysroot "%s" is not writable or readable', $sysroot);
	$c = $sysroot[strlen($sysroot)-1];
	$sysroot .= $c == '/'|| $c == '\\' ? '' : '/';
	
	$site_name = trim($_REQUEST['site_name']);
	if(empty($site_name)){
		$error[] = 'site_name can not be empty';
	}
	$site_name= htmlspecialchars($site_name);
		
	$dbhost = trim($_REQUEST['db_host']);
	$dbport = trim($_REQUEST['db_port']);
	$dbname = trim($_REQUEST['db_name']);
	$dbuser = trim($_REQUEST['db_user']);
	$tbpre  = trim($_REQUEST['tbpre']);
	$charset = trim($_REQUEST['charset']);
	$charset = empty($charset)? 'utf-8' : $charset;
	$dsn = sprintf('mysql:host=%s;port=%d',  
		$dbhost, $dbport);
	try{
		$pdo = new PDO($dsn, trim($_REQUEST['db_user']), $_REQUEST['db_pwd']);
		$count = 1;
		$pdo->query(sprintf('CREATE DATABASE IF NOT EXISTS %s CHARACTER SET %s',
			$dbname, str_replace('-', '', $charset, $count)));
		$pdo->query('use '.$dbname);
	}catch (PDOException $e){
		$error[] = $e->getMessage();
	}
	if(strcasecmp('utf-8', $charset) != 0)
		$site_name = iconv('utf-8', $charset, $site_name);
	
	$memhost = trim($_REQUEST['mem_host']);
	if($memhost != ''){
		$memport = intval(trim($_REQUEST['mem_port']));
		$fp = fsockopen($memhost, $memport, $errno, $err, 5);
		if(!$fp)
			$error[] = sprintf('unable to connect memached host(%s) on port(%d)', $memhost, $memport);
		else
			fclose($fp);
	}
	
	if(0 == count($error)){
		$zip = new ZipArchive;
		foreach($defmod as $mod){
			$res = $zip->open(sprintf(MOD_PATH, $mod));
			if(true === $res){
				$zip->extractTo($sysroot); // for installing modules in later
			}else exit(sprintf('failed to open "%s"', sprintf(MOD_PATH, $mod)));
			$zip->close();
		}
		sleep(25);
		$slashpos = strrpos($_SERVER['PHP_SELF'], '/');
		$webroot = $slashpos === false ? '/' : substr($_SERVER['PHP_SELF'], 0, $slashpos+1);
		
		define('CFG_SYS_ROOT', $sysroot);
		define('CFG_WEB_ROOT', $webroot);
		define('CFG_TB_PRE',   $tbpre);
		define('CFG_CHARSET', $charset);
		define('CFG_SITE_NAME', $site_name);
		define('RTM_APP_ROOT', dirname(__FILE__).'/');
		
		$global_conf = array(
			'db'      => array(
				$dbhost.'_'.$dbport.'_'.$dbname => array('username'=>$dbuser, 'pwd'=>$_REQUEST['db_pwd']),
			), 
			'mem'     => array(
				array($memhost, $memport)
			),
		);		
$content = <<<EOD
<?php 
if(!defined('IN_INDEX')) 
	exit('access deined');
define('CFG_SYS_ROOT', '$sysroot');
define('CFG_WEB_ROOT', '$webroot');
define('CFG_TB_PRE',   '$tbpre');
define('CFG_CHARSET', '$charset');
define('CFG_SITE_NAME', '$site_name');
\$global_conf = %s;
?>
EOD;
		
		$content = sprintf($content, var_export($global_conf, true));
		file_put_contents('global_conf.php', $content);
		
		require $sysroot.'core/class/CFileType.php';
		CFileType::import('common', 'CDbPool.php', 'CObjectDB.php');
		CFileType::import('core', 'CCore.php', 'IModInstall.php', 'CModule.php', 'CFileParser.php', 'CMacroParser.php');
		CObjectDB::setTablePrefix($tbpre);
		
		//CFileType::initRuntimeEnv();
		$oMod = new CModule($pdo);
		$oFilePsr = new CFileParser(new CMacroParser());
		
		foreach($defmod as $mod){
			$oMod->install(sprintf(MOD_PATH, $mod), '', $oFilePsr);
		}
		$othersmod = trim($_REQUEST['modseq']);
		if($othersmod != ''){
			foreach(explode(',', $othersmod) as $mod){
				$path = sprintf(MOD_PATH, trim($mod));
				if(file_exists($path)){
					$oMod->install($path, '', $oFilePsr);
				}else echo $mod , 'not found<br/>';
			}
		}
		$error = $oMod->getErrorMsg();
		
		if(0 != count($error))
			exit('error: <br/>'.implode('<br/>',$error));
			
		unlink('install.php');
		CFileType::rmdir('_mod');
		exit('安装完成');
	}else exit(implode('<br/>',$error));
}
 
?>
<!doctype html>
<html>
<head>
<title>安装系统</title>
<style type="text/css">
.main{width:950px;margin:10px auto;font-size:14px;}
table{width:100%;border:1px solid #ddd;margin-top:20px;}
caption{font-size:16px;font-weight:bold;padding:5px;}
td,th{padding:5px 0;border-bottom:1px solid #ddd;}
td{padding-left:5px;}
th{border-right:1px solid #ddd;}
</style>
<script type="text/javascript">
String.prototype.trim = function(){
	return this.replace(/^\s*([\w\W]+)\s*$/, "$1");
}
function chkform(form){
	var sysroot = form['sysroot'].value.trim();
	if(0 == sysroot.length){
		alert("系统目录不能为空");
		form['sysroot'].focus();
		return false;
	}

	var site_name = from['site_name'].value.trim();
	if(0 == site_name.length){
		alert("站点名称不能为空");
		form['site_name'].focus();
		return false;
	}
	
	var dbhost = form['db_host'].value.trim(), 
		dbport = form['db_port'].value.trim(),
		dbname = form['db_name'].value.trim(),
		dbuser = form['db_user'].value.trim(),
		dbpwd  = form['db_pwd'].value.trim(),
		tbpre  = form['tbpre'].value.trim();
	if(0 == dbhost.length || 0 == dbname.length || 
		0 == dbport.length || 0 == dbuser.length || 0 == tbpre.length){
		alert("数据库配置有误:host,name,port,user不能为空");
		form['db_host'].focus();
		return false;
	}
	if( !/\d+/.test(dbport)){
		alert("数据库端口配置有误");
		form['db_port'].focus();
		return false;
	}

	var memhost = form['mem_host'].value.trim(), 
		memport = form['mem_port'].value.trim();
	if(memhost.length > 0 && 0 == memport.length){
		alert("Memcache 端口不能为空");
		form['mem_port'].focus();
		return false;
	}
	if(memport.length > 0 && 0 == memhost.length){
		alert("Memcache主机名不能为空");
		form['mem_host'].focus();
		return false;
	}
	if(memport.lenght > 0 & !/\d+/.test(memport.lenght)){
		alert("Memcache端口配置有误");
		form['mem_port'].focus();
		return false;
	}
	return true;
}
</script>
</head>
<body>
<div class="main">
	<?php if(!$valid){?>
	<div>
		<p>系统检测结果</p>
		<ul>
			<?php foreach($notice as $str){?>
			<li><?=$str?></li>
			<?php } ?>
		</ul>
	</div>
	<p>完成上述工作后，再刷新此页面</p>
	<?php }else{ ?>
	<form action="" method="post" onsubmit="return chkform(this);">
	<table>
		<caption>系统配置选项(*必填)</caption>
		<tr><th>站点名称*</th><td><input type="text" name="site_name" value="" style="width:300px;" /></td></tr>
		<tr><th>系统目录*</th><td><input type="text" name="sysroot" value="" style="width:300px;" />(用户不能访问)</td></tr>
		<tr><th>系统字符集</th><td><input type="text" name="charset" value="utf-8" style="width:100px;" />(包括数据库，前端页面)</td></tr>
		<tr><th>MYSQL*</th><td>
			host:<input type="text" name="db_host" value="localhost" style="width:100px;" />&nbsp;
			port:<input type="text" name="db_port" value="3306" style="width:50px;" />&nbsp;
			database:<input type="text" name="db_name" value="" style="width:50px;" />&nbsp;
			user:<input type="text" name="db_user" value="" style="width:70px;" />&nbsp;
			password:<input type="text" name="db_pwd" value="" style="width:70px;" />&nbsp;
			表前缀名:<input type="text" name="tbpre" value="pre_" style="width:50px;" />&nbsp;
		</td></tr>
		<tr><th>Memcache</th><td>
			host:<input type="text" name="mem_host" value="127.0.0.1" style="width:100px;" />&nbsp;
			port:<input type="text" name="mem_port" value="11211" style="width:50px;" />&nbsp;
		</td></tr>
		<tr><th>模块安装顺序</th><td><input type="text" name="modseq" value="" />(除了core,common, 多个用英文逗号分开)</td></tr>
		<tr><td colspan=2 style="border:0;"><input type="submit" value="提交" /></td></tr>
	</table>
	</form>
	<?php } ?>
</div>
</body>
</html>