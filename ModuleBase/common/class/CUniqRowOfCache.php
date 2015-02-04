<?php

class CUniqRowOfCache
{
	protected $oMemConn  = null;
	protected $sCacheKey = '';
	protected $sSonName  = '';
	protected $exp       = 0;
	
	/**
	 * 
	 * @param Memcached $oMemConn 
	 * @param variable $prikey
	 * @param string $sonName the param was used for memcached debug which means who use the cache
	 * @param int $exp
	 */
	function __construct($oMemConn, $prikey, $sonName, $exp=0)
	{
		$this->oMemConn = $oMemConn;
		$this->sSonName = $sonName;
		$this->exp      = $exp;
		$this->setPrimaryKey($prikey);
	}
	
	function setConnection($conn)
	{
		$this->oMemConn = $conn;
	}
	function getConnection()
	{
		return $this->oMemConn;
	}
	
	function setExpiration($time)
	{
		$this->exp = $time;
	}
	function getExpiration()
	{
		return $this->exp;
	}
	
	function setPrimaryKey($key)
	{
		$this->sCacheKey = $this->sSonName.'/'.$key;
	}
	function getPrimaryKey()
	{
		return $this->sCacheKey;
	}
	
	function add($arr)
	{
		return $this->oMemConn->addByKey($this->sSonName, $this->sCacheKey, $arr, $this->exp); 
	}
	
	function get()
	{
		return $this->oMemConn->getByKey($this->sSonName, $this->sCacheKey);
	}
	
	function getMulti($arr)
	{
		return $this->oMemConn->getMulti($arr);
	}
	
	function set($val)
	{
		return $this->oMemConn->setByKey($this->sSonName, $this->sCacheKey, $val, $this->exp);
	}
	
	function setMulti($arr)
	{
		return $this->oMemConn->setMultiByKey($this->sSonName, $arr, $this->exp);
	}
	
	function destroy()
	{
		$this->oMemConn->deleteByKey($this->sSonName, $this->sCacheKey);
	}
}

?>