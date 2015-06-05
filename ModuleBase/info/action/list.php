<?php 

mbs_import('', 'CInfoControl');
$info_ctr = CInfoControl::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());

define('ROWS_PER_PAGE', 20);
define('PAGE_ID',  isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1);
define('ROWS_OFFSET', (PAGE_ID-1)*ROWS_PER_PAGE);
$search_keys = array('tstart'=>'', 'tend'=>'', 'attachment_format'=>'');
$req_search_keys = array_intersect_key($_REQUEST, $search_keys);
foreach($req_search_keys as $k=> $v){
	$req_search_keys[$k] = trim($req_search_keys[$k]);
	if(0 == strlen($req_search_keys[$k])){
		unset($req_search_keys[$k]);
	}
}
if(isset($req_search_keys['attachment_format']) && 
	!CInfoControl::typeExists($req_search_keys['attachment_format'])){
	unset($req_search_keys['attachment_format']);
}
if(isset($req_search_keys['tstart']) || isset($req_search_keys['tend'])){
	if(empty($req_search_keys['tstart'])){
		$tstart = time() - 24*3600;
		$req_search_keys['tstart'] = date('Y-m-d', $tstart);
	}else{
		$tstart = strtotime($req_search_keys['tstart']);
	}
	if(empty($req_search_keys['tend'])){
		$tend = mktime(24);
		$req_search_keys['tend'] = date('Y-m-d', $tend);
	}else{
		$tend = strtotime($req_search_keys['tend']);
	}
	if($tstart >= $tend){
		$req_search_keys['tstart'] = $req_search_keys['tend'] = '';
	}else{
		$req_search_keys['create_time'] = array($tstart, $tend);
	}
}
$search_keys = array_merge($search_keys, $req_search_keys);
mbs_import('user', 'CUserSession');
$usersess = new CUserSession();
list($req_search_keys['creator_id'],) = $usersess->get(); 
unset($req_search_keys['tstart'], $req_search_keys['tend']);
$opts = array(
	'offset' => ROWS_OFFSET,
	'limit'  => ROWS_PER_PAGE,
	'order'  => ' id desc',
);
$list = $info_ctr->getDB()->search($req_search_keys, $opts);

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
td .title{font-weight:bold;text-align:center;}
td .abstract{width:95%; margin:10px auto;color:#555;font-size:80%;}
.popimg{position:fixed;top:6%;left:6%;height:85%;width:85%;padding:1%;display:none;overflow:scroll;background-color:#333;}
.popimg img{vertical-align:middle;display:block;margin:0 auto;}
</style>
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="pure-g" style="margin-top: 20px;color:#777;">
    <div class="pure-u-2-3 align-center">
    	<form class="pure-form" method="post">
    		<fieldset>
        		<legend><?php echo $mbs_appenv->lang('search')?></legend>
        		<?php echo $mbs_appenv->lang('time')?>
        		<input type="text" style="width: 120px" name="tstart" value="<?php echo $search_keys['tstart']?>" />-<input type="text" name="tend" style="width: 120px" value="<?php echo $search_keys['tend']?>" />
       			&nbsp;<?php echo $mbs_appenv->lang('attachment_format')?>
       			<select name="attachment_format">
       				<option value=0><?php echo $mbs_appenv->lang('all')?></option>
       				<?php foreach(CInfoControl::getTypeMap() as $t=>$v){ ?>
       				<option value="<?php echo $t?>" <?php echo $search_keys['attachment_format']==$t?' selected':''?>><?php echo $mbs_appenv->lang($v)?></option>
       				<?php } ?>
       			</select>
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('search')?></button>
       			<button onclick="this.form.action='<?php echo $mbs_appenv->toURL('edit')?>'" class="pure-button-primary pure-button"><?php echo $mbs_appenv->lang('add')?></button>
         	</fieldset>
		</form>
		<form class="pure-form" method="post" name="_form" action="<?php echo $mbs_appenv->toURL('edit')?>">
			<table class="pure-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th>ID</th>
			            <th style="width: 40%"><?php echo $mbs_appenv->lang('content')?></th>
			            <th><?php echo $mbs_appenv->lang('attachment')?></th>
			            <th><?php echo $mbs_appenv->lang('time')?></th>
			        </tr>
			    </thead>
			    <tbody>
			    	<?php $k=-1; foreach($list as $k => $row){ ?>
			        <tr <?php echo 1 == $k%2 ? 'class=pure-table-odd':'' ?>>
			            <td><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /><?php echo $row['id']?></td>
			            <td><div class=title><a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$row['id']))?>">
			            	<?php echo CStrTools::txt2html($row['title'])?></a></div>
			            	<div class=abstract><?php echo CStrTools::txt2html($row['abstract'])?></div>
			            </td>
			            <td>
			            <?php if($row['attachment_format'] == CInfoControl::AT_IMG){ ?>
			            <img __to_url="<?php echo $mbs_appenv->uploadURL($row['attachment_path'])?>" 
			            	src="<?php echo $mbs_appenv->uploadURL($row['attachment_path']).CInfoControl::MIN_ATTACH_SFX?>"
			            	onclick="_img_click(this)"  />
			            <br/>
			            <?php }?>
			            <?php echo $row['attachment_name']?>
			            </td>
			            <td><?php echo date('Y-m-d H:i:s', $row['create_time'])?></td>
			        </tr>
			     	<?php } if(-1 == $k){ ?>
			     	<tr><td colspan=5 class=no-data><?php echo $mbs_appenv->lang('no_data', 'common')?></td></tr>
			     	<?php }?>
			      </tbody>
			</table>
			<div style="margin-top:10px;">
				<button class="pure-button-primary pure-button" type="submit" onclick="this.form.action='<?php echo $mbs_appenv->toURL('push')?>'"><?php echo $mbs_appenv->lang('push')?></button>
				<button class="button-error pure-button" type="submit" name="delete" onclick="return confirm('<?php echo $mbs_appenv->lang('confirm_delete_info')?>');"><?php echo $mbs_appenv->lang('delete')?></button>
			</div>
		</form>
    </div>
</div>
<div class="popimg" id="IDD_POPIMG"><img alt="" src="" /></div>
<script type="text/javascript">
var g_popimg = document.getElementById("IDD_POPIMG");
function _img_click(img){
	var to = img.getAttribute("__to_url");
	g_popimg.style.display = "block";
	g_popimg.childNodes[0].src = to;
}
g_popimg.onclick = function(e){
	g_popimg.style.display = "none";
}
</script>
<div class=footer></div>
</body>
</html>