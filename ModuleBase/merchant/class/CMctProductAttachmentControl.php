<?php

class CMctProductAttachmentControl extends CMultiRowControl{
	private static $product_ins = array();
	
	private static $subdir = 'mctpdt/';
	
	private static $thumb = array(
		'small'  => array(65,  65,  's'), // width, height, desc
		'medium' => array(180, 100, 'm'),
		'big'    => array(400, 220, 'b'),
	);
	
	private static $mbs_appenv = null;

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
		list($src, $filename) = $arr;
		$name = md5(uniqid('mct_', true));
		$hash = substr($name, 0, 2);
		$subdir = self::$subdir.$hash.'/';
		$dest_dir = self::$mbs_appenv->mkdirUpload($subdir);
		if(false === $dest_dir){
			trigger_error('mkdirUpload error: '.$subdir, E_USER_WARNING);
			return false;
		}
		$path = $hash.'/'.$name;
	
		mbs_import('common', 'CImage');
		$dest = array_values(self::$thumb);
		$dest[0][2] = $dest_dir.$name.$dest[0][2].'.'.CImage::THUMB_FORMAT;
		$dest[1][2] = $dest_dir.$name.$dest[1][2].'.'.CImage::THUMB_FORMAT;
		$dest[2][2] = $dest_dir.$name.$dest[2][2].'.'.CImage::THUMB_FORMAT;
		try {
			CImage::thumbnail($src, $dest);
			$arr = array(
					'merchant_id'  => $this->primaryKey,
					'path'         => $hash.'/'.$name,
					'name'         => $filename,
					'create_time'  => time(),
					'format'       => 1,
			);
			parent::addNode($arr);
		} catch (Exception $e) {
			trigger_error('thumbnail error: '.$e->getMessage());
			return false;
		}
		return $arr['id'];
	}
	
	static function completePath($path, $type='small'){
		return self::$subdir.$path.self::$thumb[$type][2].'.'.CImage::THUMB_FORMAT;
	}

}


?>