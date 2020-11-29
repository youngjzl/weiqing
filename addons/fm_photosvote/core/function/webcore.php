<?php
/**
 * 女神来了模块定义
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
//require IA_ROOT. '/addons/fm_photosvote/core/defines.php';
class Webcore extends WeModule {
	public $title 			 = '女神来了！';
	public $table_reply  	 = 'fm_photosvote_reply';//规则 相关设置
	public $table_reply_share  = 'fm_photosvote_reply_share';//规则 相关设置
	public $table_reply_huihua  = 'fm_photosvote_reply_huihua';//规则 相关设置
	public $table_reply_display  = 'fm_photosvote_reply_display';//规则 相关设置
	public $table_reply_vote  = 'fm_photosvote_reply_vote';//规则 相关设置
	public $table_reply_body  = 'fm_photosvote_reply_body';//规则 相关设置
	public $table_users  	 = 'fm_photosvote_provevote';	//报名参加活动的人
	public $table_pnametag 	 = 'fm_photosvote_pnametag';	//默认口号
	public $table_voteer  	 = 'fm_photosvote_voteer';	//投票的人资料
	public $table_tags  	 = 'fm_photosvote_tags';	//
	public $table_users_picarr  = 'fm_photosvote_provevote_picarr';	//
	public $table_users_videoarr  = 'fm_photosvote_provevote_videoarr';	//
	public $table_users_voice  	= 'fm_photosvote_provevote_voice';	//
	public $table_users_name  	= 'fm_photosvote_provevote_name';	//
	public $table_log        = 'fm_photosvote_votelog';//投票记录
	public $table_qunfa        = 'fm_photosvote_qunfa';//投票记录
	public $table_shuapiao        = 'fm_photosvote_vote_shuapiao';//封禁记录
	public $table_shuapiaolog        = 'fm_photosvote_vote_shuapiaolog';//刷票记录
	public $table_bbsreply   = 'fm_photosvote_bbsreply';//投票记录
	public $table_banners    = 'fm_photosvote_banners';//幻灯片
	public $table_advs  	 = 'fm_photosvote_advs';//广告
	public $table_gift  	 = 'fm_photosvote_gift';
	public $table_data  	 = 'fm_photosvote_data';
	public $table_iplist 	 = 'fm_photosvote_iplist';//禁止ip段
	public $table_iplistlog  = 'fm_photosvote_iplistlog';//禁止ip段
	public $table_announce   = 'fm_photosvote_announce';//公告
	public $table_templates   = 'fm_photosvote_templates';//模板
	public $table_designer   = 'fm_photosvote_templates_designer';//模板页面
	public $table_designer_menu   = 'fm_photosvote_templates_designer_menu';//模板页面
	public $table_order   = 'fm_photosvote_order';//付费投票
	public $table_jifen   = 'fm_photosvote_jifen';//积分抽奖
	public $table_jifen_gift   = 'fm_photosvote_jifen_gift';//礼物
	public $table_user_gift   = 'fm_photosvote_user_gift';//礼物
	public $table_user_zsgift   = 'fm_photosvote_user_zsgift';//礼物
	public $table_msg   = 'fm_photosvote_message';//消息
	public $table_orderlog   = 'fm_photosvote_orderlog';//消息
	public $table_counter   = 'fm_photosvote_counter';//投票数据统计
	public $table_qrcode   = 'fm_photosvote_qrcode';//投票数据统计
	public $table_answer   = 'fm_photosvote_answer';//答题
	public $table_source   = 'fm_photosvote_source';//来源
	public $table_school   = 'fm_photosvote_school';//学校

	public function __construct() {
		global $_W;
	}

	public function getregsh($type = '1', $rid = '') {
		global $_W;
		if ($type == 1) {
			$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE status != 1 AND uniacid =:uniacid", array(':uniacid' => $_W['uniacid']));
			if (empty($total)) {
				return '0';
			}else{
				return $total;
			}
		}elseif ($type == 2) {
			$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE status != 1 AND rid = :rid", array(':rid' => $rid));
			if (empty($total)) {
				return '0';
			}else{
				return $total;
			}

		}else{
			return '0';
		}

	}

	public function getmessage($rid='') {
		global $_W;
		if (!empty($rid)) {
			$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_bbsreply)." WHERE status != 1 AND rid =:rid", array(':rid' => $rid));
		}else{
			$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_bbsreply)." WHERE status != 1 AND uniacid =:uniacid", array(':uniacid' => $_W['uniacid']));
		}

			if (empty($total)) {
				return '0';
			}else{
				return $total;
			}

	}

	public function getlistsum($type = '1') {
		global $_W;
		$uniacid = $_W['uniacid'];
		$time = time();
		if ($type == 1) {
			$total = pdo_fetchcolumn('SELECT count(1) FROM '.tablename($this->table_reply).' WHERE uniacid = '.$uniacid.' AND '.$time.' >= start_time AND '.$time.' < end_time');

		}elseif ($type == 2) {
			$total = pdo_fetchcolumn('SELECT count(1) FROM '.tablename($this->table_reply).' WHERE uniacid = '.$uniacid.'');
		}elseif ($type == 3) {
			$total = pdo_fetchcolumn('SELECT count(1) FROM '.tablename($this->table_templates).' WHERE uniacid = '.$uniacid.' or uniacid = 0');

		}

		if (empty($total)) {
			return '0';
		}else{
			return $total;
		}

	}

	public function gethdname($rid,$limit='4') {
		$reply = pdo_fetch("SELECT title FROM ".tablename($this->table_reply)." WHERE rid = ".$rid."");
		return cutstr($reply['title'], $limit);
	}
	public function message($msg, $redirect = '', $type = '', $tips = false, $extend = array()) {
			global $_W, $_GPC;

			if($redirect == 'refresh') {
				$redirect = $_W['script_name'] . '?' . $_SERVER['QUERY_STRING'];
			}
			if($redirect == 'referer') {
				$redirect = fmreferer();
			}
				$redirect = safe_gpc_url($redirect);

			if($redirect == '') {
				$type = in_array($type, array('success', 'error', 'info', 'warning', 'ajax', 'sql')) ? $type : 'info';
			} else {
				$type = in_array($type, array('success', 'error', 'info', 'warning', 'ajax', 'sql')) ? $type : 'success';
			}

			if (empty($msg) && !empty($redirect)) {
				header('Location: '.$redirect);
				exit;
			}
			$label = $type;
			if($type == 'error') {
				$label = 'danger';
			}
			if($type == 'ajax' || $type == 'sql') {
				$label = 'warning';
			}
			include $this->template('web/common/message');
			exit;
		}

}
