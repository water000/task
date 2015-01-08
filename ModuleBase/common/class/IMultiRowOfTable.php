<?php

require_once dirname(__FILE__).'/IUniqRowOfTable.php';

interface IMultiRowOfTable extends IUniqRowOfTable
{
	function setSecondKey($key);
	function getSecondKey();
	function setPageId($pid=0);
	function getPageId();
	function setNumPerPage($num);
	function getNumPerPage();
	
	/**
	 * the return value of the method was used for
	 * the method addNode of IMultiObjectCache if 
	 * the param of CMultiObjectControl::addNode was
	 * passed 'CACHE_AND_DB'.So, append the secondkey
	 * to the return of the method if some module 
	 * generate it after the IMultiObjectDB::addNodecalled
	 * @param unknown_type $param
	 */
	function addNode($param);
	function setNode($param);
	function getNode();
	function delNode();
	function getTotal();
}

?>