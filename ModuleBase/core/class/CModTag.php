<?php

/**
 * one class implements IModTag that can operate multi tags.
 * @author Administrator
 *
 */
abstract class CModTag {
	protected $error = '';
	/**
	 * @desc if errors occued, calling getError() to operate
	 * @param array $params the params(start at 0) that the user passed
	 * @param string $tag the tag name which matched in file content.
	 * So, the param can be distinguished which tag submitted
	 * @return string on success, false on error
	 */
	abstract function oper($params, $tag='');
	
	/**
	 * @desc return an array saves the errors occured in oper()
	 */
	function getError(){
		return $this->error;
	}
}

?>