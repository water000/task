<?php 

class CInfoPushStatDB extends CUniqRowOfTable{
	function incrDup($keyval){
		$sql = sprintf('INSERT INTO %s SET %s=%d ', 
				$this->tbname, $this->keyname, $this->primaryKey);
		$dup = ' ON DUPLICATE KEY UPDATE ';
		foreach($keyval as $k => $v){
			$sql .= sprintf(', %s=%d', $k, $v);
			$dup .= sprintf('%s=%s+%d,', $k, $k, $v);
		}
		$sql = $sql.substr($dup, 0, -1);
		
		try {
			return $this->oPdoConn->exec($sql);
		} catch (Exception $e) {
			throw $e;
		}
	}
}



?>