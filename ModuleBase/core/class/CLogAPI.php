<?php 



class CLogAPI{
	protected $elems;
	
	function __construct(){
		
	}
	
	static function _format($arr){
		$ret = '';
		foreach($arr as $k=>$v){
			$ret .= $k.':'.$v."\n";
		}
		return $ret;
	}

	function write(array $output, $other=''){
		static $need = array(
			'SCRIPT_URI'     =>'',
			'HTTP_ACCEPT'    => '',
			'CONTENT_TYPE'   => '',
			'REQUEST_URI'    => '',
			'REMOTE_ADDR'    => '',
			'REQUEST_METHOD' => '',
		);
		$this->elems = array(
			'input'  => '[SERVER]'."\n".self::_format(array_intersect_key($_SERVER, $need))."\r\n"
						.'[COOKIE]'."\n".self::_format($_COOKIE)."\r\n"
						.'[POST]'."\n".self::_format($_POST),
			'output' => var_export($output, true),
			'time'   => time(),
			'other'  => $other,
		);
	}
	
	function read(string $timeline){
		
	}
	
}

class CDBLogAPI extends CLogAPI{
	private $pdoconn;
	private $table = '';
	
	function __construct($pdoconn){
		$this->pdoconn = $pdoconn;
		$this->table = mbs_tbname('core_api_log');
	}
	
	function write($output, $other=''){
		parent::write($output, $other);
		$pdos = $this->pdoconn->prepare(sprintf(
				'INSERT INTO %s(input, output, time, other) values(?, ?, ?, ?)', $this->table));
		return $pdos->execute(array_values($this->elems));
	}
	
	function read($timeline, $limit = 10){
		return $this->pdoconn->query(sprintf('SELECT * FROM %s WHERE time>=%d ORDER BY id desc limit %d ', 
				$this->table, $timeline, $limit));
	}
}



?>