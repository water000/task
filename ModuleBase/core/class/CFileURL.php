<?php

require_once dirname(__FILE__).'/IModTag.php';

class CFileURL implements IModTag {
	private $error = array();
	
	function oper($params, $tag=''){
		$this->error = array();
		
		if(count($params) < 2){
			$this->error[] = sprintf('[warning]CFileURL::oper, "%s" params error!Usage:,,mod,file,[type]', implode(',', $params));
			return false;
		}
		$mod = $file = $type = '';
		list($mod, $file) = $params;
		if(3 == count($params))
			$type = $params[2];
		else{
			$type = CFileType::getFileType($file);
		}
		if( !file_exists(CFileType::getPath(CFileType::ENV_COMPILE, $mod, $file, $type)) )	
			$this->error[] = sprintf('[warning]CFileURL::oper, "%s" not exists', implode(',', $params));
		return CFileType::getURL($mod, $file, $type);
	}
	
	function getError(){
		return $this->error;
	}
}

?>