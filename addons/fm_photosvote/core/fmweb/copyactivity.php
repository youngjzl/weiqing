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
	$oldrid = intval($_GPC['rid']);
	$list = pdo_fetch('SELECT * FROM '.tablename($this->table_reply).' WHERE rid = '.$oldrid.'');
	$uniacid = $_W['uniacid'];
	if (checksubmit('submit')) {
		if (empty($_GPC['content'])) {
			$this->message('新活动关键字不能为空');
		}
		$key = pdo_fetch('SELECT * FROM '.tablename('rule_keyword').' WHERE content = :content AND uniacid = :uniacid', array(':content' => $_GPC['content'],':uniacid' => $uniacid));
		if (!empty($key)) {
			$this->message('新活动关键字不能和以前的关键字重复');
		}
		pdo_insert('rule', array('uniacid' => $uniacid, 'name' => $list['title'],'module' => 'fm_photosvote', 'status' =>'1'));
		$rid = pdo_insertid();
		if (!empty($rid)) {
			pdo_insert('rule_keyword', array('uniacid' => $uniacid,'rid' => $rid, 'content' => $_GPC['content'],'module' => 'fm_photosvote', 'type' =>'1', 'status' =>'1'));

			$id_basic = pdo_fetch('SELECT id FROM '.tablename($this->table_reply).' WHERE 1 ORDER BY id DESC LIMIT 1');
			$i_id = $id_basic['id']+1;
			//print_r($id_basic);exit;
			$date_basic = $list;
			$date_basic['id'] = $i_id;
			$date_basic['rid'] = $rid;
			pdo_insert($this->table_reply, $date_basic);

			if ($_GPC['share'] == 'on') {
				$date_share = pdo_fetch('SELECT * FROM '.tablename($this->table_reply_share).' WHERE rid = '.$oldrid.'');
				$date_share['id'] = $i_id;
				$date_share['rid'] = $rid;
				pdo_insert($this->table_reply_share, $date_share);
			}
			if ($_GPC['huihua'] == 'on') {
				$date_huihua = pdo_fetch('SELECT * FROM '.tablename($this->table_reply_huihua).' WHERE rid = '.$oldrid.'');
				$date_huihua['id'] = $i_id;
				$date_huihua['rid'] = $rid;
				pdo_insert($this->table_reply_huihua, $date_huihua);
			}
			if ($_GPC['display'] == 'on') {
				$date_display = pdo_fetch('SELECT * FROM '.tablename($this->table_reply_display).' WHERE rid = '.$oldrid.'');
				$date_display['id'] = $i_id;
				$date_display['rid'] = $rid;
				pdo_insert($this->table_reply_display, $date_display);
			}
			if ($_GPC['vote'] == 'on') {
				$date_vote = pdo_fetch('SELECT * FROM '.tablename($this->table_reply_vote).' WHERE rid = '.$oldrid.'');
				$date_vote['id'] = $i_id;
				$date_vote['rid'] = $rid;
				pdo_insert($this->table_reply_vote, $date_vote);
			}
			if ($_GPC['rbody'] == 'on') {
				$date_body = pdo_fetch('SELECT * FROM '.tablename($this->table_reply_body).' WHERE rid = '.$oldrid.'');
				$date_body['id'] = $i_id;
				$date_body['rid'] = $rid;
				pdo_insert($this->table_reply_body, $date_body);
			}
			if ($_GPC['gift'] == 'on') {
				$date_jifen = pdo_fetch('SELECT * FROM '.tablename($this->table_jifen).' WHERE rid = '.$oldrid.'');
				$date_jifen['id'] = $i_id;
				$date_jifen['rid'] = $rid;
				pdo_insert($this->table_jifen, $date_jifen);


				$date_gifts = pdo_fetchall('SELECT * FROM '.tablename($this->table_jifen_gift).' WHERE rid = '.$oldrid.'');
				$date_gift = array();
				foreach ($date_gifts as $key => $row) {
					$id_gift = pdo_fetch('SELECT id FROM '.tablename($this->table_jifen_gift).' WHERE 1 ORDER BY id DESC LIMIT 1');
					$date_gift = $row;
					$date_gift['id'] = $id_gift['id'] + 1;
					$date_gift['rid'] = $rid;
					pdo_insert($this->table_jifen_gift, $date_gift);
				}

			}
			if ($_GPC['raward'] == 'on') {
				/**$date_share = pdo_fetch('SELECT * FROM '.tablename($this->table_reply_share).' WHERE rid = '.$oldrid.'');
				$date_share['id'] = $i_id;
				$date_share['rid'] = $rid;
				pdo_insert($this->table_reply_share, $date_share);**/
			}
			if ($_GPC['announce'] == 'on') {
				$date_announces = pdo_fetchall('SELECT * FROM '.tablename($this->table_announce).' WHERE rid = '.$oldrid.'');
				$date_announce = array();
				foreach ($date_announces as $key => $row) {
					$id_announce = pdo_fetch('SELECT id FROM '.tablename($this->table_announce).' WHERE 1 ORDER BY id DESC LIMIT 1');
					$date_announce = $row;
					$date_announce['id'] = $id_announce['id'] + 1;
					$date_announce['rid'] = $rid;
					pdo_insert($this->table_announce, $date_announce);
				}
			}
			if ($_GPC['banner'] == 'on') {
				$date_banners = pdo_fetchall('SELECT * FROM '.tablename($this->table_banners).' WHERE rid = '.$oldrid.'');
				$date_banner = array();
				foreach ($date_banners as $key => $row) {
					$id_banner = pdo_fetch('SELECT id FROM '.tablename($this->table_banners).' WHERE 1 ORDER BY id DESC LIMIT 1');
					$date_banner = $row;
					$date_banner['id'] = $id_banner['id'] + 1;
					$date_banner['rid'] = $rid;
					pdo_insert($this->table_banners, $date_banner);
				}
			}
			if ($_GPC['adv'] == 'on') {
				$date_advs = pdo_fetchall('SELECT * FROM '.tablename($this->table_advs).' WHERE rid = '.$oldrid.'');
				$date_adv = array();
				foreach ($date_advs as $key => $row) {
					$id_adv = pdo_fetch('SELECT id FROM '.tablename($this->table_advs).' WHERE 1 ORDER BY id DESC LIMIT 1');
					$date_adv = $row;
					$date_adv['id'] = $id_adv['id'] + 1;
					$date_adv['rid'] = $rid;
					pdo_insert($this->table_advs, $date_adv);
				}
			}
			if ($_GPC['answer'] == 'on') {
				$date_answers = pdo_fetchall('SELECT * FROM '.tablename($this->table_answer).' WHERE rid = '.$oldrid.'');
				$date_answer = array();
				foreach ($date_answers as $key => $row) {
					$id_answer = pdo_fetch('SELECT id FROM '.tablename($this->table_answer).' WHERE 1 ORDER BY id DESC LIMIT 1');
					$date_answer = $row;
					$date_answer['id'] = $id_answer['id'] + 1;
					$date_answer['rid'] = $rid;
					pdo_insert($this->table_answer, $date_answer);
				}
			}
			if ($_GPC['stopip'] == 'on') {
				$date_stopips = pdo_fetchall('SELECT * FROM '.tablename($this->table_iplist).' WHERE rid = '.$oldrid.'');
				$date_stopip = array();
				foreach ($date_stopips as $key => $row) {
					$id_stopip = pdo_fetch('SELECT id FROM '.tablename($this->table_iplist).' WHERE 1 ORDER BY id DESC LIMIT 1');
					$date_stopip = $row;
					$date_stopip['id'] = $id_stopip['id'] + 1;
					$date_stopip['rid'] = $rid;
					pdo_insert($this->table_iplist, $date_stopip);
				}
			}


			$this->message('复制成功',$this->createWebUrl('index'),'success');
		}



	}
} elseif ($operation == 'post') {

} else {
	$this->message('请求方式不存在');
}
include $this -> template('web/copyactivity');
