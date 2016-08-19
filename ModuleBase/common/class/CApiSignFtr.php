<?php

class CApiSignFtr extends CModTag{
	
	function oper($params, $tag=''){
		global $mbs_appenv, $mbs_cur_actiondef;
		
		if(isset($_REQUEST['_sign']) && isset($_REQUEST['_ts']) && isset($_REQUEST['_version']))
			;
		else{
			$mbs_appenv->echoex('missing param:_sign/_ts/_version', 'MISSING_PARAM');
			return false;
		}
		
		$appkeys = $mbs_appenv->config('appkeys', 'common');
		if(empty($appkeys) || !isset($appkeys[$_REQUEST['_version']])){
			$mbs_appenv->echoex('invalid version', 'INVALID_VERSION');
			return false;
		}
		
		$str = $appkeys[$_REQUEST['_version']].$_REQUEST['_ts'];
		$params = $mbs_cur_actiondef[CModDef::P_ARGS];
		foreach($params as $k => $v){
			if(isset($v[CModDef::G_DC]) && strpos($v[CModDef::G_DC], '*S*') !== false){
			    if(isset($_REQUEST[$k])){
			        $str .= $_REQUEST[$k];
			    }else{
			        $mbs_appenv->echoex('missing param', 'MISSING_PARAM');
			        return false;
			    }
			}
		}
		
		if(md5($str) != $_REQUEST['_sign']){
			$mbs_appenv->echoex('invalid sign', 'INVALID_SIGN');
			return false;
		}
		
		return true;
	}
	
}

?>