<?php

interface IUniqRowOfTable
{
	function setPrimaryKey($key);
	function getPrimaryKey();
	function setConnection($conn);
	function getConnection();
	
	/**
	 * the method must return a primary key
	 * @param unknown_type $param
	 * @param unknown_type $opt
	 */
	function add(&$param);
	function get();
	function union($param);
	function set($param);
	function del();
}

?>