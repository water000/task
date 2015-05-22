<?php 

define('REDIRECT_AFTER_LOGIN', isset($_REQUEST['redirect']) ? urldecode($_REQUEST['redirect']) : '/privilege/index');

mbs_import('', 'CUserSession');


if(isset($_REQUEST['phone_num'])){
	$error = array();
	
	if(!isset($_COOKIE['is_cookie_available'])){
		$error[] = 'cookie is unavailable on your browser';
	}
	
	if(!CStrTools::isValidPhone($_REQUEST['phone_num'])){
		$error[] = $mbs_appenv->lang('invalid_phone_num');
	}
	if(!CStrTools::isValidPassword($_REQUEST['password'])){
		$error[] = $mbs_appenv->lang('invalid_password');
	}
	
	if(isset($_REQUEST['remember_me'])){
		session_set_cookie_params(time()+15*24*3600);
	}
	
	//"$_SESSION['common_img_captcha']" defined in common/action/img_captcha.php
	session_start();
	
	$us = new CUserSession();
	$user_info = $us->get();
	if(!empty($user_info)){
		$mbs_appenv->echoex($mbs_appenv->lang('had_login'), '', REDIRECT_AFTER_LOGIN);
		exit(0);
	}
	
	if(strtoupper($_REQUEST['captcha']) != $_SESSION['common_img_captcha']){
		$error[] = $mbs_appenv->lang('invalid_captcha');
	}
	
	if(empty($error)){
		mbs_import('', 'CUserControl');
		$uc = CUserControl::getInstance($mbs_appenv, CDbPool::getInstance(), CMemcachedPool::getInstance());
		$rs = null;
		try {
			$rs = $uc->search(array('phone_num'=>$_REQUEST['phone_num']));
		} catch (Exception $e) {
			$error[] = $mbs_appenv->lang('db_exception', 'common');
		}
		if(empty($rs)){
			$error[] = $mbs_appenv->lang('invalid_phone_num');
		}
		else if(!CUserControl::checkPassword($_REQUEST['password'], $rs[0]['password'])){
			$error[] = $mbs_appenv->lang('invalid_password');
		}
		else{
			$us->set($rs[0]['id'], $rs[0]);
			setcookie('is_cookie_available', '', time()-1000);
			$mbs_appenv->echoex($rs, '', REDIRECT_AFTER_LOGIN);
			exit(0);
		}
	}
	
	if(!empty($error) && $mbs_appenv->item('client_accept') != 'html'){
		$mbs_appenv->echoex(implode(';', $error), 'LOGIN_FAILED');
		exit(0);
	}
	
}
else{
	if(!isset($_COOKIE['is_cookie_available'])){
		setcookie('is_cookie_available', 'yes'); // for checking whether the client supporting cookies
	}
	
	session_start();
	$us = new CUserSession();
	$user_info = $us->get();
	if(!empty($user_info)){
		$mbs_appenv->echoex($mbs_appenv->lang('had_login'), '', REDIRECT_AFTER_LOGIN);
		exit(0);
	}
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title($mbs_appenv->lang('login'))?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
img{vertical-align:bottom;margin: 0 6px;}
</style>
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<?php if(isset($_REQUEST['phone_num'])){if(!empty($error)){ ?>
<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
</div>
<?php }}?>
<div class="pure-g main-content">
    <div class="pure-u-1-3"></div>
    <div class="pure-u-1-3">
    	<form class="pure-form pure-form-stacked" method="post">
    		<input type="hidden" name="redirect" value="<?php echo urlencode(REDIRECT_AFTER_LOGIN)?>" />
		    <fieldset>
		    	<legend style="font-size: 1.5em;"><?php echo $mbs_appenv->lang('login')?></legend>
		    	
		        <label for="phone_num"><?php echo $mbs_appenv->lang('phone_num')?></label>
		        <input id=phone_num name="phone_num" class="pure-input-1-2"  /><br />
		
		        <label for="password"><?php echo $mbs_appenv->lang('password')?></label>
		        <input id="password" type="password" name="password" class="pure-input-1-2" /><br />
		        
		        
		        <label for=captcha><?php echo $mbs_appenv->lang('captcha')?></label>
		        <div class="pure-u-1-3">
		        <input id="captcha" type="text" name="captcha" class="pure-input-1" />
				</div>
				<img alt="<?php echo $mbs_appenv->lang('captcha')?>"  src="<?php echo $mbs_appenv->toURL('img_captcha', 'common')?>" 
				/><a href="#"  style="vertical-align: bottom;" onclick="this.previousSibling.src='<?php echo $mbs_appenv->toURL('img_captcha', 'common')?>?n='+Math.random();"><?php echo $mbs_appenv->lang('reload_on_unclear')?></a>
				<br />
				
		        <label for="remember" class="pure-checkbox" style="font-size: 12px;">
		            <input id="remember" type="checkbox" />&nbsp;<?php echo $mbs_appenv->lang('remember_me')?>
		        </label><br />
		
		        <button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('login')?></button>
		    </fieldset>
		</form>
    </div>
    <div class="pure-u-1-3"></div>
</div>
<div class=footer></div>
</body>
</html>