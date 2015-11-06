<?php 

class CMctEvent{
	
	private static function _doMapChanged($args, $mbs_appenv){
		mbs_import('merchant', 'CMctProductControl', 'CMctProductAttachmentControl');
		mbs_import('product', 'CProductAttrControl');
		
		$mct_pdt = CMctProductControl::getInstance($mbs_appenv, CDbPool::getInstance(),
				CMemcachedPool::getInstance(), $args['product']['en_name']);
		
		var_dump($args);
		if(count($args['pdtattr']) == count($args['new']) && empty($args['old'])){
			$mct_pdt_atch = CMctProductAttachmentControl::getInstance($mbs_appenv, CDbPool::getInstance(),
					CMemcachedPool::getInstance(), $args['product']['en_name']);
			
			$field_def = array();
			foreach($args['req_aid'] as $aid){
				$field_def[] = CProductAttrControl::def2sql($args['attrmap'][$aid]);
			}
			if(!empty($field_def)){
				$mct_pdt->createTable($field_def);
				$mct_pdt_atch->createTable($field_def);
			}
		}else {
			$alter_add = $alter_del = array();
			foreach($args['new'] as $naid){
				$alter_add[''] = CProductAttrControl::def2sql($args['attrmap'][$naid]);
			}
			foreach($args['old'] as $oaid){
				$alter_del[] = $args['attrmap'][$oaid]['en_name'];
			}
			$mct_pdt->alterTable($alter_add, $alter_del, array(), array());
		}
	}
	
	static function _doAttrChanged($args, $mbs_appenv){
		mbs_import('merchant', 'CMctProdcutControl', 'CMctProductAttachmentControl');
		mbs_import('product', 'CProductAttrMapControl', 'CProductControl', 'CProductAttrControl');
		
		$modify = $change = array();
		if($args['new']['en_name'] == $args['src_name']){
			$modify = array(''=>CProductAttrControl::def2sql($args['new']));
		}else{
			$change = array($args['new']['en_name'] => CProductAttrControl::def2sql($args['new']));
		}
		
		$pdt_ctr = CProductControl::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$pdtattrmap_ctr = CProductAttrMapControl::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$res = $pdtattrmap_ctr->getDB()->search(array('aid'=>$args['new']['id']));
		foreach($res as $row){
			$pdt_ctr->setPrimaryKey($row['pid']);
			$pdt = $pdt_ctr->get();
			if(!empty($pdt)){
				$mct_pdt = CMctProductControl::getInstance($mbs_appenv, CDbPool::getInstance(),
						CMemcachedPool::getInstance(), $pdt['en_name']);
				
				$mct_pdt->alterTable(array(), array(), $modify, $change);
			}
		}
	}
	
	static function response($ev, $args, $mbs_appenv, $action=null, $mod=null){
		switch ($ev){
			case 'map_changed':
				self::_doMapChanged($args, $mbs_appenv);
				break;
			case 'attr_changed':
				break;
		}
	}
	
}

?>