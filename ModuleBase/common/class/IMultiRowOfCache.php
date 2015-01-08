<?php

require_once dirname(__FILE__).'/IUniqRowOfCache.php';

interface IMultiRowOfCache extends IUniqRowOfCache
{
	function setSecondKey($key);
	function getSecondKey();
	
	function getParentPrimaryKey();
	
	/**
	 * the multi-objects was seperated into multi-page(multi keys)
	 * @param unknown_type $id
	 */
	function setPageId($id=0);
	function getPageId();
	
	function addNode($param, $opt=null);
	function setNode($param, $opt=null);
	function getNode($param, $opt=null);
	function delNode($param, $opt=null);
	function getTotal();
	function setTotal($num);
	function increaseTotal($offset=1);
	function decreaseTotal($offset=1);
}

?>