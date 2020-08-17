<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
$uniacid = $_W['uniacid'];
$row = pdo_fetch("SELECT title FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
$rowrule = pdo_fetch("SELECT content FROM ".tablename('rule_keyword')." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
if ($_GPC['op'] == 'addkeyword') {
	$type = intval($_GPC['type']);
	$istitle = pdo_fetch("SELECT content FROM ".tablename('rule_keyword')." WHERE uniacid = :uniacid AND content = :content ORDER BY `id` DESC", array(':uniacid' => $uniacid, ':content' => $_GPC['content']));
	if ($istitle && $type != 3) {
		$fmdata = array(
			"success" => -1,
			"msg" => '关键词已存在，请更换',
		);
		echo json_encode($fmdata);
		exit();
		//$this->message('关键词已存在，请更换', $this->createWebUrl('start', array('title' => $_GPC['title'], 'content' => $_GPC['content'])), 'error');
	}

	//pdo_delete('rule_keyword', array('rid' => $rid, 'uniacid' => $uniacid));
	$rowtpl = array(
		'rid' => $rid,
		'uniacid' => $uniacid,
		'module' => $_GPC['m'],
		'status' => '1',
		'type' => $type,
		'content' => $_GPC['content'],
		'displayorder' => range_limit($rule['displayorder'], 0, 254)
	);
	pdo_insert('rule_keyword', $rowtpl);

	$msg = '添加成功！';
	$fmdata = array(
		"success" => 1,
		"msg" => $msg,
	);
	echo json_encode($fmdata);
	exit();
}
if (checksubmit('submit')) {
	$istitle = pdo_fetch("SELECT title FROM ".tablename($this->table_reply)." WHERE uniacid = :uniacid AND title = :title ORDER BY `id` DESC", array(':uniacid' => $uniacid, ':title' => $_GPC['title']));
	if ($istitle) {
		$this->message('活动名称已存在，请更换', $this->createWebUrl('start', array('title' => $_GPC['title'], 'content' => $_GPC['content'])), 'error');
	}
	$istitle = pdo_fetch("SELECT content FROM ".tablename('rule_keyword')." WHERE uniacid = :uniacid AND content = :content ORDER BY `id` DESC", array(':uniacid' => $uniacid, ':content' => $_GPC['content']));
	if ($istitle) {
		$this->message('关键词已存在，请更换', $this->createWebUrl('start', array('title' => $_GPC['title'], 'content' => $_GPC['content'])), 'error');
	}

	$rule = array(
		'uniacid' => $uniacid,
		'name' => $_GPC['title'],
		'module' => $_GPC['m'],
		'status' => 1,
		'displayorder' => 0,
	);
	$rule['displayorder'] = range_limit($rule['displayorder'], 0, 254);

	if (!empty($rid)) {
		$result = pdo_update('rule', $rule, array('id' => $rid));
	} else {
		$result = pdo_insert('rule', $rule);
		$rid = pdo_insertid();
	}

	if (!empty($rid)) {

		pdo_delete('rule_keyword', array('rid' => $rid, 'uniacid' => $uniacid));

		$rowtpl = array(
			'rid' => $rid,
			'uniacid' => $uniacid,
			'module' => $_GPC['m'],
			'status' => $rule['status'],
			'type' => 1,
			'content' => $_GPC['content'],
			'displayorder' => $rule['displayorder'],
		);
		pdo_insert('rule_keyword', $rowtpl);

		$insert_basic = array(
			'rid' => $rid,
			'uniacid' => $uniacid,
			'status' => 1,
			'fmset' => 'rbasic',
	        'title' => $_GPC['title'],
	        'ip' => getip()
		);

		$ip = GetIpLookup(getip());
		$insert_basic['iparr'] = $ip['region'];
		if (!empty($row)) {
			pdo_update($this->table_reply,$insert_basic, array('rid' => $rid));
		}else{
			pdo_insert($this->table_reply, $insert_basic);
		}

		$this->message('保存成功，开启新活动', $this->createWebUrl('system', array('op'=>'rbasic', 'rid' => $rid, 'set' => 'rdisplay')), 'success');
	} else {
		$this->message('保存失败', referer(), 'error');
	}
}

include $this->template('web/start');
