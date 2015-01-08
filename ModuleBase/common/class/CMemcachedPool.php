<?php

require_once dirname(__FILE__).'/IDebugOutput.php';

class CMemcachedPool implements IDebugOutput
{
	CONST CLASS_MEMCACHED = 'Memcached';
	CONST CLASS_MEMCACHEDDEBUG = 'CMemcachedDebug';
	
	private static $oInstance = null;
	private $conn = null;
	private $conn_d = null;
	private $sClassName = self::CLASS_MEMCACHED;
	
	/**
	 * @desc elem:array(host, port), ...
	 * @var array
	 */
	private static  $arrConfig = array();
	
	private function __construct()
	{
	}
	
	static function setConf($arr){
		self::$arrConfig += $arr;
	}

	public function setClass($sClassName=self::CLASS_MEMCACHED)
	{
		$this->sClassName = $sClassName;
		if(self::CLASS_MEMCACHEDDEBUG == $this->sClassName 
			&& !class_exists('CMemcachedDebug'))
			require dirname(__FILE__).'/CMemcachedDebug.php';
	}
	
	public function html()
	{
		if($this->conn_d != null && CMemcachedDebug::getTotalExec() != 0)
		{
			//echo '<div style="padding:5px;margin:10px;font-size:14px;"><h3 style="text-align:center;padding:3px 0;">memcache query detail</h3>';
			echo '<div style="padding:5px;margin:10px;font-size:14px;">';
			$queryNum = 0;
			$style = '';
			$arrLog = $this->conn_d->getLog();
			$arrTime = $this->conn_d->getTime();
			foreach($arrLog as $sDbInfo=>$log)
			{
				echo '<fieldset><legend><i><b>'.$sDbInfo.'</b></i></legend><table style="width:100%;">';
				foreach($log as $arr)
				{
					//$style = intval($key) == $len-1 ? '' : 'style="border-bottom:1px solid #ddd;"';
					$style = 'style="border-bottom:1px solid #ddd;"';
					echo '<tr><td '.$style.'>'.$arr[0]
					.'</td><td '.$style.'>'.htmlspecialchars($arr[1])
					.'</td><td '.$style.'>'.$arr[2].(true === $arr[3] ? '' : '<font color="red">'.$arr[3].'</font>').'</td></tr>';
				}
				echo '<tr><td></td><td></td><td><b>'.count($log).'</b> quer(ies), cost <b>'.$arrTime[$sDbInfo].'</b>(s)</table></fieldset>';
				$queryNum += count($log);
			}
			echo '<div style="padding:10px 0;text-align:center;">--------total: <b>'.$queryNum.'</b> quer(ies), cost <b>'.CMemcachedDebug::getTotalExec().'</b> s ------</div></div>';
		}
	}
	
	public function xml()
	{
		echo '<memdebug><![CDATA[' , $this->html() , ']]></memdebug>';
	}
	
	public function cli()
	{
		$queryNum = 0;
		if($this->conn_d)
		{
			$arrLog = $this->conn_d->getLog();
			foreach($arrLog as $sDbInfo=>$log)
			{
				echo $sDbInfo, "\n";
				foreach($log as $arr)
				{
					echo sprintf("\t%20s%60s%s\n", $arr[0], $arr[1], $arr[2]);
				}
				$queryNum += count($log);
			}
		}
		echo 'total: '.$queryNum. 'quer(ies), cost '.CMemcachedDebug::getTotalExec();
	}
	
	public static function getHostList()
	{
		return self::$arrConfig;
	}
	
	public static function getInstance()
	{
		if(empty(self::$oInstance))
		{
			self::$oInstance = new CMemcachedPool();
		}
		return self::$oInstance;
	}
	
	public static function hasConfExists($host, $port)
	{
		foreach(self::$arrConfig as $arr)
		{
			if($arr[0] == $host && $arr[1] == $port)
				return true;
		}
		return false;
	}
	
	public function getConnection()
	{
		if(empty(self::$arrConfig))
			return null;
		
		if(self::CLASS_MEMCACHED == $this->sClassName)
		{
			if(!empty($this->conn))
				return $this->conn;
			
			$this->conn = new Memcached();
			$this->conn->addServers(self::$arrConfig);
			return $this->conn;
		}
		else
		{
			if(!empty($this->conn_d))
				return $this->conn_d;
			$this->conn_d = new CMemcachedDebug();
			$this->conn_d->addServers(self::$arrConfig);
			return $this->conn_d;
		}
	}
}
?>