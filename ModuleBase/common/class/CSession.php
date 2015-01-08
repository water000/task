<?php
/**
 * @depend core.CCore, php.memcached
 * @desc  use the cookies to stroe the session id
 * @author Administrator
 *
 */
class CSession 
{
	CONST CK_NAME      = 'PHPSID';
	CONST CACHE_EXPIRE = 1800;
	CONST DEBUG_KEY    = 'CSession';
	
	private $oMemPool   = null;
	private $oMemConn   = null;
	private $arrData    = array();
	private $sCacheKey  = '';
	private $bModified  = false;
	private $bDestroied = false;
	
	private static $oIns = null;
	
	function setConn($conn)
	{
		$this->oMemConn = $conn;
	}
	
	private function __construct($oMemp=null)
	{
		$this->oMemPool = $oMemp;
	}
	
	function __destruct()
	{
		if(!CCore::hasAborted() && $this->bModified)
		{
			if(empty($this->oMemConn))
				$this->oMemConn  = $this->oMemPool->getConnection();
			$this->oMemConn->setByKey(self::DEBUG_KEY, $this->sCacheKey,
				 $this->arrData, self::CACHE_EXPIRE);
		}
	}
	
	function start()
	{
		if(isset($_COOKIE[self::CK_NAME]) && empty($this->oMemConn))
		{
			$this->sCacheKey = $_COOKIE[self::CK_NAME];
			$this->oMemConn  = $this->oMemPool->getConnection();
			$this->arrData = $this->oMemConn->getByKey(self::DEBUG_KEY, $this->sCacheKey);
			$this->arrData = empty($this->arrData) ? array() : $this->arrData;
		}
	}
	
	function destroy()
	{
		if(empty($this->oMemConn))
			$this->oMemConn  = $this->oMemPool->getDefaultConnection();
		$this->oMemConn->deleteByKey(self::DEBUG_KEY, $this->sCacheKey);
		$conf = session_get_cookie_params();
		setcookie(self::CK_NAME, '', time() - 1000, $conf['path']);
		$this->arrData = null;
	}
	
	static function getInstance($oMemp)
	{
		if(empty(self::$oIns))
			self::$oIns = new CSession($oMemp);
		return self::$oIns;
	}
	
	function get($key)
	{
		return isset($this->arrData[$key]) ? $this->arrData[$key] : FALSE;
	}
	
	static function hasSID()
	{
		return isset($_COOKIE[self::CK_NAME]);
	}
	
	private static function _set_sid()
	{
		$conf = session_get_cookie_params();
		$_COOKIE[self::CK_NAME] = md5(uniqid(mt_rand(), true));
		setcookie(self::CK_NAME, $_COOKIE[self::CK_NAME], 
			$conf['lifetime'], $conf['path']);
	}
	
	function set($key, $val)
	{
		if(empty($this->sCacheKey))
		{
			self::_set_sid();
			$this->sCacheKey = $_COOKIE[self::CK_NAME];
		}
		$this->arrData[$key] = $val;
		$this->bModified = true;
	}
	
	function setMulti($arr)
	{
		if(empty($this->sCacheKey))
		{
			self::_set_sid();
			$this->sCacheKey = $_COOKIE[self::CK_NAME];
		}
		$this->arrData = array_merge($this->arrData, $arr);
		$this->bModified = true;
	}
	
	function delete($key)
	{
		unset($this->arrData[$key]);
		$this->bModified = true;
	}
	
	function deleteMulti($arr)
	{
		$this->arrData = array_diff_key($this->arrData, $arr);
		$this->bModified = true;
	}

}

?>