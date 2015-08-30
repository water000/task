<?php
class CPDOStatement implements Iterator 
{
	private $oPdos=null;
	private $oPdo = null;
	
	private $ptr  = 0;
	private $cur = null;
	
	function __construct($oPdos, $oPdo)
	{
		$this->oPdos = $oPdos;
		$this->oPdo = $oPdo;
	}
	
	function execute ($input_parameters=array())
	{
		$start = microtime(true);
		$statement = str_replace("\n", "", var_export($input_parameters, true));
		try
		{
			$ret =$this->oPdos->execute($input_parameters);
		}catch(PDOException $e){
			$this->oPdo->appendLog($this->oPdos, '[PDOS::execute]', $statement, $e->getMessage());
			throw $e;
		}
		$res = '';
		if(!$ret)
		{
			$arr = $this->oPdos->errorInfo();
			$res = var_export($arr, true);
			//$res = empty($arr[2]) ? var_export(error_get_last(), true) : var_export($arr, true);
		}
		else
		{
			$end = microtime(true);
			$cost = $end-$start;
			$res = sprintf('%f - %f = %f', $end, $start, $cost).'(s)';
			$this->oPdo->appendTotal($cost);
		}
		$this->oPdo->appendLog($this->oPdos, '[PDOS::execute]', $statement, $res);
		return $ret;
	}
	
	function __call ( $name , $arguments ){
		$num = count($arguments);
		if($num < 1)
			return $this->oPdos->$name();
		else if($num < 2)
			return $this->oPdos->$name($arguments[0]);
		else if($num < 3)
			return $this->oPdos->$name($arguments[0],$arguments[1]);
		else if($num < 4)
			return $this->oPdos->$name($arguments[0],$arguments[1],$arguments[2]);
		else if($num < 5)
			return $this->oPdos->$name($arguments[0],$arguments[1],$arguments[2],$arguments[3]);
		else
			return $this->oPdos->$name($arguments[0],$arguments[1],$arguments[2],$arguments[3], $arguments[4]);
	}
	static function __callStatic ( string $name , array $arguments ){
		$num = count($arguments);
		if($num < 1)
			PDOStatement::$name();
		if($num < 2)
			PDOStatement::$name($arguments[0]);
		else if($num < 3)
			PDOStatement::$name($arguments[0],$arguments[1]);
		else if($num < 4)
			PDOStatement::$name($arguments[0],$arguments[1],$arguments[2]);
		else if($num < 5)
			PDOStatement::$name($arguments[0],$arguments[1],$arguments[2],$arguments[3]);
		else
			PDOStatement::$name($arguments[0],$arguments[1],$arguments[2],$arguments[3], $arguments[4]);
	}
	
	function current (){
		return $this->cur;
	}
	function key (){
		return $this->ptr;
	}
	function next (){
		++$this->ptr;
	}
	function rewind ( ){
		$this->ptr = 0;
		$this->cur = null;
	}
	function valid ( ){
		$this->cur = $this->oPdos->fetch();
		return $this->cur === false ? false : true;
	}
	
	
}

?>