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
	
	session_start();
	
	$us = new CUserSession();
	$user_info = $us->get();
	
	if(!empty($user_info)){
		//$mbs_appenv->echoex($mbs_appenv->lang('had_login'), 'HAD_LOGIN', REDIRECT_AFTER_LOGIN);
		$mbs_appenv->echoex(array('user'=>$user_info[1], 'token'=>session_id(), 
				'allow_comment'=>$user_info[1]['class_id']==3), '', REDIRECT_AFTER_LOGIN);
		exit(0);
	}
	
	//"$_SESSION['common_img_captcha']" defined in common/action/img_captcha.php
	if(isset($_SESSION['common_img_captcha']) && 
		strtoupper($_REQUEST['captcha']) != $_SESSION['common_img_captcha']){
		$error[] = $mbs_appenv->lang('invalid_captcha');
	}
	
	$error_code = 'LOGIN_FAILED';
	if(empty($error)){
		mbs_import('', 'CUserControl');
		$uc = CUserControl::getInstance($mbs_appenv, CDbPool::getInstance(), CMemcachedPool::getInstance());
		$rs = null;
		try {
			$rs = $uc->search(array('phone'=>$_REQUEST['phone']));
		} catch (Exception $e) {
			$error[] = $mbs_appenv->lang('db_exception', 'common');
		}
		if(empty($rs) || !($rs = $rs->fetchAll(PDO::FETCH_ASSOC))){
			$error[] = $mbs_appenv->lang('invalid_phone');
		}
		else{
			$rs = $rs[0];
			if(!CUserControl::checkPassword($_REQUEST['password'], $rs['password'])){
				$error[] = $mbs_appenv->lang('invalid_password');
			}
			else if(!empty($rs['IMEI'])){
				if(isset($_REQUEST['IMEI']) && isset($_REQUEST['IMSI']) 
					&& $_REQUEST['IMEI'] == $rs['IMEI'] && $_REQUEST['IMSI'] == $rs['IMSI'])
				{
// 					if(0 == $rs['pwd_modify_count']){ // for app only
// 						$error[] = $mbs_appenv->lang('user_must_modify_pwd');
// 						$error_code = 'USER_MUST_MODIFY_PWD_ON_FIRST_LOGIN';
// 					}
				}
				else{
					$error[] = $mbs_appenv->lang('invalid_device');
				}
			}
			
			if(empty($error)){
				if(isset($_COOKIE[ini_get('session.name')])){
					session_regenerate_id();
				}
				
				$us->set($rs['id'], $rs);
				$sid = session_id();
				$mbs_appenv->echoex(array('user'=>$rs, 'token'=>$sid,
						'allow_comment'=>$rs['class_id']>1), '', REDIRECT_AFTER_LOGIN);
				
				mbs_import('', 'CUserLoginLogControl');
				$user_llog = CUserLoginLogControl::getInstance($mbs_appenv, 
						CDbPool::getInstance(), CMemcachedPool::getInstance(), $rs['id']);
				$llog = $user_llog->get();
				if(!empty($llog) && !empty($llog['token']) && $llog['token'] != $sid){//delete the session which previous user logined
					session_write_close();
					
					if('files' == ini_get('session.save_handler')){
						$sess_path = session_save_path();
						$sess_path = empty($sess_path) ? getenv('TMP') : $sess_path;
						@unlink($sess_path.'/sess_'.$llog['token']);
					}else{
						session_id($llog['token']);
						session_destroy();
					}
				}
				$user_llog->add(array('user_id'=>$rs['id'], 'token'=>$sid, 'time'=>time()));
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
<title><?php mbs_title($mbs_appenv->lang('login'))?></title>
<link href="<?php echo $mbs_appenv->sURL('reset.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('global.css')?>" rel="stylesheet">
<style type="text/css">
img{vertical-align:bottom;margin: 0 6px;}
body{
            background: url("/static/images/curves-2.png") no-repeat top center #092d6a;
            font-family: "Microsoft Yahei";
        }
        input::-webkit-input-placeholder{
            color: #eeeeee;
        }
        input::-moz-input-placeholder{
            color: #eeeeee;
        }
        input::-ms-input-placeholder{
            color: #eeeeee;
        }
        .w760{
            width: 760px;
            margin: 100px auto 0;
            text-align: center;
        }
        .title{
            margin-top: 20px;
        }
        .title span{
            font-size: 24px;
            color: #ffffff;
            line-height: 24px;
        }
        .login{
            background: url("/static/images/field-box.png") no-repeat;
            width: 333px;
            /*height: 95px;*/
            margin: 30px auto 0;
        }
        .login .inp{
            width: 280px;
            border: none;
            background-color: transparent;
            color: #eeeeee;
            font-size: 16px;
            font-family: "Microsoft Yahei";
            height: 30px;
            line-height: 30px;
            padding:10px 0 7px 53px;
            *padding:8px 0 7px 53px;
        }
        .text_l{ text-align: left; font-size: 14px; color: #eeeeee; margin-top: 10px; margin-left: 10px;}
        .text_l input[type="checkbox"]{ position: relative; top: 2px;}
        .login_btn input[type="submit"]{ background: url("/static/images/shape.png") no-repeat; width: 332px; height: 49px; border:none; cursor: pointer; margin-top: 15px;}
       .captcha{width:170px;height:30px;line-height:30px;background-color: transparent;color: #eeeeee;
       		border: 1px solid rgb(40,80,145);border-radius: 6px;padding-left: 6px; font-size:13px;}
</style>
</head>
<body>
<div class="w760">
    <div><img src="/static/images/logo.png" alt=""/></div>
    <div class="title">
        <img src="/static/images/line-copy-2.png" alt=""/>
        <span><?php echo $mbs_appenv->lang('welcome')?></span>
        <img src="/static/images/line-copy.png" alt=""/>
    </div>
    <?php if(isset($_REQUEST['phone'])){if(!empty($error)){ ?>
		<div class=error style="background-color:transparent;color:rgb(184, 0, 0);font-size:12px;padding:0;"><?php  echo implode('&nbsp;;&nbsp;', $error)?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }}?>
	<form action="" method="post">
    <div class="login">
        <div><input type="text" class="inp" name="phone" 
        	placeholder="<?php echo $mbs_appenv->lang(array('please_input', 'phone'))?>" /></div>
        <div><input type="password" class="inp" name="password" 
        	placeholder="<?php echo $mbs_appenv->lang(array('please_input', 'password'))?>" /></div>
        <?php if((isset($_REQUEST['phone']) && !empty($error) || isset($_SESSION['common_img_captcha']))){?>
        <div style="margin-top: 20px;">
        <input id="captcha" type="text" name="captcha" class="captcha" 
        	placeholder="<?php echo $mbs_appenv->lang(array('please_input', 'captcha'))?>" />
		<img alt="<?php echo $mbs_appenv->lang('captcha')?>"  src="<?php echo $mbs_appenv->toURL('img_captcha', 'common')?>" 
		/><a href="#"  style="vertical-align: bottom;" onclick="this.previousSibling.src='<?php echo $mbs_appenv->toURL('img_captcha', 'common')?>?n='+Math.random();"><?php echo $mbs_appenv->lang('reload_on_unclear')?></a>
		<br />
		</div>
	<?php } ?>	
        <div class="text_l">
            <label><input type="checkbox"/><?php echo $mbs_appenv->lang('auto_login_in_next')?></label>
        </div>
         <div class="login_btn">
            <input type="submit" value="" />
        </div>
        
    </div>
    
        
   
    </form>
</div>
</body>
</html>