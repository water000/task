<?php 

define('REDIRECT_AFTER_LOGIN', isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : '');

mbs_import('', 'CUserSession');
$us = new CUserSession();
$user_info = $us->get();
if(!empty($user_info)){
	header('Location: '.REDIRECT_AFTER_LOGIN);
	exit(0);
}else{
	setcookie('is_cookie_avaiable', 'yes'); // for checking whether the client supporting cookies
}


if(isset($_COOKIE['is_cookie_avaiable'])){
	setcookie('is_cookie_avaiable', '', time()-1000);
	unset($_COOKIE['is_cookie_avaiable']);
}else{
	mbs_api_echo('cookie unavaiable');
	exit(1);
}


?>
<!doctype html>
<html>
<head>
<title><?php mbs_title($mbs_appenv->lang('login'))?></title>
<link href="<?=$mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
body, .warpper{background-color:#fff;}
.content{background-color:#fff;}
h1{color:#555;margin:60px 0;text-align:center;margin-top:30px;font-size:38px;}
.left{width:600px;height:400px;float:left;}
.right{width:320px;float:left;padding:0 20px 20px;background-color:#eee;}
.left h2, .left p{text-align:center;color:#777;}
.left p{padding:5px;}

.right p.title{font-weight:bold;padding:2px 0;margin-top:20px;}
.right .text{width:100%; padding:3px;}
.right label{width:150px;display:inline-block;float:left;padding:2px 0;}
.right .allmod{padding:0 5px;}
.right .mod{padding:0 3px;}
.right .allmod p{color:#000;margin-top:10px;}

img{vertical-align:bottom;margin: 0 6px;}

.submit_btn{display:block;width:100%;height:32px;font-weight:bold;margin:0 auto;}
</style>
</head>
<body>
<div class="warpper">
	<div class=header></div>
	<div class=content>
		<h1><?=$mbs_appenv->lang('login')?></h1>
		<?php if(isset($_REQUEST['name'])){if(!empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?=CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" ><?=$mbs_appenv->lang('close')?></a></div>
		<?php }else{?>
		<div class=success><?=$mbs_appenv->lang('oper_succ')?>
			<a href="<?=$mbs_appenv->toURL('group_list')?>"><?=$mbs_appenv->lang('group_list')?></a>
			<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" ><?=$mbs_appenv->lang('close')?></a>
		</div>
		<?php }}?>
		<div class=left></div>
		<div class=right>
			<form action="" method="post">
				<input type="hidden" name="redirect" value="<?=urlencode(REDIRECT_AFTER_LOGIN)?>" />
				<p class=title><?=$mbs_appenv->lang('phone_num')?></p>
				<p><input type="text" class=text name="phone_num" value="" /></p>
				<p class=title><?=$mbs_appenv->lang('password')?></p>
				<p><input type="password" class=text name="password" value="" /></p>
				<p class=title><?=$mbs_appenv->lang('captcha')?></p>
				<p><input type="text" class=text name="captcha" style="width:35%;" value="" />
					<img alt="<?=$mbs_appenv->lang('captcha')?>" src="<?=$mbs_appenv->toURL('img_captcha', 'common')?>" 
					/><a href="#" onclick="this.previousSibling.src='<?=$mbs_appenv->toURL('img_captcha', 'common')?>?n='+(new Date()).getSeconds();"><?=$mbs_appenv->lang('reload_on_unclear')?></a>
				</p>
				<p class=title style="margin-top:30px;"><input class=submit_btn type=submit /></p>
			</form>
		</div>
		<div style="clear: both"></div>
	</div>
	<div class=footer></div>
</div>
</body>
</html>