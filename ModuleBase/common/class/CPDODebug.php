<?php
require_once dirname(__FILE__).'/CPDOStatement.php';

class CPDODebug extends PDO
{
	private $arrLog = array();
	private $totalExecTime = 0;
	private $arrMap = array();
	
	function __construct($dsn, $username, $pwd, $options=array())
	{
		try
		{
			parent::__construct($dsn, $username, $pwd, $options);
		}catch(Exception $e)
		{
			throw $e;
		}
	}
	
	function getLog()
	{
		return $this->arrLog;
	}
	
	function getTotalExec()
	{
		return $this->totalExecTime;
	}
	
	function appendTotal($i)
	{
		$this->totalExecTime += $i;
	}
	
	function exec($statement)
	{
		$start = microtime(true);
		try
		{
			$ret = parent::exec($statement);
		}catch(PDOException $e){
			$this->arrLog[] =array('[PDO::exec]', $statement, $e->getMessage());
			throw $e;
		}		
		$end = microtime(true);
		$arr = parent::errorInfo();
		$res = '';
		if(!empty($arr[2]))
		{
			$res = $arr[2];
		}
		else
		{
			$cost = $end-$start;
			$this->appendTotal($cost);
			$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
		} 
		$this->arrLog[] = array('[PDO::exec]', $statement, $res);
		return $ret;
	}
	
	function query($statement)
	{
		$arrArgs = func_get_args();
		$argNum = count($arrArgs);
		$start = microtime(true);
		try
		{
			if(1 == $argNum)
				$ret = parent::query($statement);
			else if(2 == $argNum)
				$ret = parent::query($statement, $arrArgs[1]);
			else if(3 == $argNum)
				$ret = parent::query($statement, $arrArgs[1], $arrArgs[2]);
		}catch(PDOException $e){
			$this->arrLog[] =array('[PDO::query]', $statement, $e->getMessage());
			throw $e;
		}
		$end = microtime(true);
		$arr = parent::errorInfo();
		$res = '';
		if(!empty($arr[2]))
		{
			$res = $arr[2];
		}
		else
		{
			$cost = $end-$start;
			$this->appendTotal($cost);
			$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
		} 
		$this->arrLog[] = array('[PDO::query]', $statement, $res);
		return $ret;
	}
	
	function prepare($statement, $driver_options=array())
	{
		$start = microtime(true);
		try
		{
			$ret = parent::prepare($statement, $driver_options);
		}catch(PDOException $e){
			$this->arrLog[] =array('[PDO::prepare]', $statement, $e->getMessage());
			throw $e;
		}
		$end = microtime(true);
		$cost = $end-$start;
		$this->appendTotal($cost);
		$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
		$this->arrLog[] = array('[PDO::prepare]', $statement, $res);
		$this->arrMap[count($this->arrLog)-1] = $ret;
		return new CPDOStatement($ret, $this);
	}
	
	function appendLog($pdos, $func, $stmt, $res)
	{
		$idx = array_search($pdos, $this->arrMap);
		if(false === $idx)
			return false;
		$arr = isset($this->arrLog[$idx][3]) ? $this->arrLog[$idx][3] : array();
		$arr[] = array($func, $stmt, $res);
		$this->arrLog[$idx][3] = $arr;
		return true;
	}
}
?>