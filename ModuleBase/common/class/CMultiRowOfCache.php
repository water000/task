<?php

/**
 * the property 'sCacheKey' from parent was called by the '$this' in 
 * the class.Why we don't use the 'parent->sCacheKey'?I think that 
 * the php language did not support the style
 * @author Administrator
 *
 */


require_once dirname(__FILE__).'/CUniqRowOfCache.php';

class CMultiRowOfCache extends CUniqRowOfCache
{
	protected $secondKey  = null;
	protected $_sCacheKey = '';
	protected $pageId     = 0;
	
	function __construct($oMemConn, $prikey, $sonName, $exp=0, $secondKey=null)
	{
		parent::__construct($oMemConn, $prikey, $sonName, $exp);
		$this->setSecondKey($secondKey);
		$this->setPageId(0);
	}
	
	function setPrimaryKey($key)
	{
		parent::setPrimaryKey($key);
		$this->setPageId($this->pageId);
	}
	function getPrimaryKey()
	{
		return $this->_sCacheKey;
	}
	
	function getParentPrimaryKey()
	{
		return $this->sCacheKey;
	}
	
	function setSecondKey($key)
	{
		$this->secondKey = $key;
	}
	function getSecondKey()
	{
		return $this->secondKey;
	}
	
	function setPageId($id=0)
	{
		$this->pageId = $id;
		$this->_sCacheKey = $this->sCacheKey.':'.$id;
	}
	function getPageId()
	{
		return $this->pageId;
	}

	function add($param)
	{
		return $this->oMemConn->addByKey($this->sSonName, $this->_sCacheKey, $param, $this->exp);
	}
	
	function set($param)
	{
		return $this->oMemConn->setByKey($this->sSonName, $this->_sCacheKey, $param, $this->exp);
	}
	
	function get()
	{
		return $this->oMemConn->getByKey($this->sSonName, $this->_sCacheKey);
	}
	
	function destroy()
	{
		$this->oMemConn->deleteByKey($this->sSonName, $this->_sCacheKey);
	}
	
	function addNode($param, $opt=null)
	{
		
	}
	
	function setNode($param, $opt=null)
	{

	}
	
	function getNode($param, $opt=null)
	{
		
	}
	
	function delNode($param, $opt=null)
	{
		
	}
	
	function getTotal()
	{
		return $this->oMemConn->getByKey($this->sSonName, $this->sCacheKey.'.TOTAL');
	}
	
	function setTotal($num)
	{
		return $this->oMemConn->setByKey($this->sSonName, $this->sCacheKey.'.TOTAL', $num, $this->exp);
	}
	
	function increaseTotal($offset=1)
	{
		return $this->oMemConn->increment($this->sCacheKey.'.TOTAL', $offset);
	}
	
	function decreaseTotal($offset=1)
	{
		return $this->oMemConn->decrement($this->sCacheKey.'.TOTAL', $offset);
	}
}

?>