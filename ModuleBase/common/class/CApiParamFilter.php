<?php

class CApiParamFilter extends CModTag{
	
	function oper($params, $tag=''){
		global $mbs_appenv;
		
		if(isset($_REQUEST['sign']) && isset($_REQUEST['ts']) && isset($_COOKIE['app_version']))
			;
		else{
			$mbs_appenv->echoex('miss param:sign/ts/app_version', 'MISS_PARAM');
			return false;
		}
		
		$appkeys = $mbs_appenv->config('appkeys', 'common');
		if(empty($appkeys) || !isset($appkeys[$_COOKIE['app_version']])){
			$mbs_appenv->echoex('invalid version', 'INVALID_VERSION');
			return false;
		}
		
		$str = $appkeys[$_COOKIE['app_version']].$_REQUEST['ts'];
		foreach($params as $v){
			$str .= $v;
		}
		
		if(md5($str) != $_REQUEST['sign']){
			$mbs_appenv->echoex('invalid sign', 'INVALID_SIGN');
			return false;
		}
		
		return true;
	}
	
}

?>