<?php

require_once dirname(__FILE__).'/CUniqRowOfTable.php';

class CMultiRowOfTable extends CUniqRowOfTable
{
	protected $skyname    = '';
	protected $secondKey  = null;
	protected $pageId     = 0;
	protected $numPerPage = 20;
	
	protected function __construct($oPdoConn, $tbname, 
									$pkeyname, $primaryKey, 
									$skeyname, $secondKey=null)
	{
		parent::__construct($oPdoConn, $tbname, $pkeyname, $primaryKey);
		$this->skyname   = $skeyname;
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
	function setPageId($pid=0)
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
	
	function get(){
		$sql = sprintf('SELECT * FROM %s WHERE %s=%d Limit %d, %d', 
			$this->tbname, $this->keyname, $this->primaryKey, 
			($this->pageId-1)*$this->numPerPage, $this->numPerPage);
		try {
			$pdos = $this->oPdoConn->query($sql);
			$ret = $pdos->fetchAll();
		} catch (Exception $e) {
			throw $e;
		}
		return $ret;
	}
	
	function addNode($param){
		$keys   = '';
		$values = '';
		
		foreach($param as $k=>$v){
			$keys   .= $k.',';
			$values .= sprintf('"%s",', $this->oPdoConn->quote($v));
		}
		$keys = substr($keys, 0, -1);
		$values = substr($values, 0, -1);
		$sql = sprintf('INSERT INTO %s(%s) VALUES(%s)', $this->tbname, $keys, $values);
		
		try{
			$this->oPdoConn->query($sql);
			$this->secondKey = $this->oPdoConn->lastInsertId();
		}catch(Exception $e){
			throw $e;
		}
		return $this->secondKey;
	}
	
	function setNode($param){
		$sql = '';
		
		foreach($param as $k => $v){
			$sql .= sprintf('`%s`="%s",', $k, $this->oPdoConn->quote($v));
		}
		$sql = sprintf('UPDATE %s SET %s WHERE %s=%d AND %s=%d', 
			$this->tbname, $sql, $this->keyname, $this->primaryKey,
			$this->skyname, $this->secondKey);
		
		try {
			$ret = $this->oPdoConn->exec($sql);
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
			$ret = $pdos->fetchAll();
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
		} catch (Exception $e) {
			throw $e;
		}
		
		return $ret;
	}
	
	function getTotal(){
		$sql = sprintf('SELECT count(1) FROM %s WHERE %s=%d', 
				$this->tbname, $this->keyname, $this->primaryKey);
		try{
			$ret = $this->oPdoConn->query($sql);
			$ret = $pdos->fetchAll();
			$ret = empty($ret) ? 0 : $ret[0][0];
		}catch (Exception $e) {
			throw $e;
		}
		
		return $ret;
	}
}

?>