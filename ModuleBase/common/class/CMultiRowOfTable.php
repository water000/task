<?php

require_once dirname(__FILE__).'/CUniqRowOfTable.php';

class CMultiRowOfTable extends CUniqRowOfTable
{
	protected $skeyname    = '';
	protected $secondKey  = null;
	protected $pageId     = 1;
	protected $numPerPage = 20;
	protected $orderSecondKey = true;
	
	
	/*protected */function __construct($oPdoConn, $tbname, 
									$pkeyname, $primaryKey, 
									$skeyname, $secondKey=null)
	{
		parent::__construct($oPdoConn, $tbname, $pkeyname, $primaryKey);
		$this->skeyname   = $skeyname;
		$this->secondKey = $secondKey;
	}
	
	function setSecondKey($key)
	{
		$this->secondKey = $key;
	}
	function getSecondKey()
	{
		return $this->secondKey;
	}
	function getSecondKeyName()
	{
		return $this->skeyname;
	}
	function setPageId($pid=1)
	{
		$this->pageId = $pid;
	}
	function getPageId()
	{
		return $this->pageId;
	}
	function setNumPerPage($num)
	{
		$this->numPerPage = $num;
	}
	function getNumPerPage()
	{
		return $this->numPerPage;
	}
	function disableOrderSecondKey(){
		$this->orderSecondKey = false;
	}
	
	function get(){
		$sql = sprintf('SELECT * FROM %s WHERE %s=%d %s Limit %d, %d', 
			$this->tbname, $this->keyname, $this->primaryKey, 
			$this->orderSecondKey ? 'ORDER BY '.$this->skeyname.' DESC ': '',
			($this->pageId-1)*$this->numPerPage, $this->numPerPage);
		try {
			$pdos = $this->oPdoConn->query($sql);
			$ret = $pdos->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			throw $e;
		}
		return $ret;
	}
	
	function getAll(){
		$sql = sprintf('SELECT * FROM %s WHERE %s=%d',
				$this->tbname, $this->keyname, $this->primaryKey);
		try {
			$pdos = $this->oPdoConn->query($sql);
		} catch (Exception $e) {
			throw $e;
		}
		return $pdos;
	}
	
	function addNode($param){	
		$ret = false;
		$sql = sprintf('INSERT INTO %s(%s) VALUES(%s)', 
				$this->tbname, 
				implode(',', array_keys($param)), 
				str_repeat('?,', count($param)-1).'?'
		);
		
		try{
			$pdos = $this->oPdoConn->prepare($sql);
			$ret = $pdos->execute(array_values($param));
			if($ret === false){
				$this->_seterror($pdos);
			}
		}catch(Exception $e){
			throw $e;
		}
		return $ret;
	} 
	
	function setNode($param){
		$sql = '';
		
		foreach($param as $k => $v){
			$sql .= sprintf('`%s`=?,', $k);
		}
		$sql = sprintf('UPDATE %s SET %s WHERE %s=%d AND %s=%d', 
			$this->tbname, 
			substr($sql, 0, -1), 
			$this->keyname, $this->primaryKey,
			$this->skeyname, $this->secondKey);
		
		try {
			$pre = $this->oPdoConn->prepare($sql);
			$ret = $pre->execute(array_values($param));
			if($ret === false){
				$this->_seterror($pre);
			}
		} catch (Exception $e) {
			throw $e;
		}
		
		return $ret;
	}
	
	function getNode(){
		$sql = sprintf('SELECT * FROM %s WHERE %s=%d AND %s=%d', 
			$this->tbname, $this->keyname, $this->primaryKey,
			$this->skeyname, $this->secondKey);
		try {
			$pdos = $this->oPdoConn->query($sql);
			$ret = $pdos->fetchAll($this->fetch_type);
			$ret = empty($ret) ? array() : $ret[0];
		} catch (Exception $e) {
			throw $e;
		}
		return $ret;
	}
	
	function delNode(){
		$sql = sprintf('DELETE FROM %s WHERE %s=%d AND %s=%d', 
 			$this->tbname, $this->keyname, $this->primaryKey,
 			$this->skeyname, $this->secondKey);
 			
 		try {
			$ret = $this->oPdoConn->exec($sql);
			if($ret === false){
				$this->_seterror($this->oPdoConn);
			}
		} catch (Exception $e) {
			throw $e;
		}
		
		return $ret;
	}
	
	function getTotal(){
		$sql = sprintf('SELECT count(1) FROM %s WHERE %s=%d', 
				$this->tbname, $this->keyname, $this->primaryKey);
		try{
			$ret = $this->oPdoConn->query($sql)->fetchAll();
			$ret = empty($ret) ? 0 : $ret[0][0];
		}catch (Exception $e) {
			throw $e;
		}
		
		return $ret;
	}
}

?>