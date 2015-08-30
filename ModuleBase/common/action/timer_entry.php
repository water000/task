<?php
//run per 5 minutes
//crontab conf: 5 * * * * php index.req common.timer_entry.php
define('TS_NOW', time());
define('TIMER_ENTERVAL', 5);

#IMPORT core.CModulePrepare
#IMPORT core.CModuleCompile
#IMPORT core.ITimerListener

$dh = opendir(CFileTypeDef::CLASS_DIR_PATH);
if(!$dh)
	CSystem::error('fail to open dir');
	
$timerLis = array();
$modTimerLis = array();
$oModPre = new CModulePrepare();

while(false !== ($file = readdir($dh)))
{
	if('.' == $file || '..' == $file)
		continue;
	$ret = CModuleCompile::getModDef($file);
	if(empty($ret))
		continue;
	$thDef = $ret[1]->getTimerHandle();
	if(!empty($thDef))
	{
		$oModPre->appendContext(CFileTypeDef::CLASS_DIR_PATH.$file.'/');
		foreach($thDef as $def)
		{
			$err = $oModPre->loadClassFile(CFileTypeDef::getPath($file, 
				CFileTypeDef::FILE_TYPE_CLASS, $def), $num, false);
			if('' == $err)
			{
				$timer = new $def;
				if(!($timer instanceof ITimerHandle))
					continue;
				$timer->run(TS_NOW);
			}
		}
	}
	
	$lis = $ret[1]->getListenedTimer();
	if(is_array($lis) && !empty($lis))
	{
		if(empty($thDef))
			$oModPre->appendContext(CFileTypeDef::CLASS_DIR_PATH.$file.'/');
		foreach($lis as $cl => $timers)
		{
			$modTimerLis[$cl] = $file;
			foreach($timers as $t)
				$timerLis[$t][] = $cl;
		}
	}
}
foreach($timerLis as $t => $lis)
{
	$param = CSystem::getTimerParam($t);
	foreach($lis as $class)
	{
		$err = $oModPre->loadClassFile(CFileTypeDef::getPath($modTimerLis[$class], 
			CFileTypeDef::FILE_TYPE_CLASS, $class), $num, false);
		if('' == $err)
		{
			$obj = new $class;
			try {
				$obj->doListenedTimer($t, $param);
			} catch (Exception $e) {
				self::writeLog($e->getTraceAsString());
			}
		}
	}
}
?>