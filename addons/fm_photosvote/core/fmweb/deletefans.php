<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');

		$reply = pdo_fetch("select * from ".tablename($this->table_reply)." where rid = :rid", array(':rid' => $rid));
		$rdisplay = pdo_fetch("select * from ".tablename($this->table_reply_display)." where rid = :rid", array(':rid' => $rid));
        if (empty($reply)) {
            $this->webmessage('抱歉，要修改的活动不存在或是已经被删除！');
        }

		if ($_GPC['type'] == 'votemembers') {
			 foreach ($_GPC['idArr'] as $k => $id) {
	            $id = intval($id);
	            if ($id == 0) {
	                //continue;
				}
				$fans = pdo_fetch("select from_user from ".tablename($this->table_voteer)." where id = :id", array(':id' => $id));
				if (empty($fans)) {
	                $this->webmessage('抱歉，选中的粉丝数据不存在！');
	            }
				//删除粉丝参与记录
				pdo_delete($this->table_voteer, array('id' => $id));
	        }
			 $this->webmessage('粉丝记录删除成功！', $this->createWebUrl('votemembers', array('rid' => $rid)), 0);
		}elseif ($_GPC['type'] == 'votemembers_one') {
	            $id = intval($_GPC['id']);
	            if ($id == 0) {
	                //continue;
				}
				$fans = pdo_fetch("select from_user from ".tablename($this->table_voteer)." where id = :id", array(':id' => $id));
				if (empty($fans)) {
					message('抱歉，选中的粉丝数据不存在！', referer(), 'error');
	            }
				//删除粉丝参与记录
				pdo_delete($this->table_voteer, array('id' => $id));
			 	message('粉丝记录删除成功！', referer(), 'success');
		}elseif ($_GPC['type'] == 'one') {
            $uid = intval($_GPC['uid']);
			$fans = pdo_fetch("select from_user,photosnum,xnphotosnum,hits,xnhits from ".tablename($this->table_users)." where uid = :uid AND rid = :rid", array(':uid' => $uid, ':rid' => $rid));


			if (empty($fans)) {
				message('抱歉，选中的参赛者数据不存在！', referer(), 'error');
            }

			//删除粉丝参与记录
			pdo_delete($this->table_users, array('uid' => $uid, 'rid' => $rid));
			pdo_delete($this->table_users_picarr, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_users_voice, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_users_videoarr, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_users_name, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_log, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_log, array('tfrom_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_bbsreply, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_data, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_order, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_orderlog, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_msg, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_user_gift, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_user_zsgift, array('from_user' => $fans['from_user'], 'rid' => $rid));
			pdo_delete($this->table_data, array('from_user' => $fans['from_user'], 'rid' => $rid));
			$date = array(
				'ljtp_total' => $rdisplay['ljtp_total']-$fans['photosnum'],
				'xunips' => $rdisplay['xunips']-$fans['xnphotosnum'],
				'cyrs_total' => $rdisplay['cyrs_total']-$fans['hits'],
				'xuninum' => $rdisplay['xuninum']-$fans['xnhits'],
				'csrs_total' => $rdisplay['csrs_total']-1,
			);
			pdo_update($this->table_reply_display,$date, array('rid' => $rid));
			message('参赛者删除成功！', referer(), 'success');
		}else{
			 foreach ($_GPC['idArr'] as $k => $id) {
	            $id = intval($id);
	            if ($id == 0) {
	               // continue;
				}
				$fans = pdo_fetch("select from_user,photosnum,xnphotosnum,hits,xnhits from ".tablename($this->table_users)." where id = :id", array(':id' => $id));


				if (empty($fans)) {
	                $this->webmessage('抱歉，选中的粉丝数据不存在！');
	            }

				//删除粉丝参与记录
				pdo_delete($this->table_users, array('id' => $id));
				pdo_delete($this->table_users_picarr, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_users_voice, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_users_name, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_log, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_log, array('tfrom_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_bbsreply, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_data, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_order, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_orderlog, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_msg, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_user_gift, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_user_zsgift, array('from_user' => $fans['from_user'], 'rid' => $rid));
				pdo_delete($this->table_data, array('from_user' => $fans['from_user'], 'rid' => $rid));
				$date = array(
					'ljtp_total' => $rdisplay['ljtp_total']-$fans['photosnum'],
					'xunips' => $rdisplay['xunips']-$fans['xnphotosnum'],
					'cyrs_total' => $rdisplay['cyrs_total']-$fans['hits'],
					'xuninum' => $rdisplay['xuninum']-$fans['xnhits'],
					'csrs_total' => $rdisplay['csrs_total']-1,
				);
				pdo_update($this->table_reply_display,$date, array('rid' => $rid));

	        }
			$this->webmessage('粉丝记录删除成功！', '', 0);
		}


