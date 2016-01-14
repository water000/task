<?php 

class CMctEvent{
	
	private static function _doMapChanged($args, $mbs_appenv){
		mbs_import('merchant', 'CMctProductControl', 'CMctProductAttachmentControl');
		mbs_import('product', 'CProductAttrControl');
		
		$mct_pdt = CMctProductControl::getInstance($mbs_appenv, CDbPool::getInstance(),
				CMemcachedPool::getInstance(), $args['product']['en_name']);
		
		$pdt_moddef = mbs_moddef('product');
		if(count($args['pdtattr']) == count($args['new']) && empty($args['old'])){
			$mct_pdt_atch = CMctProductAttachmentControl::getInstance($mbs_appenv, CDbPool::getInstance(),
					CMemcachedPool::getInstance(), $args['product']['en_name']);
			
			$field_def = array();
			$uniq_key = '';
			foreach($args['req_aid'] as $aid){
				$field_def[] = CProductAttrControl::def2sql($args['attrmap'][$aid]);
				if($pdt_moddef::isUniqAttr($aid)){
					$uniq_key .= $args['attrmap'][$aid]['en_name'].',';
				}
			}
			if(!empty($field_def)){
				$mct_pdt->createTable($field_def, empty($uniq_key) ? '' : substr($uniq_key,0,-1));
				$mct_pdt_atch->createTable($field_def);
			}
		}else {
			$alter_add = $alter_del = array();
			$uniq_key = '';
			foreach($args['new'] as $naid){
				$alter_add[] = CProductAttrControl::def2sql($args['attrmap'][$naid]);
			}
			foreach($args['old'] as $oaid){
				$alter_del[] = $args['attrmap'][$oaid]['en_name'];
			}
			foreach($args['pdtattr'] as $aid => $def){
				if(false === array_search($aid, $args['old']) && $pdt_moddef::isUniqAttr($aid)){
					$uniq_key .= $def['en_name'].',';
				}
			}
			$mct_pdt->alterTable($alter_add, $alter_del, array(), array(), '', 
					empty($uniq_key) ? '' : substr($uniq_key,0,-1));
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
	
	static function _doENNameChanged($args, $mbs_appenv){
		mbs_import('merchant', 'CMctProdcutControl', 'CMctProductAttachmentControl');
		
		$mct_pdt = CMctProductControl::getInstance($mbs_appenv, CDbPool::getInstance(),
				CMemcachedPool::getInstance(), $args['src_name']);
		$mct_pdt->alterTable(array(), array(), array(), array(), $args['new_name']);
		
		$mct_pdt_atch = CMctProductAttachmentControl::getInstance($mbs_appenv, CDbPool::getInstance(),
				CMemcachedPool::getInstance(), $args['src_name']);
		$mct_pdt_atch->alterTable($args['new_name']);
	}
	
	static function response($ev, $args, $mbs_appenv, $action=null, $mod=null){
		switch ($ev){
			case 'map_changed':
				self::_doMapChanged($args, $mbs_appenv);
				break;
			case 'attr_changed':
				self::_doAttrChanged($args, $mbs_appenv);
				break;
			case 'en_name_changed':
				self::_doENNameChanged($args, $mbs_appenv);
				break;
		}
	}
	
}

?>