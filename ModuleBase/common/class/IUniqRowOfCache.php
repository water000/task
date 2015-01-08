<?php

interface IUniqRowOfCache
{
	function setPrimaryKey($key);
	function getPrimaryKey();
	function setConnection($conn);
	function getConnection();
	function setExpiration($time);
	function getExpiration();
	
	function add($param);
	function get();
	function getMulti($keys);
	function set($param);
	function setMulti($keys);
	function destroy();
}

?>