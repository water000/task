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
	
	function countNewComment($user_id, $info_table){
		$sql = sprintf('SELECT sum(new_comment_count) FROM %s s, %s i 
				WHERE s.info_id = i.id AND i.creator_id=%d AND new_comment_count>0',
				$this->tbname, $info_table, $user_id);
		$ret = $this->oPdoConn->query($sql)->fetchAll();
		return empty($ret) ? 0 : $ret[0][0];
	}
	
	function resetNewCommentCount($user_id, $info_table){
		$sql = sprintf('UPDATE %s s, %s i SET new_comment_count =0
				WHERE s.info_id = i.id AND i.creator_id=%d AND new_comment_count>0',
				$this->tbname, $info_table, $user_id);
		return $this->oPdoConn->exec($sql);
	}
}



?>