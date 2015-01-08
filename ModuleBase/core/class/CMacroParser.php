<?php
class CMacroParser {
	
	private static $funcStyles = array(
		array('pfx'=>'#', 'name'=>'NTAG_CALL', 'sfx'=>''),
		array('pfx'=>'<!--\s*', 'name'=>'HTAG_CALL', 'sfx'=>'\s*-->'),
		array('pfx'=>'#', 'name'=>'FILTER_CALL', 'sfx'=>''),
	);
	
	private $ntags = array();
	private $htags = array();
	private $filters = array();
	
	function __construct($sContent=''){
		$this->setContent($sContent);
	}
	
	function setContent($sContent){
		$this->ntags = array();
		$this->htags = array();
		$this->filters = array();
		if(!empty($sContent))
			$this->parse($sContent);
	}
	
	private function parse($sContent){
		$res = array(//the index must be equal to 'funcStyles'
			&$this->ntags,
			&$this->htags,
			&$this->filters,
		);
		static $sPtnFormat = '/%s%s\(\s*(\w*)\s*,\s*(\w+)((?:\s*(?(?=[,])(?:,\s*(?(?=[\'"])(?:([\'"])(.*?[^\\\\])\4\s*)|([^,)]*?(?=[,)])))))){0,10})\)%s/';
		for($i=0, $c=count(self::$funcStyles); $i<$c; ++$i){
			$match = array();
			$ptn = sprintf($sPtnFormat, self::$funcStyles[$i]['pfx'], 
				self::$funcStyles[$i]['name'], self::$funcStyles[$i]['sfx']);
			preg_match_all($ptn, $sContent, $match);
			//var_dump($ptn, $sContent, $match);
			for($j=0,$len=count($match[0]); $j<$len; ++$j){
				$node = array($match[0][$j], $match[1][$j], $match[2][$j]);
				$param = array();
				for($offset=0, $subject=$match[3][$j].')'; 
					preg_match('/\s*,\s*(?(?=[\'"])(?:([\'"])(.*?[^\\\\])\1\s*)|([^,)\s]*?(?=[,)\s])))/',
						substr($subject, $offset), $param); 
					$offset += strlen($param[0]))
					$node[] = empty($param[2]) ? $param[3] : $param[2];
				$res[$i][] = $node;
			}
		}
	}
	
	function getNormalTags(){
		return $this->ntags;
	}
	
	function getHtmlTags(){
		return $this->htags;
	}
	
	function getFilters(){
		return $this->filters;
	}
}
?>