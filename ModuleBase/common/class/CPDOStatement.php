<?php
class CPDOStatement extends PDOStatement
{
	private $oPdos=null;
	private $oPdo = null;
	
	function __construct($oPdos, $oPdo)
	{
		$this->oPdos = $oPdos;
		$this->oPdo = $oPdo;
	}
	
	function bindColumn ( $column , &$param, $type=0 , $maxlen=0 , $driverdata=NULL)
	{
		return $this->oPdos->bindColumn($column, $param, $type, $maxlen, $driverdata);
	}
	
	function bindParam ( $parameter , &$variable , $type=0 , $maxlen=0 , $driverdata=NULL)
	{
		return $this->oPdos->bindColumn($parameter, $variable, $type, $maxlen, $driverdata);
	}
	
	function bindValue ( $parameter , $value , $data_type =0 )
	{
		return $this->oPdos->bindValue($parameter, $value, $data_type);
	}
	
	function closeCursor ()
	{
		return $this->oPdos->closeCursor();
	}
	
	function columnCount ()
	{
		return $this->oPdos->columnCount();
	}
	
	function errorCode ()
	{
		return $this->oPdos->errorCode();
	}
	
	function errorInfo ()
	{
		return $this->oPdos->errorInfo();
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
	
	function fetch ($fetch_style=PDO::FETCH_BOTH , $cursor_orientation=PDO::FETCH_ORI_NEXT , $cursor_offset=0 )
	{
		return $this->oPdos->fetch($fetch_style, $cursor_orientation, $cursor_offset);
	}
	
	function fetchAll ($fetch_style=PDO::FETCH_BOTH)
	{
		return $this->oPdos->fetchAll($fetch_style);
	}
	
	
	function fetchColumn ($column_number=0)
	{
		return $this->oPdos->fetchColumn($column_number);
	}
	
	function fetchObject ($class_name , $ctor_args )
	{
		return $this->oPdos->fetchObject($class_name, $ctor_args);
	}
	
	
	function getAttribute ( $attribute )
	{
			return $this->oPdos->getAttribute($attribute);
	}
	
	function getColumnMeta ( $column )
	{
			return $this->oPdos->getColumnMeta($column);
	}
	
	function nextRowset ()
	{
		return $this->oPdos->nextRowset();
	}
	
	function rowCount ()
	{
		return $this->oPdos->rowCount();
	}
	
	function setAttribute ( $attribute , $value )
	{
		return $this->oPdos->setAttribute($attribute, $value);
	}
	
	function setFetchMode ( $mode )
	{
		return $this->oPdos->setFetchMode($mode);
	}
		
}

?>