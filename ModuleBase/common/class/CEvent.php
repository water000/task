<?php 

class CEvent{
	
	static function trigger($ev, $args, $mbs_appenv, $action=null, $mod=null){
		$cfg = $mbs_appenv->config('events', 'common');
		if(!empty($cfg)){
			$evid = sprintf('%s.%s.%s', empty($mod) ? $mbs_appenv->item('cur_mod'): $mod, 
				empty($action) ? $mbs_appenv->item('cur_action'): $action, 
				$ev);
			if(isset($cfg[$evid])){
				foreach($cfg[$evid] as $modresp){
					list($mod, $resp) = explode('.', $modresp);
					mbs_import($mod, $resp);
					$resp::response($ev, $args, $action, $mod);
				}
			}
		}
	}
	
}


?>