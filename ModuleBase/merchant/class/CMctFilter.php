<?php 

class CMctFilter extends CModTag{
	function _exists(){
		
	}
	
	function _status(){
		
	}
	
	function oper($params, $tag=''){
		switch ($tag){
			case 'checkStatus':
				$this->_status(_exists);
				break;
			case 'checkExists':
				return $this->_exists($params);
				break;
		}
		return false;
	}
}

?>