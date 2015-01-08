<?php
class CModuleSession
{
	private $oSession = null;
	private $sSessKeyPre = null;
	
	function __construct($oSession, $sSessKeyPre)
	{
		$this->oSession = $oSession;
		$this->sSessKeyPre = $sSessKeyPre.'.';
	}
	
	function get($key)
	{
		return $this->oSession->get($this->sSessKeyPre.$key);
	}
	
	function set($key, $val)
	{
		$this->oSession->set($this->sSessKeyPre.$key, $val);
	}
	
	function setMulti($arr)
	{
		foreach($arr as $k => $v)
			$this->set($k, $v);
	}
	
	function delete($key)
	{
		$this->oSession->delete($this->sSessKeyPre.$key);
	}
	
	function deleteMulti($arr)
	{
		foreach($arr as $v)
			$this->delete($v);
	}
}

?>