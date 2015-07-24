<?php 

define('REDIRECT_AFTER_LOGIN', isset($_REQUEST['redirect']) ? 
	urldecode($_REQUEST['redirect']) : $mbs_appenv->toURL('department'));

mbs_import('', 'CUserSession', 'CUserDepSession', 'CUserDepControl', 'CUserDepMemberControl');
$user_sess = new CUserSession();
$uid = $user_sess->checkLogin();
if(false === $uid){
	exit(0);
}

$user_dep_sess = new CUserDepSession();
$user_dep_info = $user_dep_sess->get();
if(!empty($user_dep_info)){
	$mbs_appenv->echoex($user_dep_info[1], '', REDIRECT_AFTER_LOGIN);
	exit(0);
}

$udepmbr = CUserDepMemberControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$sch = $udepmbr->getDB()->search(array('user_id'=>$uid));
if(false === $sch || !($sch = $sch->fetchAll())){
	$mbs_appenv->echoex('access denied', 'USER_DEP_LOGIN_FAILED');
	exit(0);
}
$sch = $sch[0];

$user_dep_ctr = CUserDepControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance(), $sch['dep_id']);
$dep_info = $user_dep_ctr->get();
if(empty($dep_info)){
	$mbs_appenv->echoex('access denied!no such dep: '.$sch['dep_id'], 'USER_DEP_LOGIN_FAILED');
	exit(0);
}

$user_dep_sess->set($dep_info['id'], $dep_info);
$mbs_appenv->echoex($dep_info, '', REDIRECT_AFTER_LOGIN); // passed directly without using password 
exit(0);

if(isset($_REQUEST['password'])){
	if($dep_info['password'] != $_REQUEST['password']){
		$error[] = $mbs_appenv->lang('invalid_password');
	}else{
		$user_dep_sess->set($dep_info['id'], $dep_info);
		$mbs_appenv->echoex($dep_info, '', REDIRECT_AFTER_LOGIN);
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
<div class="pure-g wrapper">
    <div class="pure-u-1-3"></div>
    <div class="pure-u-1-3">
    	<?php if(isset($_REQUEST['password'])){if(!empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }}?>
    	<form class="pure-form pure-form-stacked" method="post">
    		<input type="hidden" name="redirect" value="<?php echo urlencode(REDIRECT_AFTER_LOGIN)?>" />
		    <fieldset>
		    	<legend style="font-size: 1.5em;"><?php echo $mbs_appenv->lang('dep_login')?></legend>
		    	
		        <label for="name"><?php echo $mbs_appenv->lang('name', 'common')?></label>
		        <input id=name name="phone" class="pure-input-1-2" value="<?php echo CStrTools::txt2html($dep_info['name'])?>" disabled   /><br />
		
		        <label for="password"><?php echo $mbs_appenv->lang('password')?></label>
		        <input id="password" type="password" name="password" class="pure-input-1-2" /><br />

		        <button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('login')?></button>
		    </fieldset>
		</form>
    </div>
    <div class="pure-u-1-3"></div>
</div>
<div class=footer></div>
</body>
</html>