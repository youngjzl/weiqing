<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');

$uniacid = $_W['uniacid'];
//幻灯片
$banners = pdo_fetchall("SELECT bannername,link,thumb FROM " . tablename($this -> table_banners) . " WHERE enabled=1 AND uniacid= '{$uniacid}' ORDER BY displayorder ASC");
$keyword = $_GPC['keyword'];
if ($_GPC['op'] == 'list') {
		$item_per_page = empty($_GPC['indextpxz']) ? 10 : $_GPC['indextpxz'];
		$page_number = max(1, intval($_GPC['pagesnum']));
		if(!is_numeric($page_number)){
   			header('HTTP/1.1 500 Invalid page number!');
    		exit();
		}
      	//print_r($_GPC['indextpxz']);
		$position = ($page_number-1) * $item_per_page;
		$where = '';
		if (!empty($_GPC['keyword'])) {
			$keyword = $_GPC['keyword'];
			if (is_numeric($keyword))
				$where .= " AND id = '".$keyword."'";
			else
				$where .= " AND title LIKE '%{$keyword}%'";

		}

		$where .= " AND status = '1'";

		$where .= " ORDER BY `id` DESC";



		$list = pdo_fetchall('SELECT * FROM '.tablename($this->table_reply).' WHERE uniacid = :uniacid '.$where.'  LIMIT ' . $position . ',' . $item_per_page, array(':uniacid' => $uniacid) );
		foreach ($list as $key => $row) {
			$picture = !empty($row['picture']) ? $row['picture'] : 'addons/fm_photosvote/static/mobile/public/images/pimages.jpg' ;
			$list[$key]['avatar'] = tomedia($picture);
			$list[$key]['username'] .= $row['title'];
			$list[$key]['linkurl'] .= $this->createMobileUrl('photosvote', array('rid' => $row['rid']));
		}
    		echo json_encode($list);
		exit;
}


$title = $_W['account']['name'] . ' 活动';
$fmimage = $this -> getpicarr($uniacid, $rid, $from_user, 1);
if (!empty($rshare['sharelink'])) {
	$_share['link'] = $rshare['sharelink'];
}else{
$_share['link'] = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('shareuserview', array('rid' => $rid, 'fromuser' => $from_user, 'tfrom_user' => $from_user));
//分享URL
}
$_share['title'] = $this -> get_share($uniacid, $rid, $from_user, $rshare['sharetitle']);
$_share['content'] = $this -> get_share($uniacid, $rid, $from_user, $rshare['sharecontent']);
$_share['imgUrl'] = $this -> getphotos($rshare['sharephoto'], $rshare['sharephoto'], $rshare['sharephoto']);

$templatename = $rbasic['templates'];
if ($templatename != 'default' && $templatename != 'stylebase') {
	require FM_CORE . 'fmmobile/tp.php';
}
$toye = $this -> templatec($templatename, $_GPC['do']);
include $this -> template($toye);