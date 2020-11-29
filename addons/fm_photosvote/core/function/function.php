<?php

function sortArrByField(&$array, $field, $desc = true){
  $fieldArr = array();
  foreach ($array as $k => $v) {
    $fieldArr[$k] = $v[$field];
  }
  $sort = $desc == false ? SORT_ASC : SORT_DESC;
  array_multisort($fieldArr, $sort, $array);
}
function fm_file_upload($file, $type = 'image', $name = '') {
	$harmtype = array('asp', 'php', 'jsp', 'js', 'css', 'php3', 'php4', 'php5', 'ashx', 'aspx', 'exe', 'cgi');
	if (empty($file)) {
		return error(-1, '没有上传内容');
	}
	if (!in_array($type, array('image', 'thumb', 'music', 'voice', 'video', 'vedio', 'audio'))) {
		return error(-2, '未知的上传类型');
	}

	global $_W;
	$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
	$ext = strtolower($ext);
	switch($type){
		case 'image':
		case 'thumb':
			$allowExt = array('gif', 'jpg', 'jpeg', 'bmp', 'png');
			$limit = 10 * 1024;
			break;
		case 'voice':
		case 'music':
		case 'audio':
			$allowExt = array('mp3', 'wma', 'wav', 'amr', '3gp','mov','mp4','avi','flv');
			$limit = 20 * 1024;
			break;
		case 'vedio':
		case 'video':
			$allowExt = array('rm', 'rmvb', 'wmv', 'avi', 'mpg', 'mpeg', 'mp4', 'mov', 'flv', '3gp', '4gp', 'mkv', 'f4v', 'm4v');
			$limit = 150 * 1024;
			break;
	}
	if (!in_array(strtolower($ext), $allowExt) || in_array(strtolower($ext), $harmtype)) {
		return error(-3, '不允许上传此类文件');
	}
	if (!empty($limit) && $limit * 1024 < filesize($file['tmp_name'])) {
		return error(-4, "上传的文件超过大小限制，请上传小于 {$limit}k 的文件");
	}
	$result = array();
	if (empty($name) || $name == 'auto') {
		$uniacid = intval($_W['uniacid']);
		$path = "{$type}s/{$uniacid}/" . date('Y/m/');
		mkdirs(ATTACHMENT_ROOT . '/' . $path);
		$filename = file_random_name(ATTACHMENT_ROOT . '/' . $path, $ext);

		$result['fname'] = $filename;
		$result['path'] = $path . $filename;
	} else {
		mkdirs(dirname(ATTACHMENT_ROOT . '/' . $name));
		if (!strexists($name, $ext)) {
			$name .= '.' . $ext;
		}
		$result['fname'] = $name;
		$result['path'] = $name;
	}

	if (!file_move($file['tmp_name'], ATTACHMENT_ROOT . '/' . $result['path'])) {
		return error(-1, '保存上传文件失败');
	}
	$result['success'] = true;
	return $result;
}

function fm_form_category_2level($name, $parents, $children, $parentid, $childid, $threec,$autosave = false){
	$html = '
		<script type="text/javascript">
			window._' . $name . ' = ' . json_encode($children) . ';
		</script>';
			if (!defined('TPL_INIT_CATEGORY')) {
				$html .= '
		<script type="text/javascript">
			function renderCategory(obj, name){
				var index = obj.options[obj.selectedIndex].value;

					$selectChild = $(\'#\'+name+\'_child\');
					var html = \'<option value="0">请选择二级分类</option>\';
					if (!window[\'_\'+name] || !window[\'_\'+name][index]) {
						$selectChild.html(html);
						return false;
					}
					for(var i=0; i< window[\'_\'+name][index].length; i++){
						html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';
					}
					$selectChild.html(html);

			}
			function renderCategory_t(obj, name){
				var index = obj.options[obj.selectedIndex].value;

					$selectChild = $(\'#\'+name+\'_threec\');
					var html = \'<option value="0">请选择三级分类</option>\';
					if (!window[\'_\'+name] || !window[\'_\'+name][index]) {
						$selectChild.html(html);
						return false;
					}
					for(var i=0; i< window[\'_\'+name][index].length; i++){
						html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';
					}
					$selectChild.html(html);

			}
		</script>
					';
				define('TPL_INIT_CATEGORY', true);
			}

			$html .=
				'<div class="row row-fix tpl-category-container">
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
			if ($autosave == true) {
				$html .='<select class="form-control tpl-category-parent" id="' . $name . '_parent" name="' . $name . '[parentid]" onchange="renderCategory(this,\'' . $name . '\');autosave(\'' . $name . '_parent\');">';
			}else{
				$html .='<select class="form-control tpl-category-parent" id="' . $name . '_parent" name="' . $name . '[parentid]" onchange="renderCategory(this,\'' . $name . '\')">';
			}
			$html .='<option value="0">请选择一级分类</option>';

			$ops = '';
			foreach ($parents as $row) {
				$html .= '
					<option value="' . $row['id'] . '" ' . (($row['id'] == $parentid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
			}
			$html .= '
				</select>
			</div>';


			//二级
			if (!empty($parentid) || !empty($children)) {

					$html .='<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
					if ($autosave == true) {
						$html .= '<select class="form-control tpl-category-child" id="' . $name . '_child" name="' . $name . '[childid]" onchange="renderCategory_t(this,\'' . $name . '\');autosave(\'' . $name . '_child\');">';
					}else{
						$html .= '<select class="form-control tpl-category-child" id="' . $name . '_child" name="' . $name . '[childid]" onchange="renderCategory_t(this,\'' . $name . '\')">';
					}

					$html .= '<option value="0">请选择二级分类</option>';
					if (!empty($parentid) && !empty($children[$parentid])) {
						foreach ($children[$parentid] as $row) {
							$html .= '
							<option value="' . $row['id'] . '"' . (($row['id'] == $childid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
						}
					}
					$html .= '
						</select>
					</div>';
			}
		//三级
		if (!empty($childid) || !empty($children)) {
			$html .= '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
			if ($autosave == true) {
				$html .= '<select class="form-control tpl-category-child" id="' . $name . '_threec" name="' . $name . '[threecs]" onchange="autosave(\'' . $name . '_threec\');">';
			}else{
				$html .= '<select class="form-control tpl-category-child" id="' . $name . '_threec" name="' . $name . '[threecs]" >';
			}
			$html .= '<option value="0">请选择三级分类</option>';
			if (!empty($childid) && !empty($children[$childid])) {
				foreach ($children[$childid] as $row) {
					$html .= '
					<option value="' . $row['id'] . '"' . (($row['id'] == $threec) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
				}
			}
			$html .= '
				</select>
			</div>';
		}
			////
		$html .= '</div>';
	return $html;
}
function fm_mc_credit_update($uid, $credittype, $creditval = 0,$up = true, $log = array()) {
	global $_W;
	$credittype = trim($credittype);
	$credittypes = mc_credit_types();
	if (!in_array($credittype, $credittypes)) {
		return error('-1', "指定的用户积分类型 “{$credittype}”不存在.");
	}

	$creditval = floatval($creditval);
	if (empty($creditval)) {
		return true;
	}
	$value = pdo_fetchcolumn("SELECT $credittype FROM " . tablename('mc_members') . " WHERE `uid` = :uid", array(':uid' => $uid));
	if ($creditval > 0 || ($value + $creditval >= 0) || $credittype == 'credit6') {
		if ($up == 'js') {
			pdo_update('mc_members', array($credittype => $value - $creditval), array('uid' => $uid));
		}elseif ($up == 'add') {
			pdo_update('mc_members', array($credittype => $value + $creditval), array('uid' => $uid));
		}else{
			if ($up) {
				pdo_update('mc_members', array($credittype => $value + $creditval), array('uid' => $uid));
			}else{
				pdo_update('mc_members', array($credittype => $creditval), array('uid' => $uid));
			}
		}


	} else {
		return error('-1', "积分类型为“{$credittype}”的积分不够，无法操作。");
	}
		if (empty($log) || !is_array($log)) {
		$log = array($uid, '未记录', 0, 0);
	}
	$clerk_type = intval($log[5]) ? intval($log[5]) : 1;
	$data = array(
		'uid' => $uid,
		'credittype' => $credittype,
		'uniacid' => $_W['uniacid'],
		'num' => $creditval,
		'createtime' => TIMESTAMP,
		'operator' => intval($log[0]),
		'module' => trim($log[2]),
		'clerk_id' => intval($log[3]),
		'store_id' => intval($log[4]),
		'clerk_type' => $clerk_type,
		'remark' => $log[1],
	);
	pdo_insert('mc_credits_record', $data);

	return true;
}
function getmobilenames($names) {
	switch ($names) {
	  case 'photosvote.html':
	    $name = '投票首页';
	    break;
	  case 'tuser.html':
	    $name = '投票详情页';
	    break;
	  case 'tuserphotos.html':
	    $name = '投票相册展示页';
	    break;
	  case 'reg.html':
	    $name = '注册报名页';
	    break;
	  case 'paihang.html':
	    $name = '排行榜页';
	    break;
	  case 'des.html':
	    $name = '活动详情页';
	    break;

	  default:
	    $name = '女神来了';
	    break;
	}
	return $name;
}

function update_tags_piaoshu ($rid) {
	$tags = pdo_fetchall("SELECT * FROM ".tablename('fm_photosvote_tags')." WHERE rid = :rid", array(':rid' => $rid));

	foreach ($tags as $key => $row) {
		if ($row['icon'] == 1) {
			$photosnum = pdo_fetch('SELECT SUM(photosnum) as ta, SUM(xnphotosnum) as tb FROM '.tablename('fm_photosvote_provevote').' WHERE rid = :rid AND tagpid = :tagpid AND status = 1', array(':rid' => $rid,':tagpid' => $row['id']));
		}elseif ($row['icon'] == 2) {
			$photosnum = pdo_fetch('SELECT  SUM(photosnum) as ta, SUM(xnphotosnum) as tb FROM '.tablename('fm_photosvote_provevote').' WHERE rid = :rid AND tagid = :tagid AND status = 1', array(':rid' => $rid,':tagid' => $row['id']));
		}elseif ($row['icon'] == 3) {
			$photosnum = pdo_fetch('SELECT SUM(photosnum) as ta, SUM(xnphotosnum) as tb FROM '.tablename('fm_photosvote_provevote').' WHERE rid = :rid AND tagtid = :tagtid AND status = 1', array(':rid' => $rid,':tagtid' => $row['id']));
		}
		pdo_update('fm_photosvote_tags', array('piaoshu' => $photosnum['ta'] + $photosnum['tb']), array('rid' => $rid, 'id' => $row['id']));
	}
}


if(!function_exists('paginationm')) {
	/**
	 * 生成分页数据
	 * @param int $currentPage 当前页码
	 * @param int $totalCount 总记录数
	 * @param string $url 要生成的 url 格式，页码占位符请使用 *，如果未写占位符，系统将自动生成
	 * @param int $pageSize 分页大小
	 * @return string 分页HTML
	 */
	function paginationm($tcount, $pindex, $psize = 15, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => '')) {
		global $_W;
		$pdata = array(
			'tcount' => 0,
			'tpage' => 0,
			'cindex' => 0,
			'findex' => 0,
			'pindex' => 0,
			'nindex' => 0,
			'lindex' => 0,
			'options' => ''
		);
		if($context['ajaxcallback']) {
			$context['isajax'] = true;
		}

		$pdata['tcount'] = $tcount;
		$pdata['tpage'] = ceil($tcount / $psize);
		if($pdata['tpage'] <= 1) {
			return '';
		}
		$cindex = $pindex;
		$cindex = min($cindex, $pdata['tpage']);
		$cindex = max($cindex, 1);
		$pdata['cindex'] = $cindex;
		$pdata['findex'] = 1;
		$pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
		$pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
		$pdata['lindex'] = $pdata['tpage'];

		if($context['isajax']) {
			if(!$url) {
				$url = $_W['script_name'] . '?' . http_build_query($_GET);
			}
			$pdata['faa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', ' . $context['ajaxcallback'] . ')"';
			$pdata['paa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', ' . $context['ajaxcallback'] . ')"';
			$pdata['naa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', ' . $context['ajaxcallback'] . ')"';
			$pdata['laa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', ' . $context['ajaxcallback'] . ')"';
		} else {
			if($url) {
				$pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
				$pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
				$pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
				$pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
			} else {
				$_GET['page'] = $pdata['findex'];
				$pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
				$_GET['page'] = $pdata['pindex'];
				$pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
				$_GET['page'] = $pdata['nindex'];
				$pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
				$_GET['page'] = $pdata['lindex'];
				$pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
			}
		}

		$html = '<div class="pagination pagination-centered"><ul class="pagination pagination-centered">';
		if($pdata['cindex'] > 1) {
			$html .= "<li><a {$pdata['faa']} class=\"pager-nav\">首页</a></li>";
			$html .= "<li><a {$pdata['paa']} class=\"pager-nav\">&laquo;上一页</a></li>";
		}
		//页码算法：前5后4，不足10位补齐
		if(!$context['before'] && $context['before'] != 0) {
			$context['before'] = 5;
		}
		if(!$context['after'] && $context['after'] != 0) {
			$context['after'] = 4;
		}

		if($context['after'] != 0 && $context['before'] != 0) {
			$range = array();
			$range['start'] = max(1, $pdata['cindex'] - $context['before']);
			$range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
			if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
				$range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
				$range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
			}
			for ($i = $range['start']; $i <= $range['end']; $i++) {
				if($context['isajax']) {
					$aa = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $i . '\', ' . $context['ajaxcallback'] . ')"';
				} else {
					if($url) {
						$aa = 'href="?' . str_replace('*', $i, $url) . '"';
					} else {
						$_GET['page'] = $i;
						$aa = 'href="?' . http_build_query($_GET) . '"';
					}
				}
				$html .= ($i == $pdata['cindex'] ? '<li class="active"><a href="javascript:;">' . $i . '</a></li>' : "<li><a {$aa}>" . $i . '</a></li>');
			}
		}

		if($pdata['cindex'] < $pdata['tpage']) {
			$html .= "<li><a {$pdata['naa']} class=\"pager-nav\">下一页&raquo;</a></li>";
			$html .= "<li><a {$pdata['laa']} class=\"pager-nav\">尾页</a></li>";
		}
		$html .= '</ul></div>';
		return $html;
	}
}


/**
*求两个已知经纬度之间的距离,单位为米
*@param lng1,lng2 经度
*@param lat1,lat2 纬度
*@return float 距离，单位千米
*@author www.fmoons.com
**/
if(!function_exists('getDistance')) {
	function getDistance($lng1,$lat1,$lng2,$lat2){
		//将角度转为狐度
		$radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
		$radLat2=deg2rad($lat2);
		$radLng1=deg2rad($lng1);
		$radLng2=deg2rad($lng2);
		$a=$radLat1-$radLat2;
		$b=$radLng1-$radLng2;
		$s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
		return sprintf("%.2f", $s/1000);
	}
}
if(!function_exists('mobilelimit')) {
	function mobilelimit($mobile){
		$phone = $mobile;
		$mphone = substr($phone,3,4);
		$lphone = str_replace($mphone,"****",$phone);
		return $lphone;
	}
}
if(!function_exists('getrealip')) {
	function getrealip(){
		$arr_ip_header = array(
		    "HTTP_CLIENT_IP",
		    "HTTP_X_FORWARDED_FOR",
		    "REMOTE_ADDR",
		    "HTTP_CDN_SRC_IP",
		    "HTTP_PROXY_CLIENT_IP",
		    "HTTP_WL_PROXY_CLIENT_IP"
		);
		$client_ip = 'unknown';
		foreach ($arr_ip_header as $key) {
		    if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != "unknown") {
		        $client_ip = $_SERVER[$key];
		        break;
		    }
		}
		if ($pos = strpos($client_ip,',')){
			$client_ip = substr($client_ip,$pos+1);
		}
		return $client_ip;
	}
}
/**
 * 获取客户端IP地址
 *
 * @param boolean $s_type ip类型[ip|long]
 * @return string $ip
 */
 if(!function_exists('getip')) {
	function getip($b_ip = true){
		$arr_ip_header = array(
		    "HTTP_CLIENT_IP",
		    "HTTP_X_FORWARDED_FOR",
		    "REMOTE_ADDR",
		    "HTTP_CDN_SRC_IP",
		    "HTTP_PROXY_CLIENT_IP",
		    "HTTP_WL_PROXY_CLIENT_IP"
		);
		$client_ip = 'unknown';
		foreach ($arr_ip_header as $key) {
		    if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != "unknown") {
		        $client_ip = $_SERVER[$key];
		        break;
		    }
		}
		if ($pos = strpos($client_ip,',')){
			$client_ip = substr($client_ip,$pos+1);
		}
		return $client_ip;
	}
 }
if(!function_exists('GetIpLookup')) {
	function GetIpLookup($ip = ''){
		if(empty($ip)){
			$ip = getip();
		}
//		//return $ip;
//		$ip = @file_get_contents("https://dm-81.data.aliyun.com/rest/160601/ip/getIpInfo.json");
//		//$ip = json_decode($ip,true);
//		/**print_r($ip);exit;
//		return $ip;
//		//$res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
//		//return $ip;
//		if(empty($res)){ return false; }
//		$jsonMatches = array();
//		preg_match('#\{.+?\}#', $res, $jsonMatches);
//		if(!isset($jsonMatches[0])){ return false; }**/
//		$json = json_decode($ip,true);
//		print_r($json);exit;
//		if(isset($json['code']) && $json['code'] == 0){
//			$jsons = $json['data'];
//			unset($json['code']);
//		}else{
//			return false;
//		}
		load()->func('communication');
		$url = "http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
		$content = ihttp_get($url);
		$info = @json_decode($content['content'], true);
		//print_r($info);
		return $info['data'];
	}
}
if(!function_exists('getiparr')) {
	function getiparr($ip) {
		global $_GPC;
		$r = pdo_fetch("SELECT isgetiparr FROM ".tablename('fm_photosvote_reply_vote')." WHERE rid = :rid", array(':rid' => $_GPC['rid']));
		if ($r['isgetiparr']) {
			$ip = GetIpLookup($ip);
			$iparr = array();
			$iparr['area'] .= $ip['area'];
			$iparr['country'] .= $ip['country'];
			$iparr['province'] .= $ip['region'];
			$iparr['city'] .= $ip['city'];
			//$iparr['district'] .= $ip['county'];
			$iparr['ist'] .= $ip['isp'];
			$iparr = iserializer($iparr);
		}
		return $iparr;
	}
}
if(!function_exists('getuserlocal')) {
	function getuserlocal($key, $getip='') {
		global $_W;
		load()->func('communication');
		if (empty($getip)) {
			//$ip = CLIENT_IP;
		}else{
			$ip = $getip;
		}
		$getipurl = "https://apis.map.qq.com/ws/location/v1/ip?ip=".$ip."&key=".$key;
		//$getipurl = "http://apis.map.qq.com/ws/district/v1/search?&keyword=海门&key=".$key;
		$content = ihttp_get($getipurl);
		$info = @json_decode($content['content'], true);
		return $info['result']['ad_info'];
	}
}
if(!function_exists('getoauth')) {
	function getoauth() {
		global $_W;
		$setting = setting_load('site');
		$id = isset($setting['site']['key']) ? $setting['site']['key'] : '0';
		$onlyoauth = pdo_fetch("SELECT * FROM ".tablename('fm_photosvote_onlyoauth')." WHERE siteid = :siteid", array(':siteid' => $id));
		return $onlyoauth['fmauthtoken'];
	}
}

function resize($srcImage,$toFile,$newName,$maxWidth = 100,$maxHeight = 100,$imgQuality=100)
{

    list($width, $height, $type, $attr) = getimagesize($srcImage);
    if($width < $maxWidth  || $height < $maxHeight) return ;
    switch ($type) {
    case 1: $img = imagecreatefromgif($srcImage); break;
    case 2: $img = imagecreatefromjpeg($srcImage); break;
    case 3: $img = imagecreatefrompng($srcImage); break;
    }
    $scale = min($maxWidth/$width, $maxHeight/$height); //求出绽放比例

    if($scale < 1) {
    $newWidth = floor($scale*$width);
    $newHeight = floor($scale*$height);
    $newImg = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $toFile = preg_replace("/(.gif|.jpg|.jpeg|.png)/i","",$toFile);

    switch($type) {
        case 1: if(imagegif($newImg, "$toFile$newName.gif", $imgQuality))
        return "$newName.gif"; break;
        case 2: if(imagejpeg($newImg, "$toFile$newName.jpg", $imgQuality))
        return "$newName.jpg"; break;
        case 3: if(imagepng($newImg, "$toFile$newName.png", $imgQuality))
        return "$newName.png"; break;
        default: if(imagejpeg($newImg, "$toFile$newName.jpg", $imgQuality))
        return "$newName.jpg"; break;
    }
    imagedestroy($newImg);
    }
    imagedestroy($img);
    return false;
}

function des_encode($key, $text){
	$y = pkcs5_pad($text);
	$td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_CBC, ''); //使用MCRYPT_DES算法,cbc模式
	$ks = mcrypt_enc_get_key_size($td);

	mcrypt_generic_init($td, $key, $key); //初始处理
	$encrypted = mcrypt_generic($td, $y); //解密
	mcrypt_generic_deinit($td); //结束
	mcrypt_module_close($td);
	return base64_encode($encrypted);
}
function pkcs5_pad($text, $block = 8){
	$pad = $block - (strlen($text) % $block);
	return $text . str_repeat(chr($pad), $pad);
}

function Sec2Time($time){
    if(is_numeric($time)){
    $value = array(
      "years" => 0, "days" => 0, "hours" => 0,
      "minutes" => 0, "seconds" => 0,
    );
    if($time >= 31556926){
      $value["years"] = floor($time/31556926);
      $time = ($time%31556926);
    }
    if($time >= 86400){
      $value["days"] = floor($time/86400);
      $time = ($time%86400);
    }
    if($time >= 3600){
      $value["hours"] = floor($time/3600);
      $time = ($time%3600);
    }
    if($time >= 60){
      $value["minutes"] = floor($time/60);
      $time = ($time%60);
    }
    $value["seconds"] = floor($time);
    //return (array) $value;
    $t = '';
    if (!empty($value["years"])) {
    		$t .= $value["years"] ."年";
    }elseif (!empty($value["days"])) {
    		$t .= $value["days"] ."天";
    }elseif (!empty($value["hours"])) {
    		$t .= $value["hours"] ."小时";
    }elseif (!empty($value["minutes"])) {
    		$t .= $value["minutes"] ."分";
    }else{
    		$t .= $value["seconds"]."秒";
    }

    Return $t;

     }else{
    return (bool) FALSE;
    }
 }

 function fm_tpl_form_field_daterange($name, $value = array(), $time = false) {
	$s = '';

	if (empty($time) && !defined('TPL_INIT_DATERANGE_DATE')) {
		$s = '
<script type="text/javascript">
	require(["daterangepicker"], function(){
		$(function(){
			$(".daterange.daterange-date").each(function(){
				var elm = this;
				$(this).daterangepicker({
					startDate: $(elm).prev().prev().val(),
					endDate: $(elm).prev().val(),
					format: "YYYY-MM-DD"
				}, function(start, end){
					$(elm).find(".date-title").html(start.toDateStr() + " 至 " + end.toDateStr());
					$(elm).prev().prev().val(start.toDateStr());
					$(elm).prev().val(end.toDateStr());
				});
			});
		});
	});
</script>
';
		define('TPL_INIT_DATERANGE_DATE', true);
	}

	if (!empty($time) && !defined('TPL_INIT_DATERANGE_TIME')) {
		$s = '
<script type="text/javascript">
	require(["daterangepicker"], function(){
		$(function(){
			$(".daterange.daterange-time").each(function(){
				var elm = this;
				$(this).daterangepicker({
					startDate: $(elm).prev().prev().val(),
					endDate: $(elm).prev().val(),
					format: "YYYY-MM-DD HH:mm",
					timePicker: true,
					timePicker12Hour : false,
					timePickerIncrement: 1,
					minuteStep: 1
				}, function(start, end){
					$(elm).find(".date-title").html(start.toDateTimeStr() + " 至 " + end.toDateTimeStr());
					$(elm).prev().prev().val(start.toDateTimeStr());
					$(elm).prev().val(end.toDateTimeStr());
				});
			});
		});
	});
</script>
';
		define('TPL_INIT_DATERANGE_TIME', true);
	}
	if ($value['starttime'] !== false && $value['start'] !== false) {
		if($value['start']) {
			$value['starttime'] = empty($time) ? date('Y-m-d',strtotime($value['start'])) : date('Y-m-d H:i',strtotime($value['start']));
		}
		$value['starttime'] = empty($value['starttime']) ? (empty($time) ? date('Y-m-d') : date('Y-m-d H:i') ): $value['starttime'];
	} else {
		$value['starttime'] = '请选择';
	}

	if ($value['endtime'] !== false && $value['end'] !== false) {
		if($value['end']) {
			$value['endtime'] = empty($time) ? date('Y-m-d',strtotime($value['end'])) : date('Y-m-d H:i',strtotime($value['end']));
		}
		$value['endtime'] = empty($value['endtime']) ? $value['starttime'] : $value['endtime'];
	} else {
		$value['endtime'] = '请选择';
	}
	$s .= '
	<input name="'.$name . '[start]'.'" type="hidden" value="'. $value['starttime'].'" />
	<input name="'.$name . '[end]'.'" type="hidden" value="'. $value['endtime'].'" />
	<button class="btn btn-default daterange '.(!empty($time) ? 'daterange-time' : 'daterange-date').'" type="button"><span class="date-title">'.$value['starttime'].' 至 '.$value['endtime'].'</span> <i class="fa fa-calendar"></i></button>
	';
	return $s;
}

function fmreferer($default = '') {
	global $_GPC, $_W;
	$_W['referer'] = !empty($_GPC['referer']) ? $_GPC['referer'] : $_SERVER['HTTP_REFERER'];;
	$_W['referer'] = substr($_W['referer'], -1) == '?' ? substr($_W['referer'], 0, -1) : $_W['referer'];

	if (strpos($_W['referer'], 'member.php?act=login')) {
		$_W['referer'] = $default;
	}
	$_W['referer'] = $_W['referer'];
	$_W['referer'] = str_replace('&amp;', '&', $_W['referer']);
	$reurl = parse_url($_W['referer']);

	if (!empty($reurl['host']) && !in_array($reurl['host'], array($_SERVER['HTTP_HOST'], 'www.' . $_SERVER['HTTP_HOST'])) && !in_array($_SERVER['HTTP_HOST'], array($reurl['host'], 'www.' . $reurl['host']))) {
		$_W['referer'] = $_W['siteroot'];
	} elseif (empty($reurl['host'])) {
		$_W['referer'] = $_W['siteroot'] . './' . $_W['referer'];
	}
	return strip_tags($_W['referer']);
}

function getmaps() {
		$maps = "{'上海':[121.472644, 31.231706],'东莞':[113.8953, 22.901],'东营':[118.7073, 37.5513],'中山':[113.4229, 22.478],'临汾':[111.4783, 36.1615],'临沂':[118.3118, 35.2936],'丹东':[124.541, 40.4242],'丽水':[119.5642, 28.1854],'乌鲁木齐':[87.9236, 43.5883],'云南':[102.712251, 25.040609],'佛山':[112.8955, 23.1097],'保定':[115.0488, 39.0948],'兰州':[103.5901, 36.3043],'内蒙古':[111.670801, 40.818311],'包头':[110.3467, 41.4899],'北京':[116.405285, 39.904989],'北海':[109.314, 21.6211],'南京':[118.8062, 31.9208],'南宁':[108.479, 23.1152],'南昌':[116.0046, 28.6633],'南通':[121.1023, 32.1625],'厦门':[118.1689, 24.6478],'台州':[121.1353, 28.6688],'台湾':[121.509062, 25.044332],'合肥':[117.29, 32.0581],'吉林':[125.3245, 43.886841],'呼和浩特':[111.4124, 40.4901],'咸阳':[108.4131, 34.8706],'哈尔滨':[127.9688, 45.368],'唐山':[118.4766, 39.6826],'嘉兴':[120.9155, 30.6354],'四川':[104.065735, 30.659462],'大同':[113.7854, 39.8035],'大连':[122.2229, 39.4409],'天津':[117.190182, 39.125596],'太原':[112.3352, 37.9413],'威海':[121.9482, 37.1393],'宁夏':[106.278179, 38.46637],'宁波':[121.5967, 29.6466],'安徽':[117.283042, 31.86119],'宝鸡':[107.1826, 34.3433],'宿迁':[118.5535, 33.7775],'山东':[117.000923, 36.675807],'山西':[112.549248, 37.857014],'常州':[119.4543, 31.5582],'广东':[113.280637, 23.125178],'广州':[113.5107, 23.2196],'广西':[108.320004, 22.82402],'廊坊':[116.521, 39.0509],'延安':[109.1052, 36.4252],'张家口':[115.1477, 40.8527],'徐州':[117.5208, 34.3268],'德州':[116.6858, 37.2107],'惠州':[114.6204, 23.1647],'成都':[103.9526, 30.7617],'扬州':[119.4653, 32.8162],'承德':[117.5757, 41.4075],'拉萨':[91.1865, 30.1465],'新疆':[87.617733, 43.792818],'无锡':[120.3442, 31.5527],'日照':[119.2786, 35.5023],'昆明':[102.9199, 25.4663],'杭州':[119.5313, 29.8773],'枣庄':[117.323, 34.8926],'柳州':[109.3799, 24.9774],'株洲':[113.5327, 27.0319],'武汉':[114.3896, 30.6628],'汕头':[117.1692, 23.3405],'江苏':[118.767413, 32.041544],'江西':[115.892151, 28.676493],'江门':[112.6318, 22.1484],'沈阳':[123.1238, 42.1216],'沧州':[116.8286, 38.2104],'河北':[114.502461, 38.045474],'河南':[113.665412, 34.757975],'河源':[114.917, 23.9722],'泉州':[118.3228, 25.1147],'泰安':[117.0264, 36.0516],'泰州':[120.0586, 32.5525],'济南':[117.1582, 36.8701],'济宁':[116.8286, 35.3375],'浙江':[120.153576, 30.287459],'海南':[110.33119, 20.031971],'海口':[110.3893, 19.8516],'淄博':[118.0371, 36.6064],'淮安':[118.927, 33.4039],'深圳':[114.5435, 22.5439],'清远':[112.9175, 24.3292],'温州':[120.498, 27.8119],'渭南':[109.7864, 35.0299],'湖北':[114.298572, 30.584355],'湖南':[112.982279, 28.19409],'湖州':[119.8608, 30.7782],'湘潭':[112.5439, 27.7075],'滨州':[117.8174, 37.4963],'潍坊':[119.0918, 36.524],'澳门':[113.54909, 22.198951],'烟台':[120.7397, 37.5128],'玉溪':[101.9312, 23.8898],'珠海':[113.7305, 22.1155],'甘肃':[103.823557, 36.058039],'盐城':[120.2234, 33.5577],'盘锦':[121.9482, 41.0449],'石家庄':[114.4995, 38.1006],'福州':[119.4543, 25.9222],'福建':[119.306239, 26.075302],'秦皇岛':[119.2126, 40.0232],'绍兴':[120.564, 29.7565],'聊城':[115.9167, 36.4032],'肇庆':[112.1265, 23.5822],'舟山':[122.2559, 30.2234],'苏州':[120.6519, 31.3989],'莱芜':[117.6526, 36.2714],'菏泽':[115.6201, 35.2057],'营口':[122.4316, 40.4297],'葫芦岛':[120.1575, 40.578],'衡水':[115.8838, 37.7161],'衢州':[118.6853, 28.8666],'西宁':[101.4038, 36.8207],'西安':[109.1162, 34.2004],'西藏':[91.132212, 29.660361],'贵州':[106.713478, 26.578343],'贵阳':[106.6992, 26.7682],'辽宁':[123.429096, 41.796767],'连云港':[119.1248, 34.552],'邢台':[114.8071, 37.2821],'邯郸':[114.4775, 36.535],'郑州':[113.4668, 34.6234],'鄂尔多斯':[108.9734, 39.2487],'重庆':[106.504962, 29.533155],'金华':[120.0037, 29.1028],'铜川':[109.0393, 35.1947],'银川':[106.3586, 38.1775],'镇江':[119.4763, 31.9702],'长春':[125.8154, 44.2584],'长沙':[113.0823, 28.2568],'长治':[112.8625, 36.4746],'阳泉':[113.4778, 38.0951],'陕西':[108.948024, 34.263161],'青岛':[120.4651, 36.3373],'青海':[101.778916, 36.623178],'韶关':[113.7964, 24.7028],'香港':[114.173355, 22.320048],'黑龙江':[126.642464, 45.756967]}";
		return $maps;
	}

