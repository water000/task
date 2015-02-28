<?php


class CUniqRowOfTable
{
	protected        $primaryKey = null;
	protected        $oPdoConn   = null;
	protected        $keyname    = null;
	protected        $tbname     = null;
	protected static $pdos       = null;

	/*protected*/ function __construct($oPdoConn, $tbname, $keyname, $primaryKey)
	{
		$this->oPdoConn   = $oPdoConn;
		$this->primaryKey = $primaryKey;
		$this->keyname    = $keyname;
		$this->tbname     = $tbname;
	}
	
	function setPrimaryKey($key)
	{
		$this->primaryKey = $key;
	}
	function getPrimaryKey()
	{
		return $this->primaryKey;
	}
	function setConnection($conn)
	{
		$this->oPdoConn = $conn;
	}
	function getConnection()
	{
		return $this->oPdoConn;
	}
	
	/**
	 * the method must return a primary key
	 * @param array $param the key-pair array, if some keys(like primary key) are not set,
	 * you MUST set them. Because the array will be cached like calling 'get()'
	 */
	function add(&$param){
		$keys = array_keys($param);
		$sql = sprintf('INSERT INTO %s(%s) VALUES(%s)', 
			$this->tbname, 
			implode(',', $keys), 
			str_repeat('?,', count($keys)-1).'?'
		);
		try{
			$pre = $this->oPdoConn->prepare($sql);
			$pre->execute(array_values($param));
			$this->primaryKey = $this->oPdoConn->lastInsertId();
		}catch(Exception $e){
			throw $e;
		}
		$param[$this->keyname] = $this->primaryKey;
		return $this->primaryKey;
	}
	
	function get(){
		$sql = sprintf('SELECT * FROM %s WHERE %s=%d', 
			$this->tbname, $this->keyname, $this->primaryKey);
		try {
			$pdos = $this->oPdoConn->query($sql);
			$ret = $pdos->fetchAll(PDO::FETCH_ASSOC);
			$ret = empty($ret) ? array() : $ret[0];
		} catch (Exception $e) {
			throw $e;
		}
		return $ret;
	}
	
	function union($keys){
		$sql = '';
		$ret = array();
		
		foreach($keys as $k){
			$sql .= sprintf('SELECT * FROM %s WHERE %s=%d UNION', 
				$this->tbname, $this->keyname, $k);
		}
		if(!empty($sql)){
			$sql = substr($sql, 0, -5);
			try {
				$pdos = $this->oPdoConn->query($sql);
				while(($arr = $pdos->fetch(PDO::FETCH_ASSOC)) !== FALSE){
					$ret[$arr[$this->keyname]] = $arr;
				}
			} catch (Exception $e) {
				throw $e;
			}
		}
		
		return $ret;
	}
	
	function set($param){
		$keys = array_keys($param);
		$sql = sprintf('UPDATE %s SET %s WHERE %s=%d', 
			$this->tbname, 
			implode('=?,', $keys).'=?',
			$this->keyname, $this->primaryKey);
		
		try {
			$pdos = $this->oPdoConn->prepare($sql);
			$ret = $pdos->execute(array_values($param));
		} catch (Exception $e) {
			throw $e;
		}
		
		return $ret;
	}
	
 	function del(){
 		$ret = null;
 		$sql = sprintf('DELETE FROM %s WHERE %s=%d', 
 			$this->tbname, $this->keyname, $this->primaryKey);
 			
 		try {
			$ret = $this->oPdoConn->exec($sql);
		} catch (Exception $e) {
			throw $e;
		}
		
		return $ret;
 	}
 	
 	function listAll(){
 		$ret = null;
 		
 		try {
 			$pdos = $this->oPdoConn->query('SELECT * FROM '.$this->tbname);
 			$ret  = $pdos->fetchAll();
 			 
 		} catch (Exception $e) {
 			throw $e;
 		}
 		
 		return $ret;
 	}
 	
 	function tbname(){
 		return $this->tbname;
 	}
 	
 	function search($keyval){
 		$sql = sprintf('SELECT * FROM %s WHERE '.implode('=?,', array_keys($keyval)).'=?', $this->tbname());
 		try{
 			$pdos = $this->oPdoConn->prepare($sql);
 			$pdos->execute(array_values($keyval));
 			return $pdos->fetchAll();
 		}catch (Exception $e){
 			throw $e;
 		}
 		return null;
 	}
}

?>