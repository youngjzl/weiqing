<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
load() -> func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	if (!empty($_GPC['displayorder'])) {
		foreach ($_GPC['displayorder'] as $id => $displayorder) {
			$update = array('displayorder' => $displayorder);
			pdo_update($this->table_answer, $update, array('id' => $id));
		}
		$this->message('题目排序更新成功！', $this->createWebUrl('answer', array('rid' => $rid)), 'success');
	}
	$list = pdo_fetchall("SELECT * FROM " . tablename($this -> table_answer) . " WHERE rid = '{$rid}' " . $uni . "  ORDER BY displayorder DESC");

	//	include $this->template('web/answer');
} elseif ($operation == 'post') {

	$id = intval($_GPC['id']);
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			$this->message('请填写题目标题');
		}
		$getanswers = array(
				'0' => array('keyid0' => $_GPC['keyid0'],'content0' => $_GPC['content0']),
				'1' => array('keyid1' => $_GPC['keyid1'],'content1' => $_GPC['content1']),
				'2' => array('keyid2' => $_GPC['keyid2'],'content2' => $_GPC['content2']),
				'3' => array('keyid3' => $_GPC['keyid3'],'content3' => $_GPC['content3']),
		);
		$data = array('uniacid' => $uniacid, 'rid' => $rid, 'title' => $_GPC['title'], 'answer' => iserializer($getanswers), 'thumb' => $_GPC['thumb'], 'enabled' => intval($_GPC['enabled']), 'key' => strtoupper($_GPC['key']), 'displayorder' => intval($_GPC['displayorder']));

		if (!empty($id)) {
			pdo_update($this -> table_answer, $data, array('id' => $id));
			load() -> func('file');
			file_delete($_GPC['thumb_old']);
		} else {
			pdo_insert($this -> table_answer, $data);
			$id = pdo_insertid();
		}
		$this->message('更新题目成功！', $this -> createWebUrl('answer', array('op' => 'display', 'rid' => $rid)), 'success');
	}
	$answer = pdo_fetch("select * from " . tablename($this -> table_answer) . " where id=:id and rid=:rid limit 1", array(":id" => $id, ':rid' => $rid));
	$answers = iunserializer($answer['answer']);
	//print_r($answers);
	//include $this->template('web/banner_post');
} elseif ($operation == 'delete') {
	$id = intval($_GPC['id']);
	$answer = pdo_fetch("SELECT id  FROM " . tablename($this -> table_answer) . " WHERE id = '$id' AND rid=" . $rid . "");
	if (empty($answer)) {
		$this->message('抱歉，题目不存在或是已经被删除！', $this -> createWebUrl('answer', array('op' => 'display', 'rid' => $rid)), 'error');
	}
	pdo_delete($this -> table_answer, array('id' => $id));
	$this->message('题目删除成功！', $this -> createWebUrl('answer', array('op' => 'display', 'rid' => $rid)), 'success');
} else {
	$this->message('请求方式不存在');
}
include $this -> template('web/answer');
