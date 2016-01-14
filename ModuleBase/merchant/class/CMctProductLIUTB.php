<?php 

class CMctProductMapTB extends CUniqRowOfTable{

	function add(&$param){
		$ret = false;
		$sql = sprintf('INSERT INTO %s(%s) VALUES(%s)',
				$this->tbname,
				implode(',', array_keys($param)),
				str_repeat('?,', count($param)-1).'?'
		);
		
		try{
			$pdos = $this->oPdoConn->prepare($sql);
			$ret = $pdos->execute(array_values($param));
		}catch(Exception $e){
			throw $e;
		}
		return $ret;
	}

}

?>