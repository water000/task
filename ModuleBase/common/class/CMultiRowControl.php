<?php
/**
 * @author Administrator
 *
 */

require_once dirname(__FILE__).'/CUniqRowControl.php';
require_once dirname(__FILE__).'/CMultiRowOfTable.php';
require_once dirname(__FILE__).'/CMultiRowOfCache.php';

class CMultiRowControl extends CUniqRowControl
{
	CONST CACHE_TO_HEAD = 0;
	CONST CACHE_TO_TAIL = 1;
	
	protected $bufkey = null;
	
	/**
	 * the variable was used for these methods('getNode, setNode, delNode')
	 * @var string
	 */
	protected $sSecondKeyName = '';
	
	protected $total = -1;
	
	/**
	 * 
	 * @param CMultiRowOfTable $db the instance of class CMultiRowOfTable
	 * @param CMultiRowOfCache $cache the instance of class CMultiRowOfCache
	 * @param string/int $primaryKey
	 * @param string/int $secondKey
	 */
	protected function __construct($db, $cache, $primaryKey=null, $secondKey=null)
	{
		parent::__construct($db, $cache, $primaryKey);
		$this->setSecondKey($secondKey);
		$this->setPageId(1);
	}
	
	function setPrimaryKey($key)
	{
		parent::setPrimaryKey($key);
		$this->bufkey = $this->primaryKey.':'.$this->oDB->getPageId();
		$this->total = -1;
	}
	
	function setPageId($id)
	{
		$this->oDB->setPageId($id);
		if($this->oCache)
			$this->oCache->setPageId($id);
		$this->bufkey = $this->primaryKey.':'.$id;
	}
	
	function setSecondKey($key)
	{
		$this->oDB->setSecondKey($key);
		if($this->oCache)
			$this->oCache->setSecondKey($key);
	}
	
	function setSecondKeyName($name)
	{
		$this->sSecondKeyName = $name;
	}
	
	function get()
	{
		if(isset($this->arrBuf[$this->bufkey]))
			return $this->arrBuf[$this->bufkey];
			
		try
		{	
			if($this->oCache)
			{
				$this->arrBuf[$this->bufkey] = $this->oCache->get();
				if(false === $this->arrBuf[$this->bufkey])
				{
					$this->arrBuf[$this->bufkey] = $this->oDB->get();
					$this->oCache->set($this->arrBuf[$this->bufkey]);
				}
			}
			else 
			{
				$this->arrBuf[$this->bufkey] = $this->oDB->get();
			}
		}
		catch(Exception $e)
		{
			throw $e;
		}
		return $this->arrBuf[$this->bufkey];
	}
	
	function destroy($condtions=array())
	{
		try
		{
			$ret = parent::destroy($condtions);
		}
		catch(Exception $e)
		{
			throw $e;
		}
		unset($this->arrBuf[$this->bufkey]);
		return $ret;
	}
	
	function addNode($param, $pos = self::CACHE_TO_HEAD)
	{
		try
		{
			$ret = $this->oDB->addNode($param);
			if($this->oCache && $ret !== false)
			{
				$ret = $this->oCache->get();
				if($ret !== false)
				{
					$this->arrBuf[$this->bufkey] = $ret;
					if($pos == self::CACHE_TO_HEAD)
						array_unshift($this->arrBuf[$this->bufkey], $param);
					else
						$this->arrBuf[$this->bufkey][] = $param;
					$this->oCache->set($this->arrBuf[$this->bufkey]);
				}
				$this->oCache->increaseTotal();
			}
		}
		catch(Exception $e)
		{
			throw $e;
		}
		return $ret;
	}
	
	function setNode($param)
	{
		$arr = null;
		$ret = false;
			
		try
		{
			$ret = $this->oDB->setNode($param);
		}
		catch(Exception $e)
		{
			throw $e;
		}
		
		if($ret != 0 && $this->oCache)
		{
			$arr = $this->oCache->get();
			if(!empty($arr))
			{
				$nidkey = $this->sSecondKeyName;
				$nid = $this->oDB->getSecondKey();
				foreach($arr as &$row)
				{
					if($nid == $row[$nidkey])
					{
						$row = array_merge($row, $param);
						$ret = $this->oCache->set($arr);
						break;
					}
				}
			}
		}
			
		if(empty($arr))
		{
			if(isset($this->arrBuf[$this->bufkey]))
			{
				$arr = $this->arrBuf[$this->bufkey];
				$nidkey = $this->sSecondKeyName;
				$nid = $this->oDB->getSecondKey();
				foreach($arr as &$row)
				{
					if($nid == $row[$nidkey])
					{
						$row = array_merge($row, $param);
						$this->arrBuf[$this->bufkey] = $arr;
						break;
					}
				}
			}
		}
		else
		{
			$this->arrBuf[$this->bufkey] = $arr;
		}
		
		return $ret;
	}
	
	/* need to think how about buf to do*/
	function delNode()
	{
		$arr = null;
		$ret = 0;
			
		try
		{
			$ret = $this->oDB->delNode();
		}
		catch(Exception $e)
		{
			throw $e;
		}
		
		if($ret != 0 && $this->oCache)
		{
			$arr = $this->oCache->get();
			if(!empty($arr))
			{
				$nidkey = $this->sSecondKeyName;
				$nid = $this->oDB->getSecondKey();
				foreach($arr as $k=>$row)
				{
					if($nid == $row[$nidkey])
					{
						unset($arr[$k]);
						$this->oCache->decreaseTotal();
						$ret = $this->oCache->set($arr);
						break;
					}
				}
			}
		}
		
		if(empty($arr))
		{
			if(isset($this->arrBuf[$this->bufkey]))
			{
				$arr = $this->arrBuf[$this->bufkey];
				$nidkey = $this->sSecondKeyName;
				$nid = $this->oDB->getSecondKey();
				foreach($arr as $k=>$row)
				{
					if($nid == $row[$nidkey])
					{
						unset($arr[$k]);
						$this->arrBuf[$this->bufkey] = $arr;
						break;
					}
				}
			}
		}
		else
		{
			$this->arrBuf[$this->bufkey] = $arr;
		}
		
		return $ret;
	}
	
	function getNode()
	{
		$ret = null;
		$arr = null;
		
		try
		{
			if(isset($this->arrBuf[$this->bufkey]))
				$arr = $this->arrBuf[$this->bufkey];
				
			if(null == $arr && $this->oCache)
				$arr = $this->oCache->get();
			
			if(empty($arr))
			{
				$ret = $this->oDB->getNode();
			}
			else
			{
				$nidkey = $this->sSecondKeyName;
				$nid = $this->oDB->getSecondKey();
				foreach($arr as $row)
				{
					if($nid == $row[$nidkey])
					{
						$ret = $row;
						break;
					}
				}
			}
		}
		catch(Exception $e)
		{
			throw $e;
		}
		return $ret;
	}
	
	function getTotal()
	{
		$total = -1;
		
		if(-1 != $this->total)
			return $this->total;
		
		try
		{
			if($this->oCache)
				$total = $this->oCache->getTotal();
				
			if(-1 == $total || false === $total)
			{
				$total = $this->oDB->getTotal();
				if($this->oCache)
					$this->oCache->setTotal($total);
			}
		}
		catch(Exception $e)
		{
			throw $e;
		}
		$this->total = $total;
		
		return $total;
	}
}

?>