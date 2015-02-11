<?php

class CApiParamFilter extends CModTag{
	
	function oper($params, $tag=''){
		global $mbs_appenv;
		
		if(isset($_REQUEST['sign']) && isset($_REQUEST['ts']) && isset($_COOKIE['app_version']))
			;
		else{
			$this->error = mbs_api_echo('missing param:sign/ts/app_version', null, true);
			return false;
		}
		
		$appkeys = $mbs_appenv->config('appkeys', 'common');
		if(empty($appkeys) || !isset($appkeys[$_COOKIE['app_version']])){
			$this->error = mbs_api_echo('invalid version', null, true);
			return false;
		}
		
		$str = $appkeys[$_COOKIE['app_version']].$_REQUEST['ts'];
		foreach($params as $v){
			$str .= $v;
		}
		
		if(md5($str) != $_REQUEST['sign']){
			$this->error = mbs_api_echo('invalid sign', null, true);
			return false;
		}
		
		return true;
	}
	
}

?>