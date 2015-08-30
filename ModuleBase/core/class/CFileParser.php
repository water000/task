<?php

require_once dirname(__FILE__).'/IModDef.php';
/**
 * @depends core.CFileType.php,core.IModDef
 * @author Administrator
 *
 */
class CFileParser {
	private $oMacro = null;
	private $sModName = '';
	private $warnings = array();
	private $tagdefbuf = array();
	private $operatedTag = array();
	private $bNewFile = false;
	private $sContent = '';
	
	
	function __construct($oMacro){
		$this->oMacro = $oMacro;
	}
	
	function getWarnings(){
		return $this->warnings;
	}
	
	function getOperatedTag(){
		return $this->operatedTag;
	}
	
	private function _get_classdef($mod, $tag, $type=IModDef::TAG){
		$ret = array('', '');
		$moddef = CFileType::getModDef($mod);
		$desc = $moddef->desc();
		if(isset($desc[$type]) && isset($desc[$type][$tag])){
			list($file, $class) = CFileType::parseClassDef($desc[$type][$tag][IModDef::G_CS]);
			if($file != '' && $class != '' 
				&& file_exists(CFileType::getPath(CFileType::ENV_RUNTIME, $mod, $file, CFileType::FT_CLASS)))
			{
				$ret = array($file, $class);
			}else $this->warnings[] = sprintf('CFileParser::_get_classdef,classdef "%s" was not found, file "%s"', 
				$desc[$type][$tag][IModDef::G_CS], $file);
		}else $this->warnings[] = sprintf('CFileParser::_get_classdef,tagdef "%s" was not found', $tag);
		
		return $ret;
	}
	
	private function _find_tagdef($mod, $tag, $type=IModDef::TAG){
		
		if(!isset($this->tagdefbuf[$mod][$tag])){
			$tagdef = null;
			list($file, $class) = $this->_get_classdef($mod, $tag, $type);
			if($file != '' && $class != ''){
				CFileType::import($mod, $file);
				if(class_exists($class))
					$tagdef = new $class;
				else 
					$this->warnings[] = sprintf('CFileParser::_find_tagdef,class "%s" was not defined in file "%s"', $class, $file);
			}else $this->warnings[] = sprintf('CFileParser::_find_tagdef,classdef "%s" was not found', $class);
			$this->tagdefbuf[$mod][$tag] = $tagdef;
		}
		return $this->tagdefbuf[$mod][$tag];
	}
	
	private function _oper_tag($tags, $type){
		foreach( $tags as $tag ){
			list($match, $mod, $tagname) = array_splice($tag, 0, 3);
			if(!isset($this->operatedTag[$match])){
				$mod = $mod != '' ? $mod : $this->sModName;
				$tagdef = $this->_find_tagdef($mod, $tagname, $type);
				if(null == $tagdef){
					$this->warnings[] = 'CFileParser::_oper_tag,can not find moddef or tagdef in: "'.$match.'"';
					$this->operatedTag[$match] = '';
				}else {
					$this->operatedTag[$match] = $tagdef->oper($tag);
					$this->warnings += $tagdef->getError();
				}
			}else if(!$this->bNewFile) continue;
			
			$this->sContent = str_replace($match, 
				$this->operatedTag[$match], $this->sContent);
			$this->bNewFile = false;
		}
	}
	
	private function _oper_filter($tags){
		foreach( $tags as $tag ){
			list($match, $mod, $tagname) = array_splice($tag, 0, 3);
			if(!isset($this->operatedTag[$match])){
				$mod = $mod != '' ? $mod : $this->sModName;
				//chk_filter defined in 'index.php'
				$this->operatedTag[$match] = sprintf("chk_filter('%s', '%s', %s);",
					$mod, $tagname, var_export($tag,true));
			}else if(!$this->bNewFile) continue;
			
			$this->sContent = str_replace($match, 
				$this->operatedTag[$match], $this->sContent);
			$this->bNewFile = false;
		}
	}
	
	private function _replace_rel_url(){
		static $ptns = array(
			'/(?:src|href)\s*=\s*(["\']?)\s*(\.\.\/[\w\.\/]+)\s*(\\1)/i',
			'/url\s*\(\s*(\.\.\/[\w\.\/]+)\s*\)/i',
		);
		foreach($ptns as $p){
			$match = array();
			if(preg_match_all($p, $this->sContent, $match) > 0){
				for($i=0, $j=count($match[0]); $i<$j; ++$i){
					$url = 4 == count($match) ? $match[2][$i] : $match[1][$i];
					$exp = explode('/', $url, 3);
					$dir = CFileType::getDir(CFileType::ENV_RUNTIME, $this->sModName, $exp[1]);
					$realpath = realpath($dir.$url);
					$rep = '';
					if($realpath !== false && 0 === strpos($realpath, realpath($dir))){
						$rep = CFileType::getURL($this->sModName, $exp[2], $exp[1]);
					}
					$rep = str_replace($url, $rep, $match[0][$i]);
					$this->sContent = str_replace($match[0][$i], $rep, $this->sContent);
				}
			}
		}
	}
	
	function getResult($sModName, $sFileContent){
		$this->sModName = $sModName;
		$this->bNewFile = true;
		$this->sContent = $sFileContent;
		$this->oMacro->setContent($sFileContent);
		$normalTags = $this->oMacro->getNormalTags();
		$htmlTags = $this->oMacro->getHtmlTags();
		$filters = $this->oMacro->getFilters();
		$this->_oper_tag($normalTags, IModDef::TAG);
		$this->_oper_tag($htmlTags, IModDef::TAG);
		$this->_oper_filter($filters);
		$this->_replace_rel_url();
		
		return $this->sContent;
	}
}

?>