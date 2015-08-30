<?php 

class CUserLoginLogTB extends CUniqRowOfTable{
	function __construct($oPdoConn, $tbname, $keyname, $primaryKey)
	{
		parent::__construct($oPdoConn, $tbname, $keyname, $primaryKey);
	}
	
	/**
	 * the method must return a primary key
	 * @param array $param the key-pair array, if some keys(like primary key) are not set,
	 * you MUST set them. Because the array will be cached like calling 'get()'
	 */
	function add(&$param){
		$keys = array_keys($param);
		$sql = sprintf('REPLACE INTO %s(%s) VALUES(%s)',
				$this->tbname,
				implode(',', $keys),
				str_repeat('?,', count($keys)-1).'?'
		);
		try{
			$pre = $this->oPdoConn->prepare($sql);
			$ret = $pre->execute(array_values($param));
			if($ret === false){
				$this->_seterror($pre);
				return 0;
			}
			$this->primaryKey = $this->oPdoConn->lastInsertId();
		}catch(Exception $e){
			throw $e;
		}
		$param[$this->keyname] = $this->primaryKey;
		return $this->primaryKey;
	}
}


?>