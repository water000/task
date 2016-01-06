<?php 

class CProductAttrKVTB extends CMultiRowOfTable{
	
	function get(){
		$keyval = array(
			$this->keyname  => $this->primaryKey,
		);
		$opt = array(
			'offset' => ($this->pageId-1)*$this->numPerPage,
			'limit'  => $this->numPerPage,
			'order'  => 'first_char',
		);
		try {
			$pdos = $this->search($keyval, $opt);
			return $pdos->fetchAll($this->fetch_type);
		} catch (Exception $e) {
			throw $e;
		}
		
	}
	
}

?>