<?php

/**
 * @depends-on: CPDOStatement
 * @author Administrator
 *
 */
class CPDOSBuf implements Iterator {
	
	private $pdos = null;
	private $position = 0;
	private $buf = array();
	
	function __construct($pdos){
		$this->pdos = $pdos;
		$this->position = 0;
	}

    function rewind() {
    	$this->position = 0;
    	if(!isset($this->buf[$this->postion])){
    		try {
    			$this->buf[$this->position] = $this->pdos->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_FIRST);
    		} catch (Exception $e) {
    			throw $e;
    		}
    	}
    }

    function current() {
        return $this->buf[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
    	$this->position++;
    	if(!isset($this->buf[$this->postion])){
    		try {
    			$this->buf[$this->position] = $this->pdos->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_FIRST);
    		} catch (Exception $e) {
    			throw $e;
    		}
    	}
        return $this->buf[$this->postion];
    }

    function valid() {
        return $this->buf[$this->postion];
    }
	
	function getBuf(){
		return $this->buf;
	}
	
}

?>