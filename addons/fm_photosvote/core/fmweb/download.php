<?php
/**
 * 女神来了导出
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');
global $_GPC, $_W;
$rid = intval($_GPC['rid']);
$uniacid = $_W['uniacid'];
if (empty($rid)) {
	$this->message('抱歉，传递的参数错误！', '', 'error');
}

$reply = pdo_fetch("SELECT * FROM " . tablename($this -> table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
$v = pdo_fetch("SELECT uni_all_users FROM " . tablename($this -> table_reply_vote) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
$d = pdo_fetch("SELECT regtitlearr FROM " . tablename($this -> table_reply_display) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
if (!empty($d['regtitlearr'])) {
	$rt = iunserializer($d['regtitlearr']);
}

if ($_GPC['uni_all_users'] != 1) {
	if ($uniacid != $_GPC['uniacid']) {
		$uni = " AND uniacid = " . $uniacid;
	}
}
$where = '';
$where .= ' rid= '.$rid;
if ($_GPC['status'] == 1) {
	$where .= ' AND status=1';
} else {
	$where .= ' AND status=0';
}

/**if (!empty($_GPC['start_time']) && !empty($_GPC['end_time'])) {
	$starttime = $_GPC['start_time'];
	$endtime = $_GPC['end_time'];
	$where .= " AND createtime >= " . $starttime;
	$where .= " AND createtime < " . $endtime;
}**/
$rt['cmmrealname'] = empty($rt['cmmrealname']) ? "姓名" : $rt['cmmrealname'];
$rt['cmmmobile'] = empty($rt['cmmmobile']) ? "手机" : $rt['cmmmobile'];
$rt['cmmphotosname'] = empty($rt['cmmphotosname']) ? "宣言" : $rt['cmmphotosname'];
$rt['cmmregdes'] = empty($rt['cmmregdes']) ? "介绍" : $rt['cmmregdes'];
$rt['cmmweixin'] = empty($rt['cmmweixin']) ? "微信" : $rt['cmmweixin'];
$rt['cmmqqhao'] = empty($rt['cmmqqhao']) ? "QQ号" : $rt['cmmqqhao'];
$rt['cmmemail'] = empty($rt['cmmemail']) ? "电子邮箱" : $rt['cmmemail'];
$rt['cmmjob'] = empty($rt['cmmjob']) ? "职业" : $rt['cmmjob'];
$rt['cmmxingqu'] = empty($rt['cmmxingqu']) ? "兴趣" : $rt['cmmxingqu'];
$rt['cmmaddress'] = empty($rt['cmmaddress']) ? "地址" : $rt['cmmaddress'];

$count = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this -> table_users) . ' WHERE  ' . $where . ' ' . $uni . ' ORDER BY `uid` DESC,`createtime` DESC');
$pagesize = ceil($count/5000);

$tableheader = array('uid' => '用户ID', 'nickname' => $rt['cmmrealname'], 'realname' => '真实姓名', 'photosnum' => '真实票数', 'xnphotosnum' => '虚拟票数', 'hits' => '真实人气', 'xnhits' => '虚拟人气', 'sharenum' => '分享数','zans' => '点赞', 'comments' => '评论', 'hylevel' => '活跃等级', 'sex' => '性别', 'tags' => '分组', 'mobile' => $rt['cmmmobile'], 'weixin' => $rt['cmmweixin'], 'qqhao' => $rt['cmmqqhao'], 'email' => $rt['cmmemail'], 'job' => $rt['cmmjob'], 'xingqu' => $rt['cmmxingqu'], 'address' => $rt['cmmaddress'], 'photoname' => $rt['cmmphotosname'], 'description' => $rt['cmmregdes'], 'images' => '图片', 'music' => '音乐', 'vedio' => '视频', 'status' => '状态', 'ordersn' => '报名付费状态', 'createip' => 'IP', 'createtime' => '报名时间');

$keys = array_keys($tableheader);
$html = "\xEF\xBB\xBF";
foreach ($tableheader as $li) {
	$html .= $li . "\t ,";
}
$html .= "\n";
	//$size = ceil(count($list) / 500);
	for ($j = 1; $j <= $pagesize; $j++) {

		$sql = "SELECT * FROM " . tablename($this -> table_users) . " WHERE " . $where . $uni . " ORDER BY `uid` DESC,`createtime` DESC LIMIT " . ($j - 1) * 5000 . ",5000 ";
		$list = pdo_fetchall($sql);

		if (!empty($list)) {
			$size = ceil(count($list) / 500);
			for ($i = 0; $i < $size; $i++) {
				$buffer = array_slice($list, $i * 500, 500);
				$user = array();
				foreach($buffer as $value) {

					$value['sharenum'] =  pdo_fetchcolumn("SELECT COUNT(1) FROM " . tablename($this -> table_data) . " WHERE tfrom_user = :tfrom_user and rid = :rid", array(':tfrom_user' => $value['from_user'], ':rid' => $rid));
					$value['tags'] = $this -> gettagname($value['tagid'], $value['tagpid'], $value['tagtid'], $rid);

					$value['sex'] = ($value['sex'] == 2) ? '女' : '男';
					$value['status'] = ($value['status'] == 1) ? '已审核' : '未审核';
					if ($row['ordersn']) {
						$value['ordersn'] = '已付费';
					} else {
						$value['ordersn'] = '未付费';
					}
					$value['comments'] = $this -> getcommentnum($rid, $uniacid, $value['from_user']);
					$value['hylevel'] = intval($this -> fmvipleavel($rid, $uniacid, $value['from_user']));
					$value['createtime'] = date('Y-m-d H:i:s', $value['createtime']);
					$value['music'] = tomedia($value['music']);
					$value['vedio'] = tomedia($value['vedio']);
					$description=preg_replace("/\s+/", " ", $value['description']); //过滤多余回车
					$value['description'] = strip_tags($description);

					$photosarr = pdo_fetchall('SELECT photos FROM ' . tablename($this -> table_users_picarr) . ' WHERE rid =:rid  AND from_user=:from_user ORDER BY `id` DESC', array(':rid' => $rid, ':from_user' => $value['from_user']));
					$photos = '';
					foreach ($photosarr as $key => $row) {
						$photos .= tomedia($row['photos']).' | ';
					}
					$value['images'] = $photos;
					unset($photos);
					foreach($keys as $key) {
						$data[] = $value[$key];
					}
					$user[] = implode("\t ,", $data) . "\t ,";
					unset($data);
				}
				$html .= implode("\n", $user);
			}
		}
	}

$filename = $_GPC['title'] . '_' . $rid . '_' . $pindex;
header("Content-type:text/csv");
header("Content-Disposition:attachment; filename=" . $filename . ".csv");
echo $html;
exit();