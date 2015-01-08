<?php

if(!CFileType::hasMod($_REQUEST['mod']))
	CCore::abort(sprintf('the module "%s" speciafied do not existed', $_REQUEST['mod']));
	
define('CUR_MOD', $_REQUEST['mod']);

if(!CFileType::hasType($_REQUEST['type']))
	CCore::abort('invalid file type: '.htmlspecialchars($_REQUEST['type']));
	
if(strpos($_REQUEST['file'], '..') !== false)
	CCore::abort('invalid file name: '. htmlspecialchars($_REQUEST['file']));

$moddef = CFileType::getModDef(CUR_MOD);
if(!$moddef)
	CCore::abort('failed on loading moddef class');
$info = $moddef->desc();

$path = CFileType::getPath(CFileType::ENV_COMPILE, 
	CUR_MOD, $_REQUEST['file'], $_REQUEST['type']);
if(!file_exists($path))
	CCore::abort('not found: '.$path);
	
if(isset($_REQUEST['code'])){
	CFileType::import('common', 'CDbPool.php');
	CFileType::import('core', 'CCore.php', 'IModInstall.php', 'CModule.php', 'CFileParser.php', 'CMacroParser.php');
	$dbp = CDbPool::getInstance();
	$pdoconn = $dbp->getDefaultConnection();
	$oMod = new CModule($pdoconn, CUR_MOD);
	$oFilePsr = new CFileParser(new CMacroParser());
	
	$content = iconv(CFG_CHARSET, $info[IModDef::MOD][IModDef::M_CS], $_REQUEST['code']);
	file_put_contents($path, $content);
	$oMod->updateFile($_REQUEST['file'], $_REQUEST['type'], $path, $oFilePsr);
	$error = $oMod->getErrorMsg();
	
	$content = $_REQUEST['code'];
	
}else {
	$content =  iconv($info[IModDef::MOD][IModDef::M_CS], CFG_CHARSET, file_get_contents($path));
}

?>
<!doctype html>
<html>
<head>
<title>edit source code</title>
<link href="#NTAG_CALL(core,url,common, common.css)" rel="stylesheet" type="text/css"  />
</head>
<body>
<div class="wrap">
	<div class="main">
		<div style="margin:20px 0;">
			<a href="#NTAG_CALL(core, url, core, modmgr)">module management</a>&nbsp;&gt;&nbsp; 
			<a href="#NTAG_CALL(core, url, core, detail)&mod=<?=CUR_MOD?>"><?=CUR_MOD?></a>&nbsp;&gt;&nbsp;
			<b><?=htmlspecialchars($_REQUEST['file'])?></b>
		</div>
		<?php if(isset($_REQUEST['code'])){ ?>
		<p style="text-align:center;"><b>edit result: <?=count($error) > 0 ? 'failure' : 'success'?></b></p>
		<?php if(count($error) > 0){foreach($error as $err){?>
		<p style="color:red;"><?=htmlspecialchars($err)?></p>
		<?php }}}?>
		<div style="margin:15px 0;">
			<form action="" method="post">
			<?php if(CFileType::FT_CLASS == $_REQUEST['type'] || CFileType::FT_ACTION == $_REQUEST['type']){ ?>
				<p><b>highlight</b></p>
				<div style="border:2px solid #ddd;width:98%;height:300px;overflow:scroll;"><?php highlight_string($content)?></div>
			<?php } ?>
				<p style="margin-top:10px;"><b>edit</b></p>
				<div><textarea name="code" style="border:2px solid #ddd;width:98%;height:500px;"><?=htmlspecialchars($content)?></textarea></div>
				<div style="margin:10px 0;"><input type="submit" value="update" />&nbsp;&nbsp;WARNING: we recommand highly that the content modified should be download to local file </div>
			</form>
		</div>
	</div>
</div>
</body>
</html>