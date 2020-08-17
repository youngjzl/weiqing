<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
load()->func('tpl');
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					$update = array('displayorder' => $displayorder);
					pdo_update($this->table_advs, $update, array('id' => $id));
				}
				$this->message('分组排序更新成功！', $this->createWebUrl('adv', array('rid' => $rid)), 'success');
			}
            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_advs) . " WHERE rid = '{$rid}' ".$uni."  ORDER BY displayorder DESC");
			//include $this->template('web/adv');
        } elseif ($operation == 'post') {

            $id = intval($_GPC['id']);
            if (checksubmit('submit')) {
				if (empty($_GPC['advname'])) {
                    $this->message('请填写标题');
                }
                $data = array(
                    'uniacid' => $uniacid,
                    'rid' => $rid,
                    'advname' => $_GPC['advname'],
                    'description' => htmlspecialchars_decode($_GPC['description']),
                    'link' => $_GPC['link'],
                    'thumb' => $_GPC['thumb'],
                    'enabled' => $_GPC['enabled'] == 'on' ? 1 : 0,
                    'ismiaoxian' => $_GPC['ismiaoxian'] == 'on' ? 1 : 0,
                    'issuiji' => $_GPC['issuiji'] == 'on' ? 1 : 0,
                    'nexttime' => intval($_GPC['nexttime']),
                    'times' => intval($_GPC['times']),
                    'displayorder' => intval($_GPC['displayorder'])
                );
                if (!empty($id)) {
                    pdo_update($this->table_advs, $data, array('id' => $id));
					load()->func('file');
					file_delete($_GPC['thumb_old']);
                } else {
                    pdo_insert($this->table_advs, $data);
                    $id = pdo_insertid();
                }
                $this->message('更新广告成功！', $this->createWebUrl('adv', array('op' => 'display', 'rid' => $rid)), 'success');
            }
            $adv = pdo_fetch("select * from " . tablename($this->table_advs) . " where id=:id and rid=:rid limit 1", array(":id" => $id, ':rid' => $rid));
			//include $this->template('web/adv_post');
		} elseif ($operation == 'miaoxian') {
			$reply = pdo_fetch("SELECT ismiaoxian, mxnexttime, mxtimes FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			if (checksubmit('submit')) {
				pdo_update($this->table_reply, array('ismiaoxian' => intval($_GPC['ismiaoxian']), 'mxnexttime' => intval($_GPC['mxnexttime']), 'mxtimes' => intval($_GPC['mxtimes']) ), array('rid' => $rid));
				$this->message('更新秒显广告成功！', $this->createWebUrl('adv', array('op' => 'display', 'rid' => $rid)), 'success');
			}
	   	} elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $adv = pdo_fetch("SELECT id  FROM " . tablename($this->table_advs) . " WHERE id = '$id' AND rid=" . $rid . "");
            if (empty($adv)) {
                $this->message('抱歉，广告不存在或是已经被删除！', $this->createWebUrl('adv', array('op' => 'display', 'rid' => $rid)), 'error');
            }
            pdo_delete($this->table_advs, array('id' => $id));
            $this->message('广告删除成功！', $this->createWebUrl('adv', array('op' => 'display', 'rid' => $rid)), 'success');
        } else {
            $this->message('请求方式不存在');
        }
        include $this->template('web/adv');
