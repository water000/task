<?php 

class CMctEvent{
	
	private static function _doMapChanged($args, $mbs_appenv){
		mbs_import('merchant', 'CMctProdcutControl', 'CMctProductAttachmentControl');
		mbs_import('product', 'CProductAttrControl');
		
		$mct_pdt = CMctProductControl::getInstance($mbs_appenv, CDbPool::getInstance(),
				CMemcachedPool::getInstance(), $args['pdt']['name']);
		$mct_pdt_atch = CMctProductAttachmentControl::getInstance($mbs_appenv, CDbPool::getInstance(),
				CMemcachedPool::getInstance(), $args['pdt']['name']);
		
		if(empty($args['pdtattr'])){
			$field_def = array();
			foreach($args['req_aid'] as $aid){
				$field_def[] = CProductAttrControl::def2sql($args['attrmap'][$aid]);
			}
			if(!empty($field_def)){
				$mct_pdt->createTable($field_def);
				$mct_pdt_atch->createTable($field_def);
			}
		}
	}
	
	static function response($ev, $args, $mbs_appenv, $action=null, $mod=null){
		switch ($ev){
			case 'map_changed':
				self::_doMapChanged($args, $mbs_appenv);
				break;
		}
	}
	
}

?>