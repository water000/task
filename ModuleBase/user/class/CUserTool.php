<?php

class CUserTool{
    static function careerExsits($id){
        global $mbs_appenv;
        
        $list = $mbs_appenv->config('career_list', 'user');
        return isset($list[$id]);
    }
    
    static function careerName($id){
        global $mbs_appenv;
        
        $list = $mbs_appenv->config('career_list', 'user');
        return isset($list[$id]) ? $list[$id] : 'unknown';
    }
}

?>