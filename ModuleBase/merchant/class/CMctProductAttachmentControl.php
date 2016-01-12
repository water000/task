<?php

class CMctProductAttachmentControl extends CMultiRowControl{
	private static $product_ins = array();
	
	private static $mbs_appenv = null;
	private static $imgthumb = null;

	protected function __construct($db, $cache, $primarykey = null){
		parent::__construct($db, $cache, $primarykey);
	}

	static function formatTable($product_name){
		return mbs_tbname('merchant_product_attachment_'.$product_name);
	}

	/**
	 *
	 * @param CAppEnvironment $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string product_name the name of the product
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $product_name, $primarykey = null){
		if(!isset(self::$product_ins[$product_name])){
			try {
				$memconn = $mempool->getConnection();
				self::$product_ins[$product_name] = new CMctProductAttachmentControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								self::formatTable($product_name), 'mp_id', $primarykey, 'id'),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CMctProductAttachmentControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
			self::$imgthumb = new CImage('mctpdt/');
		}
		return self::$product_ins[$product_name];
	}

	function createTable($field_def){

		$sql = 'CREATE TABLE IF NOT EXISTS %s(
					id int unsigned not null auto_increment,
					mp_id int unsigned not null,
					format tinyint not null, -- image, video, ...
					name varchar(16) not null,
					path varchar(128) not null, -- only path, not include domain
					abstract varchar(32) not null,
					create_time int unsigned not null,
					primary key(id),
					key(mp_id)
				)';
		try {
			$this->oDB->getConnection()->exec(sprintf($sql, $this->oDB->tbname()));
		} catch (Exception $e) {
			throw $e;
		}
		return true;
	}
	
	function alterTable($new_product_name){
		try {
			$sql = sprintf('ALTER TABLE %s RENAME %s', $this->oDB->tbname(), 
					self::formatTable($new_product_name));
			$this->oDB->getConnection()->exec($sql);
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	function addNode(&$arr, $pos=null){
		try {
			$path = self::$imgthumb->thumbnailEx(self::$mbs_appenv, $arr);
			$arr2 = array(
				'mp_id'        => $this->primaryKey,
				'path'         => $path,
				'name'         => $arr[1],
				'create_time'  => time(),
				'format'       => 1,
			);
			parent::addNode($arr2);
		} catch (Exception $e) {
			throw $e;
		}
		return $arr2['id'];
	}
	
	static function completePath($path, $type='small'){
		return self::$imgthumb->completePath($path, $type);
	}

}


?>