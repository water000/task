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

	static function getInstance($oMemp)
	{
		if(empty(self::$oIns))
			self::$oIns = new CSession($oMemp);
		return self::$oIns;
	}
	
	private function __construct($oMemp=null)
	{
		$this->oMemPool = $oMemp;
		register_shutdown_function(array($this, '__destruct'));
	}
	
	function __destruct()
	{
		if($this->bModified)
		{
			if(empty($this->oMemConn))
				$this->oMemConn  = $this->oMemPool->getConnection();
			
			if(empty($this->oMemConn)){
				trigger_error('failed to connect memcache server', E_USER_WARNING);
			}else{
				if(is_null($this->arrData))
					$this->oMemConn->deleteByKey(self::DEBUG_KEY, $this->sCacheKey);
				else
					$this->oMemConn->setByKey(self::DEBUG_KEY, $this->sCacheKey,
						 $this->arrData, self::CACHE_EXPIRE);
			}
		}
	}
	
	function start()
	{
		if(isset($_COOKIE[self::CK_NAME]) && empty($this->oMemConn))
		{
			$this->sCacheKey = $_COOKIE[self::CK_NAME];
			$this->oMemConn  = $this->oMemPool->getConnection();
			if(empty($this->oMemConn)){
				trigger_error('failed to connect memcache server', E_USER_WARNING);
			}else{
				$this->arrData = $this->oMemConn->getByKey(self::DEBUG_KEY, $this->sCacheKey);
				$this->arrData = empty($this->arrData) ? array() : $this->arrData;
			}
		}
	}
	
	function destroy()
	{
		$conf = session_get_cookie_params();
		setcookie(self::CK_NAME, '', time() - 1000, $conf['path']);
		$this->arrData = null;
		$this->bModified = true;
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
	
	function checkLogin($loginURL){
		
	}

}

?>