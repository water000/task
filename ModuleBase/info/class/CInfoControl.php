<?php

class CInfoControl extends CUniqRowControl {
	
	private static $instance   = null;
	
	const AT_TXT = 1;
	const AT_VDO = 2;
	const AT_IMG = 3;
	private static $ATYPE_MAP = array(
		self::AT_IMG => 'IMG',
		self::AT_VDO => 'VDO',
		self::AT_IMG => 'IMG',
	);
	
	protected function __construct($db, $cache, $primarykey = null){
		parent::__construct($db, $cache, $primarykey);
	}
	
	/**
	 *
	 * @param CAppEnvironment $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $primarykey = null){
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CInfoControl(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('info'), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CInfoControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		return self::$instance;
	}
	
	static function getAttchType($filename){
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$type = 0;
		switch ($ext){
			case 'doc':
			case 'docx':
				$type = self::AT_TXT;
				break;
			case 'gif':
			case 'jpg':
			case 'png':
			case 'swf':
			case 'swc':
			case 'psd':
			case 'tiff':
			case 'bmp':
			case 'iff':
			case 'jp2':
			case 'wbmp':
				$type = self::AT_IMG;
				break;
			case 'mp4':
			case 'avi':
			case 'mov':
			case 'asf':
			case 'wmv':
			case 'navi':
			case '3gp':
			case 'ram':
			case 'mkv':
			case 'flv':
				$type = self::AT_VDO;
				break;
		}
		
		return $type;
	}
	
	static function moveAttachment($path){
		
	}
}

?>