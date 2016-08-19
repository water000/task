<?php 
define('REDIRECT_AFTER_LOGIN', isset($_REQUEST['redirect']) 
	? urldecode($_REQUEST['redirect']) : $mbs_appenv->toURL('index', 'privilege'));

mbs_import('', 'CUserSession');


if(isset($_REQUEST['phone'])){
	$error = array();
	
	if(isset($_REQUEST['need_testing_cookie'])){
		if(!isset($_COOKIE['is_cookie_available'])){
			$error[] = 'cookie is unavailable on your browser!configured and <a href="">retry</a>';
			define('NEED_TESTING_COOKIE', 1);
		}else{
			setcookie('is_cookie_available', '', time()-1000);
		}
	}
	
	if(!CStrTools::isValidPhone($_REQUEST['phone'])){
		$error[] = $mbs_appenv->lang('invalid_phone');
	}
	if(!CStrTools::isValidPassword($_REQUEST['password'])){
		$error[] = $mbs_appenv->lang('invalid_password');
	}
	
	if(isset($_REQUEST['remember_me'])){
		session_set_cookie_params(time()+15*24*3600);
	}
	
	$us = new CUserSession();
	$user_info = $us->get();
	
	if(!empty($user_info)){
		//$mbs_appenv->echoex($mbs_appenv->lang('had_login'), 'HAD_LOGIN', REDIRECT_AFTER_LOGIN);
		$mbs_appenv->echoex(array('user'=>$user_info[1], 'token'=>session_id()), '', REDIRECT_AFTER_LOGIN);
		exit(0);
	}
	
	//"$_SESSION['common_img_captcha']" defined in common/action/img_captcha.php
	if(isset($_SESSION['common_img_captcha']) && 
		strtoupper($_REQUEST['captcha']) != $_SESSION['common_img_captcha']){
		$error[] = $mbs_appenv->lang('invalid_captcha');
	}
	
	$error_code = 'LOGIN_FAILED';
	if(empty($error)){
		mbs_import('', 'CUserInfoCtr');
		$uc = CUserInfoCtr::getInstance($mbs_appenv, CDbPool::getInstance(), 
		    CMemcachedPool::getInstance());
		$rs = null;
		
		$rs = $uc->search(array('phone'=>$_REQUEST['phone']));
		if(empty($rs) || !($rs = $rs->fetchAll(PDO::FETCH_ASSOC))){
			$error[] = $mbs_appenv->lang('invalid_phone');
		}
		else{
			$rs = $rs[0];
			if(!CUserInfoCtr::passwordVerify($_REQUEST['password'], $rs['password'])){
				$error[] = $mbs_appenv->lang('invalid_password');
			}
			else{
				if(isset($_COOKIE[ini_get('session.name')])){
					session_regenerate_id();
				}
				
				$us->set($rs['id'], $rs);
				$mbs_appenv->echoex(array('user'=>$rs, 'token'=>session_id()), '', REDIRECT_AFTER_LOGIN);
				
				exit(0);
			}
		}
	}
	if(!empty($error) && $mbs_appenv->item('client_accept') != 'html'){
		$mbs_appenv->echoex(implode(';', $error), $error_code);
		exit(0);
	}
}
else{
	if(ini_get('session.use_cookies') && empty($_COOKIE)){
		setcookie('is_cookie_available', 'yes', time() + 365*86400); // for checking whether the client supporting cookies
		define('NEED_TESTING_COOKIE', 1);
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
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('reset.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('style.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('iconfont.css')?>" rel="stylesheet">
</head>
<body>
<div class="login_div">
		<div class="logo"></div>
		<div class="logo_text"></div>
		<form name=myform action="" method="post">
    		<div class="logo_form">
    			<div class="layer">
    				<i class="iconfont">&#xe60f;</i>
    				<input type="text" class="inp" name="phone" 
            	       placeholder="<?php echo $mbs_appenv->lang(array('please_input', 'phone'))?>" />
    			</div>
    			<div class="layer">
    				<i class="iconfont">&#xe603;</i>
    				<input type="password" class="inp" name="password" 
            	       placeholder="<?php echo $mbs_appenv->lang(array('please_input', 'password'))?>" />
    			</div>
    			<?php if((isset($_REQUEST['phone']) && !empty($error) || isset($_SESSION['common_img_captcha']))){?>
                <div class="layer">
                    <i class="iconfont">&#xe603;</i>
                    <input id="captcha" type="text" name="captcha" class="inp" style="width: 135px;" 
                    	placeholder="<?php echo $mbs_appenv->lang(array('please_input', 'captcha'))?>" />
            		<img alt="<?php echo $mbs_appenv->lang('captcha')?>"  src="<?php echo $mbs_appenv->toURL('img_captcha', 'common')?>" 
            		/><a href="#"  style="vertical-align: bottom;font-size:12px;" onclick="this.previousSibling.src='<?php echo $mbs_appenv->toURL('img_captcha', 'common')?>?n='+Math.random();"><?php echo $mbs_appenv->lang('reload_on_unclear')?></a>
        		</div>
        	   <?php } ?>
    			<div class="auto_login">
    				<input type="checkbox" class="top2" name="remember_me" />
    				<?php echo $mbs_appenv->lang('auto_login_in_next')?>
    			</div>
    			<a href="javascript:document.myform.submit();" class="logo_btn">
    			 <span><?php echo $mbs_appenv->lang('login')?></span><i class="iconfont">&#xe60d;</i></a>
    		</div>
    	</form>
	</div>
	<footer><?php echo $mbs_appenv->lang('foot')?></footer>
</body>
</html>