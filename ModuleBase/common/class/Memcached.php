<?php
if(!class_exists('Memcache', false)){
	//trigger_error('memcache lib not exists!install if use');
	class Memcache{}
}
class Memcached extends Memcache 
{
	private $arrServerPool = array();
	private $arrKeyMap = array();
	
	function addServers($arr)
	{
		$this->arrServerPool = $arr;
		foreach($arr as $conf)
			parent::addServer($conf[0], $conf[1]);
	}
	
	function addByKey($server_key, $key, $value, $exp=0)
	{
		return parent::add($key, $value, NULL, $exp);
	}

	function getByKey($server_key, $key, $cb=0, $cas=0)
	{
		$ret = false;
		
		$argNum = func_num_args();
		if(2 == $argNum)
			$ret = parent::get($key);
		else if(3 == $argNum)
			$ret = parent::get($key, $cb);
		else if(4 == $argNum)
			$ret = parent::get($key, $cb, $cas);
		return $ret;
	}
	
	function getMulti($keys, $cas=null){
		return parent::get($keys, $cas);
	}
	
	function setByKey($server_key, $key, $value, $exp=0)
	{
		return parent::set($key, $value, NULL, $exp);
	}
	
	function setMultiByKey($server_key, $item, $exp=0)
	{
		$ret = true;
		foreach($item as $key=>$val)
			$ret = (parent::set($key, $val, NULL, $exp) && $ret);
		return $ret;
	}
	
	function deleteByKey ($server_key, $key , $time=0 )
	{
		return parent::delete($key , $time);
	}
	
	function getMultiByKey ($server_key, $arr, $param=null)
	{
		return parent::get($arr, $param);
	}
	
	public function getServerByKey ($server_key )
	{
		return array('unknown', 'unknown');
	}
	
	public function getResultCode()
	{
		return 'FALSE(NOT_FOUND)';
	}
}
?>