<?php
if(!class_exists('Memcached'))
	require_once dirname(__FILE__).'/Memcached.php';

class CMemcachedDebug extends Memcached 
{
	private $arrLog = array();
	private static $totalExecTime = 0;
	private $arrSingleTime = array();
		
	function getTotalExec()
	{
		return self::$totalExecTime;
	}
	
	function getLog()
	{
		return $this->arrLog;
	}
	
	function getTime()
	{
		return $this->arrSingleTime;
	}
	
	function addByKey($server_key, $key, $value, $expire=0)
	{
		$start = microtime(true);
		$ret = parent::addByKey($server_key, $key, $value, $expire);
		$end = microtime(true);
		$cost = $end-$start;
		self::$totalExecTime += $cost;
		$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
		$statement = '['.$key.'('.$expire.'s)]'.var_export($value, true);
		$key = implode('_', parent::getServerByKey($server_key));
		$this->arrLog[$key][] = array('[Memcached::add]', $statement, 
			$res, $ret?$ret:parent::getResultCode());
		$this->arrSingleTime[$key] = isset($this->arrSingleTime[$key]) ?
			$this->arrSingleTime[$key] + $cost : $cost;
		return $ret;
	}
	
	function getByKey($server_key, $key, $cb=0, $cas=0)
	{
		$start = microtime(true);
		$argNum = func_num_args();
		if(1 == $argNum)
			$ret = parent::getByKey($server_key,$key);
		else if(2 == $argNum)
			$ret = parent::getByKey($server_key,$key);
		else if(3 == $argNum)
			$ret = parent::getByKey($server_key,$key, $cb);
		else if(4 == $argNum)
			$ret = parent::getByKey($server_key,$key, $cb ,$cas);
		$end = microtime(true);
		$cost = $end-$start;
		self::$totalExecTime += $cost;
		$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
		$statement = sprintf('"%s", "%s", "%s"', $key, $cb, $cas);
		$key = implode('_', parent::getServerByKey($server_key));
		$this->arrLog[$key][] = array('[Memcached::get]', $statement, 
			$res, $ret!==false?true:parent::getResultCode());
		$this->arrSingleTime[$key] = isset($this->arrSingleTime[$key]) ?
			$this->arrSingleTime[$key] + $cost : $cost;
		return $ret;
	}
	
	function getMultiByKey($server_key, $key, $cas=0)
	{
		$start = microtime(true);
		$ret = parent::getMultiByKey($server_key,$key, $cas);
		$end = microtime(true);
		$cost = $end-$start;
		self::$totalExecTime += $cost;
		$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
		$statement = sprintf('"%s", "%s"', implode(',', $key), $cas);
		$key = implode('_', parent::getServerByKey($server_key));
		$this->arrLog[$key][] = array('[Memcached::getMultiByKey]', 
			$statement, $res, $ret!==false?true:parent::getResultCode());
		$this->arrSingleTime[$key] = isset($this->arrSingleTime[$key]) ?
			$this->arrSingleTime[$key] + $cost : $cost;
		return $ret;
	}
	
	function setByKey($server_key, $key, $value, $expire=0)
	{
		$start = microtime(true);
		$ret = parent::setByKey($server_key, $key, $value, $expire);
		$end = microtime(true);
		$cost = $end-$start;
		self::$totalExecTime += $cost;
		$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
		$statement = '['.$key.'('.strlen(serialize($value)).'B,'.$expire.'s)]'.var_export($value, true);
		$key = implode('_', parent::getServerByKey($server_key));
		$this->arrLog[$key][] = array('[Memcached::set]', $statement, 
			$res, $ret?$ret:parent::getResultCode());
		$this->arrSingleTime[$key] = isset($this->arrSingleTime[$key]) ?
			$this->arrSingleTime[$key] + $cost : $cost;
		return $ret;	
	}
	
	function setMultiByKey($server_key, $item, $exp=0)
	{
		$start = microtime(true);
		$ret = parent::setMultiByKey($server_key, $item, $exp);
		$end = microtime(true);
		$cost = $end-$start;
		self::$totalExecTime += $cost;
		$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
		$statement = '['.implode(',', array_keys($item)).'('.strlen(serialize($item)).'B,'.$exp.'s)]'.var_export($item, true);
		$key = implode('_', parent::getServerByKey($server_key));
		$this->arrLog[$key][] = array('[Memcached::setMultiByKey]', 
			$statement, $res, $ret?$ret:parent::getResultCode());
		$this->arrSingleTime[$key] = isset($this->arrSingleTime[$key]) ?
			$this->arrSingleTime[$key] + $cost : $cost;
		return $ret;
	}
	
	function increment($key, $num=1)
	{
		$start = microtime(true);
		$ret = parent::increment($key, $num);
		$end = microtime(true);
		$cost = $end-$start;
		self::$totalExecTime += $cost;
		$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
		$statement = $key;
		$this->arrLog['increment'][] 
			= array('[Memcached::increment]', $statement, $res, $ret?$ret:parent::getResultCode());
		$this->arrSingleTime['increment'] = isset($this->arrSingleTime['increment']) ?
			$this->arrSingleTime['increment'] + $cost : $cost;
		return $ret;
	}
	
	function decrement($key, $num=1)
	{
		$start = microtime(true);
		$ret = parent::decrement($key, $num);
		$end = microtime(true);
		$cost = $end-$start;
		self::$totalExecTime += $cost;
		$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
		$statement = $key;
		$this->arrLog['increment'][] 
			= array('[Memcached::decrement]', $statement, $res, $ret?$ret:parent::getResultCode());
		$this->arrSingleTime['increment'] = isset($this->arrSingleTime['increment']) ?
			$this->arrSingleTime['increment'] + $cost : $cost;
	}
}

?>