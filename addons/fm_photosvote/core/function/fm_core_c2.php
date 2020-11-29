<?php
/**
 * 女神来了模块定义
 * 核心功能文件
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
class FmCoreC2 extends FmCoreC1 {
	public function downloadqrcodeimg($ticket, $filename) {
		//下载图片
		global $_W;
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		load() -> func('file');
		$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket";
		$fileInfo = $this -> downloadWeixinFile($url);
		$updir = '../attachment/images/fm_photosvote_qrcode/' . $uniacid . '/' . date("Y") . '/' . date("m") . '/';
		if (!is_dir($updir)) {
			mkdirs($updir);
		}
		$filename = $updir . $filename . ".jpg";
		$this -> saveWeixinFile($filename, $fileInfo["body"]);
		return $filename;
	}
	public function downloadurl2qr($url, $filename) {
		//下载图片
		global $_W;
		require(IA_ROOT . '/framework/library/qrcode/phpqrcode.php');
		$errorCorrectionLevel = "L";
		$matrixPointSize = "5";
		$filename = empty($filename) ? date("YmdHis") . '' . random(10) :$filename;

		$dfileurl = 'attachment/images/fm_photosvote_qrcode/' . $uniacid . '/' . date("Y") . '/' . date("m") . '/';
		$fileurl = '../' . $dfileurl;
		load() -> func('file');
		if (!is_dir($fileurl)) {
			mkdirs($fileurl);
		}
		$fileurl = $fileurl . '/' . $filename . '.png';

		QRcode::png($url, $fileurl, $errorCorrectionLevel, $matrixPointSize);
		return $fileurl;
	}
	public function doMobileQrcode() {
		global $_GPC,$_W;
		$rid = $_GPC['rid'];
		$uid = $_GPC['uid'];
		$tfrom_user = $_GPC['tfrom_user'];

		$user = pdo_fetch("SELECT * FROM ".tablename($this->table_users)." WHERE from_user = :from_user AND rid = :rid", array(':from_user' => $tfrom_user,':rid' => $rid));
		$fmimage = $this->getpicarr($_W['uniacid'],$rid, $tfrom_user,1);
		if (empty($fmimage)) {
			$fmdata = array(
				"success" => -1,
				"msg" => '请上传并设置封面',
			);
			echo json_encode($fmdata);
			exit;
		}
		//$ewm = $this->qrcodecreate($_GPC['code']);
		$barcode = iunserializer(base64_decode($_GPC['code']));
		$account_api = WeAccount::create();
		$ewm = $account_api->barCodeCreateDisposable($barcode);
		$ticket = UrlEncode($ewm['ticket']);
		$qrname = $tfrom_user . $rid.'_'.$uid;
		$ewmurl = $this->downloadurl2qr($ewm['url'],$qrname);
		if ($ewmurl) {

			if (!empty($ewm['ticket'])) {
				$rhuihua = pdo_fetch("SELECT * FROM ".tablename($this->table_reply_huihua)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
				$qrcode = pdo_fetch("SELECT * FROM ".tablename($this->table_qrcode)." WHERE tfrom_user = :tfrom_user AND ticket = :ticket AND rid = :rid", array(':tfrom_user' => $tfrom_user,':ticket' => $ewm['ticket'],':rid' => $rid));

				$barcode = iunserializer(base64_decode($_GPC['code']));
				if (empty($qrcode)) {
					$insert = array(
						'uniacid' => $_W['uniacid'],
						'rid' => $rid,
						'tfrom_user' => $tfrom_user,
						'qrcid' => $barcode['action_info']['scene']['scene_id'],
						'scene_str' => $barcode['action_info']['scene']['scene_str'],
						'keyword' =>$rhuihua['tcommand'].$uid,
						'name' => $this->getname($rid, $tfrom_user,'8'),
						'model' => '2',
						'ticket' => $ewm['ticket'],
						'url' => $ewm['url'],
						'imgurl' => $ewmurl,
						'expire' => $ewm['expire_seconds'],
						'createtime' => TIMESTAMP,
						'status' => '1',
						'type' => 'scene',
					);
					pdo_insert($this->table_qrcode, $insert);
				}else{
					$insert = array(
						'uniacid' => $_W['uniacid'],
						'qrcid' => $barcode['action_info']['scene']['scene_id'],
						'scene_str' => $barcode['action_info']['scene']['scene_str'],
						'keyword' =>$rhuihua['tcommand'].$uid,
						'name' => $this->getname($rid, $tfrom_user,'8'),
						'model' => '2',
						'tfrom_user' => $tfrom_user,
						'ticket' => $ewm['ticket'],
						'url' => $ewm['url'],
						'imgurl' => $ewmurl,
						'expire' => $ewm['expire_seconds'],
						'createtime' => TIMESTAMP,
					);
					pdo_update($this->table_qrcode, $insert, array('rid' => $rid, 'tfrom_user' => $tfrom_user, 'ticket' => $ewm['ticket']));
				}
			}

		//print_r($ewmurl);

			$fmimage = $this->getpicarr($_W['uniacid'],$rid, $tfrom_user,1);
			load() -> func('file');

			$cfg_markpicurl = IA_ROOT . str_replace("..", "", $ewmurl);
			$cfg_markpicurl = resize($cfg_markpicurl,$cfg_markpicurl,'',200,200,100);
			$cfg_markpicurl = IA_ROOT . str_replace("..", "", $ewmurl);
			//print_r($cfg_markpicurl);
			$groundimage = IA_ROOT . str_replace("..", "", $fmimage['imgpath']);
			//print_r($cfg_markpicurl);
			//print_r($groundimage);
			$filename = $fmimage['photoname'] . ".jpg";
			$save_dir = '../addons/fm_photosvote/haibao/' . $rid . '/' . date("Y") . '/' . date("m") . '/';
			if (!is_dir($save_dir)) {
				mkdirs($save_dir);
			}
			$rdisplay = pdo_fetch("SELECT * FROM ".tablename($this->table_reply_display)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			if (!empty($rdisplay['qrset'])) {
				$qrset = iunserializer($rdisplay['qrset']);
			}
			$save_dir = $save_dir.$filename;
			$cfg_markminwidth = '200';
			$cfg_markminheight = '200';
			$cfg_markwhere = empty($qrset['qr_where']) ? "7" : $qrset['qr_where'];
			$cfg_markwheret = empty($qrset['font_where']) ? "9" : $qrset['font_where'];
			$n = "\n";
			$o = '我是：'.$this->getname($rid, $tfrom_user,'8');
			$a = '编号：'.$user['uid'];
			$b = cutstr($user['photoname'], '6');
			$cfg_marktext = $o.$n.$a.$n.$b.$n;
			$cfg_marksize = empty($qrset['font_size']) ? "20" : $qrset['font_size'];
			$cfg_fontfamily = empty($qrset['font_path']) ? FM_STATIC_MOBILE . 'fonts/msyh.ttf' : FM_STATIC_MOBILE . 'fonts/' . $qrset['font_path'];
			$cfg_markcolor = empty($qrset['font_color']) ? '#0000FF' : $qrset['font_color'];
			$cfg_marktype = '0';
			$cfg_marktypet = '0';

			$this->WaterPoster($save_dir,$groundimage, $cfg_markpicurl, $cfg_markminwidth, $cfg_markminheight, $cfg_markwhere,$cfg_markwheret,$cfg_marktext, $cfg_fontfamily, $cfg_marksize, $cfg_markcolor, $cfg_marktype, $cfg_marktypet);

			pdo_update($this->table_users, array('ewm' => $ewmurl, 'haibao' => $save_dir), array('rid'=>$rid, 'from_user' => $tfrom_user));
			$fmdata = array(
				"success" => 1,
				"linkurl" => tomedia($save_dir).'?v='.time(),
				"msg" => '生成成功',
			);
		}else {
			$fmdata = array(
				"success" => -1,
				"msg" => '生成失败',
			);
		}
		echo json_encode($fmdata);
		exit;
	}
	public function doMobileewmimagehc() {
		global $_GPC, $_W;
		$rid = $_GPC['rid'];
		$tfrom_user=$_GPC['tfrom_user'];
		$user = pdo_fetch("SELECT * FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $tfrom_user,':rid' => $rid));
		//$fmimage = $this->getpicarr($_W['uniacid'],$rid, $tfrom_user,1);

	}
	public function doMobileDhgift() {
		global $_GPC, $_W;
		$rid = $_GPC['rid'];
		$from_user = $_GPC['from_user'];
		$id = $_GPC['id'];
		$jifen = $_GPC['jifen'];
		$userjf = $this->cxjifen($rid, $from_user);
		if ($jifen > $userjf) {
			$data = array(
				'success' => -1,
				'msg' => '您当前没有足够的积分兑换该礼物'
			);
			echo json_encode($data);
			exit ;
		}
		$item = pdo_fetch("SELECT * FROM " . tablename($this -> table_jifen_gift) . ' WHERE id = :id ', array(':id' => $id));
		if (empty($item)) {
			$data = array(
				'success' => -1,
				'msg' => '没有找到您要兑换的礼物，请兑换其他礼物'
			);
			echo json_encode($data);
			exit ;
		}
		$usergift = pdo_fetch("SELECT * FROM " . tablename($this -> table_user_gift) . ' WHERE giftid = :giftid AND from_user = :from_user AND rid = :rid AND status = 1 ', array(':giftid' => $id,':from_user' => $from_user,':rid' => $rid));
		if (empty($usergift)) {
			$data = array(
				'uniacid' => $_W['uniacid'],
				'rid' => $rid,
				'giftid' => $id,
				'giftnum' => 1,
				'status' => 1,
				'from_user' => $from_user,
				'lasttime' => time(),
				'createtime' => time(),
			);
			pdo_insert($this->table_user_gift, $data);
		}else{
			pdo_update($this->table_user_gift, array('giftnum' => $usergift['giftnum'] + 1, 'lasttime' => time()), array('rid' => $rid,'giftid' => $id, 'from_user'=>$from_user));//
		}
		pdo_update($this->table_jifen_gift, array('dhnum' => $item['dhnum'] + 1), array('rid' => $rid,'id' => $id));
		$this->jsjifen($rid, $from_user, $jifen,$item['gifttitle']);
		$data = array(
			'success' => 1,
			'msg' => '兑换成功！'
		);
		echo json_encode($data);
		exit ;
	}
	public function doMobilePagedata() {
		global $_GPC;
		require_once FM_CORE.'fmmobile/pagedata.php';
	}
	public function doMobilePagedatab() {
		global $_GPC;
		require_once FM_CORE.'fmmobile/pagedatab.php';
	}
	public function doMobilePay() {
		global $_GPC,$_W;
		require_once FM_PHOTOSVOTE_PAYMENT."wechat/pay.php";

	}
	public function doWebdownload(){
		require_once FM_CORE.'fmweb/download.php';
	}
	public function doWebtpdownload(){
		require_once FM_CORE.'fmweb/tpdownload.php';
	}
	public function doWebdownloadph(){
		require_once FM_CORE.'fmweb/downloadph.php';
	}
	public function doWebSettuijian() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$data = intval($_GPC['data']);

		$type = $_GPC['type'];
		if (in_array($type, array('tuijian'))) {
			$data = ($data==1?'0':'1');
			pdo_update($this->table_users, array('istuijian' => $data), array("id" => $id));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		if (in_array($type, array('limitsd'))) {

			pdo_update($this->table_users, array('limitsd' => $data), array("id" => $id));
			die(json_encode(array("result" => 1, "data" => $data)));
		}

		if (in_array($type, array('menu'))) {
			pdo_update($this->table_reply, array('menuid' => $data), array("rid" => $id));
			$menu = pdo_fetch('select menuname from ' . tablename($this->table_designer_menu) . ' where id=:id', array(':id' => $data));
			die(json_encode(array("result" => 1, "data" => $data, "menuname" => $menu['menuname'])));
		}

		if (in_array($type, array('qfstatus'))) {
			$data = ($data==1?'0':'1');
			pdo_update($this->table_qunfa, array('status' => $data), array("id" => $id));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		if (in_array($type, array('shenhe'))) {
			$data = ($data==1?'0':'1');
			pdo_update($this->table_bbsreply, array('status' => $data), array("id" => $id));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		die(json_encode(array("result" => 0)));
	}
	public function doWebShuapiao() {
		global $_GPC, $_W;
		$rid = intval($_GPC['rid']);
		$shuapiao = intval($_GPC['shuapiao']);
		$data = $_GPC['data'];
		$ip = $_GPC['ip'];
		$ua = $_GPC['ua'];
		$type = $_GPC['type'];
		if (in_array($type, array('shuapiao'))) {
			if ($shuapiao == 1) {
				pdo_delete($this->table_shuapiao, array('rid' => $rid, 'from_user'=>$data));
				pdo_delete($this->table_shuapiao, array('rid' => $rid, 'ip'=>$ip));
				pdo_delete($this->table_shuapiao, array('rid' => $rid, 'ua'=>$ua));
				pdo_update($this->table_log, array('shuapiao' => 0),array( 'from_user'=>$data, 'rid'=>$rid));
				pdo_update($this->table_log, array('shuapiao' => 0),array( 'ip'=>$ip, 'rid'=>$rid));
				pdo_update($this->table_log, array('shuapiao' => 0),array( 'mobile_info'=>$ua, 'rid'=>$rid));
			}else{
				pdo_insert($this->table_shuapiao, array('rid' => $rid, 'uniacid' => $_W['uniacid'], 'from_user'=>$data, 'ip'=>$ip,'ua'=>$ua, 'createtime' => time()));
				pdo_update($this->table_log, array('shuapiao' => 1),array( 'from_user'=>$data, 'rid'=>$rid));
				pdo_update($this->table_log, array('shuapiao' => 1),array( 'ip'=>$ip, 'rid'=>$rid));
				pdo_update($this->table_log, array('shuapiao' => 1),array( 'mobile_info'=>$ua, 'rid'=>$rid));
			}
			die(json_encode(array("result" => 1, "data" => 1)));
		}
		die(json_encode(array("result" => 0)));
	}
	public function doWebStatusall() {
		global $_GPC, $_W;
		$rid = intval($_GPC['rid']);
		$id = intval($_GPC['id']);
		$data = $_GPC['data'];
		$type = $_GPC['type'];
		if (in_array($type, array('banner'))) {
			if ($data == 1) {
				pdo_update($this->table_banners, array('enabled' => 0),array('id'=>$id));
			}else{
				pdo_update($this->table_banners, array('enabled' => 1),array('id'=>$id));
			}
			die(json_encode(array("result" => 1, "data" => 1)));
		}
		if (in_array($type, array('adv'))) {
			if ($data == 1) {
				pdo_update($this->table_advs, array('enabled' => 0),array('id'=>$id));
			}else{
				pdo_update($this->table_advs, array('enabled' => 1),array('id'=>$id));
			}
			die(json_encode(array("result" => 1, "data" => 1)));
		}

		if (in_array($type, array('adv_miaoxian'))) {
			if ($data == 1) {
				pdo_update($this->table_advs, array('ismiaoxian' => 0),array('id'=>$id));
			}else{
				pdo_update($this->table_advs, array('ismiaoxian' => 1),array('id'=>$id));
			}
			die(json_encode(array("result" => 1, "data" => 1)));
		}

		if (in_array($type, array('adv_suiji'))) {
			if ($data == 1) {
				pdo_update($this->table_advs, array('issuiji' => 0),array('id'=>$id));
			}else{
				pdo_update($this->table_advs, array('issuiji' => 1),array('id'=>$id));
			}
			die(json_encode(array("result" => 1, "data" => 1)));
		}

		die(json_encode(array("result" => 0)));
	}
	// 首页
	public function doWebGetCommon() {
		global $_GPC,$_W;
		$uniacid = $_W['uniacid'];
		$time = time();
		$fm_list = pdo_fetchall('SELECT title,start_time,end_time,rid,picture FROM '.tablename($this->table_reply).' WHERE uniacid = '.$uniacid.' AND '.$time.' >= start_time AND '.$time.' < end_time ORDER BY `start_time` DESC LIMIT 6');

		$html_cshenhe  = '<li class="tpl-dropdown-content-external">
                            <h3>你有 <span class="tpl-color-success">'.$this->getregsh().'</span> 条报名未审核</h3><a href="###" style="margin-top: 3px;">剩余</a>
                        </li>';
		foreach ($fm_list as $key => $row) {
			$ztotal_regsh = 0;$total_regsh = $this->getregsh('2', $row['rid']);$ztotal_regsh += $total_regsh;$sytime = $this->getsytime($row['start_time'], $row['end_time']);
			$html_cshenhe .= '<li class="tpl-dropdown-list-bdbc"><a href="'.$this->createWebUrl('provevote', array('rid' => $row['rid'], 'foo' => 'display')).'" class="tpl-dropdown-list-fl"><span class="am-icon-btn am-icon-eye tpl-dropdown-ico-btn-size tpl-badge-success"></span> '.$row['title'].' <span class="am-badge tpl-badge-success am-round">'.$total_regsh.'</span></a>
	                            	<span class="tpl-dropdown-list-fr">'.$sytime.'</span>
	                        	</li>';
		}

		$html_rhuodong = '<li class="tpl-dropdown-content-external">
                            <h3>你有 <span class="tpl-color-primary">'.$this->getlistsum().'</span> 个活动进行中</h3><a href="'.$this->createWebUrl('index').'">全部</a></li>';
		foreach ($fm_list as $key => $row) {
			$sytime = $this->getsytime($row['start_time'], $row['end_time'], '2');
			$html_rhuodong .= '<li>
	                            <a href="javascript:;" class="tpl-dropdown-content-progress">
	                                <span class="task">
	                        				<span class="desc">'.$row['title'].' </span>
	                                		<span class="percent">'.$sytime.'%</span>
	                                </span>
	                                	<span class="progress">
	                        				<div class="am-progress tpl-progress am-progress-striped am-active"><div class="am-progress-bar am-progress-bar-warning" style="width:'.$sytime.'%"></div></div>
	                    				</span>
	                            </a>
	                        </li>';
		}

		$messages = pdo_fetchall('SELECT * FROM '.tablename($this->table_bbsreply).' WHERE uniacid = '.$uniacid.' AND status != 1 ORDER BY `createtime` DESC LIMIT 6');

		$html_msg  = '<li class="tpl-dropdown-content-external">
                            <h3>你有 <span class="tpl-color-danger">'.$this->getmessage().'</span> 条留言未审核</h3></li><li>';
		foreach ($messages as $key => $row) {
			 if ($row['zan'] ==1){$content = '👍 赞了一个 ~~~';}else{ $content = $row['content'];}
			$html_msg .= '<a href="'.$this->createWebUrl('message', array('rid' => $row['rid'])).'" class="tpl-dropdown-content-message">
                                <span class="tpl-dropdown-content-photo">
                      <img src="'.$this->getname($rid, $row['from_user'],'20' , 'avatar').'" alt="fmoons"> </span>
                                <span class="tpl-dropdown-content-subject">
                      <span class="tpl-dropdown-content-from"> '.$this->getname($rid, $row['from_user'],'8').' </span>
                                <span class="tpl-dropdown-content-time">'.Sec2Time($time - $row['createtime']).'前 </span>
                                </span>
                                <span class="tpl-dropdown-content-font"> '.$content.' </span>
                            </a>';
		}
		$html_msg  .= '</li>';
		$data = array(
				'success' => 1,
				'html_cshenhe' => $html_cshenhe,
				'html_rhuodong' => $html_rhuodong,
				'html_msg' => $html_msg,
				'msg' => '成功！'
			);
			echo json_encode($data);
			exit ;

	}

	public function doWebGetdate() {
		global $_GPC,$_W;
		$uniacid = $_W['uniacid'];
		$type = $_GPC['type'];
		$time = time();
			switch ($type) {
				case 'hdysh' :
					$fm_list = pdo_fetchall('SELECT title,start_time,end_time,rid,picture FROM '.tablename($this->table_reply).' WHERE uniacid = '.$uniacid.' AND '.$time.' >= start_time AND '.$time.' < end_time ORDER BY `start_time` DESC LIMIT 10');
					$html = '';
					foreach ($fm_list as $key => $row) {
						$sytime = $this->getsytime($row['start_time'], $row['end_time'], '2');
						$html .= '<tr>
											<td class="am-text-truncate">
												<img src="'.tomedia($row['picture']).'" alt="'.$row['title'].'" class="user-pic am-img-thumbnail am-circle" style="border-radius: 5px;">
												<a class="font-yellow font-yellow-size"  href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'">'.cutstr($row['title'],'10').'</a>
											</td>
											<td>
					                            	<span class="progress">
					                    				<div class="am-progress tpl-progress am-progress-striped am-active"><div class="am-progress-bar am-progress-bar-warning" style="width:'.$sytime.'%"></div></div>
					                				</span>
											</td>
											<td><span class="percent">'.$sytime.'%</span></td>
											<td>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-user-plus"></i> 报名 '.$this->getshuju('1',$row['rid'], '2').'</span>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-user-plus"></i> 未审核 '.$this->getregsh('2', $row['rid']).'</span>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-eye"></i> 点击 '.$this->getshuju('3',$row['rid'], '2').'</span><br>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-money"></i> 充值 '.$this->getshuju('4',$row['rid'], '2').'</span>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-clone"></i> 投票 '.$this->getshuju('6',$row['rid'], '2').'</span>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-users"></i> 粉丝 '.$this->getshuju('10',$row['rid'], '2').'</span>
											</td>
											<td><a class="am-badge am-badge-primary am-round tpl-badge-primary" href="'.$this->createWebUrl('index', array('rid' => $row['rid'])).'" target="_blank"><i class="am-icon-eye"></i> 管理</a></td>

										</tr>';
					}
				break;
				case 'hdwsh':
					$fm_list_end = pdo_fetchall('SELECT title,start_time,end_time,rid,picture FROM '.tablename($this->table_reply).' WHERE uniacid = '.$uniacid.' AND '.$time.' > end_time ORDER BY `end_time` DESC LIMIT 10');
					$html = '';
					foreach ($fm_list_end as $key => $row) {
						$sytime = $this->getsytime($row['start_time'], $row['end_time'], '2');
						$html .= '<tr>
											<td class="am-text-truncate">
												<img src="'.tomedia($row['picture']).'" alt="'.$row['title'].'" class="user-pic am-img-thumbnail am-circle" style="border-radius: 5px;">
												<a class="font-yellow font-yellow-size"  href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'">'.cutstr($row['title'],'10').'</a>
											</td>
											<td>
					                            	<span class="progress">
					                    				<div class="am-progress tpl-progress am-progress-striped am-active"><div class="am-progress-bar am-progress-bar-warning" style="width:'.$sytime.'%"></div></div>
					                				</span>
											</td>
											<td>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-user-plus"></i> 报名 '.$this->getshuju('1',$row['rid'], '2').'</span>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-user-plus"></i> 未审核 '.$this->getregsh('2', $row['rid']).'</span>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-eye"></i> 点击 '.$this->getshuju('3',$row['rid'], '2').'</span><br />
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-money"></i> 充值 '.$this->getshuju('4',$row['rid'], '2').'</span>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-clone"></i> 投票 '.$this->getshuju('6',$row['rid'], '2').'</span>
												<span class="am-badge am-badge-warning am-round"><i class="am-icon-users"></i> 粉丝 '.$this->getshuju('10',$row['rid'], '2').'</span>
											</td>
											<td><a class="am-badge am-badge-primary am-round tpl-badge-success" href="'.$this->createWebUrl('index', array('rid' => $row['rid'])).'" target="_blank"><i class="am-icon-eye"></i> 查看</a></td>

										</tr>';
					}
				break;
				case 'rank':
					$rank = pdo_fetchall('SELECT rid, from_user,photosnum,xnphotosnum FROM ' . tablename($this -> table_users) . ' WHERE uniacid = '.$uniacid.' AND status = 1 ORDER BY `photosnum` + `xnphotosnum`  DESC LIMIT 6');
					$html = '';
					foreach ($rank as $key => $row) {
						$html .= '<tr>
									<td>
										<img src="'. $this->getname($row['rid'], $row['from_user'],'8','avatar').'" alt="" class="user-pic am-img-thumbnail am-circle">
										<a class="user-name" href="'.$this->createWebUrl('provevote', array('status' => '1', 'member' => 1, 'foo' => 'post', 'rid' => $row['rid'], 'from_user' => $row['from_user'])).'" target="_blank">'.$this->getname($row['rid'], $row['from_user'],'5').'</a>
									</td>
									<td>
										<span class="am-badge tpl-badge-warning am-round">'.($key+1).'</span>
									</td>
									<td class="font-green bold">'.($row['photosnum'] + $row['xnphotosnum']).'</td>
									<td style="font-size: 12px;"><a href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'" target="_blank" style="color: #93a2a9;">'.$this->gethdname($row['rid']).'</a></td>
								</tr>';
					}
				break;
				case 'charge':
					$charge = pdo_fetchall('SELECT rid, from_user,SUM(price) AS pmoney FROM '.tablename($this->table_order).' WHERE uniacid = '.$uniacid.' AND status = 1 GROUP BY `from_user` LIMIT 6');
					array_multisort(array_column($charge,'pmoney'),SORT_DESC,$charge);
					$html = '';
					foreach ($charge as $key => $row) {

						$html .= '<tr>
									<td>
										<img src="'. $this->getname($row['rid'], $row['from_user'],'8','avatar').'" alt="" class="user-pic am-img-thumbnail am-circle">
										<a class="user-name" href="'.$this->createWebUrl('votemembers', array('op' => 'allxiaofei', 'rid' => $row['rid'], 'keyword' => $row['from_user'])).'" target="_blank">'.$this->getname($row['rid'], $row['from_user'],'4').'</a>
									</td>
									<td>
										<span class="am-badge tpl-badge-warning am-round">'.($key+1).'</span>
									</td>
									<td class="font-green bold">'.sprintf("%.2f",$row['pmoney']).'</td>
									<td style="font-size: 12px;"><a href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'" target="_blank" style="color: #93a2a9;">'.$this->gethdname($row['rid']).'</a></td>
								</tr>';
					}
				break;
				case 'gift':
					$gift = pdo_fetchall('SELECT rid, from_user,giftnum FROM ' . tablename($this -> table_users) . ' WHERE uniacid = '.$uniacid.' AND status = 1 ORDER BY `giftnum` DESC LIMIT 6');
					$html  = '';
					foreach ($gift as $key => $row) {

						$html .= '<tr>
									<td>
										<img src="'. $this->getname($row['rid'], $row['from_user'],'8','avatar').'" alt="" class="user-pic am-img-thumbnail am-circle">
										<a class="user-name" href="'.$this->createWebUrl('provevote', array('status' => '1', 'member' => 1, 'foo' => 'post', 'rid' => $row['rid'], 'from_user' => $row['from_user'])).'" target="_blank">'.$this->getname($row['rid'], $row['from_user'],'5').'</a>
									</td>
									<td>
										<span class="am-badge tpl-badge-warning am-round">'.($key+1).'</span>
									</td>
									<td class="font-green bold">'.$row['giftnum'].'</td>
									<td style="font-size: 12px;"><a href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'" target="_blank" style="color: #93a2a9;">'.$this->gethdname($row['rid']).'</a></td>
								</tr>';
					}
				break;
				case 'jifen':
					$jifen = pdo_fetchall('SELECT rid, from_user,jifen FROM ' . tablename($this -> table_voteer) . ' WHERE uniacid = '.$uniacid.' ORDER BY `jifen` DESC LIMIT 6');
					$html  = '';
					foreach ($jifen as $key => $row) {
						$html .= '<tr>
									<td>
										<img src="'. $this->getname($row['rid'], $row['from_user'],'8','avatar').'" alt="" class="user-pic am-img-thumbnail am-circle">
										<a class="user-name" href="'.$this->createWebUrl('votemembers', array('op' => 'edit', 'rid' => $row['rid'], 'from_user' => $row['from_user'])).'" target="_blank">'.$this->getname($row['rid'], $row['from_user'],'5').'</a>
									</td>
									<td>
										<span class="am-badge tpl-badge-warning am-round">'.($key+1).'</span>
									</td>
									<td class="font-green bold">'.$row['jifen'].'</td>
									<td style="font-size: 12px;"><a href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'" target="_blank" style="color: #93a2a9;">'.$this->gethdname($row['rid']).'</a></td>
								</tr>';
					}
				break;
				case 'vote':
					$votelogs = pdo_fetchall('SELECT * FROM ' . tablename($this -> table_log) . ' WHERE `uniacid` = ' . $uniacid . '   ORDER BY `createtime` DESC LIMIT 6');
					$html  = '';
					foreach ($votelogs as $key => $row) {
						$html .= '<tr>
									<td>
										<img src="'.$this->getname($row['rid'], $row['from_user'],'8','avatar').'" alt="" class="user-pic am-img-thumbnail am-circle">
										<a class="user-name" href="###">'.$this->getname($row['rid'], $row['from_user'],'5').'</a>
									</td>
									<td>
										<img src="'.$this->getname($row['rid'], $row['tfrom_user'],'8','avatar').'" alt="" class="user-pic am-img-thumbnail am-circle">
										<a class="user-name" href="###">'.$this->getname($row['rid'], $row['tfrom_user'],'15').'</a>
									</td>
									<td class="font-green bold">'.$row['vote_times'].'</td>
									<td >'.Sec2Time($time - $row['createtime']).'前</td>
									<td style="font-size: 12px;">'.$this->gethdname($row['rid']).'</td>
								</tr>';
					}

				break;
				case 'newreg':
					$list = pdo_fetchall('SELECT uid,rid, from_user,createtime FROM ' . tablename($this -> table_users) . ' WHERE uniacid = '.$uniacid.' AND status = 1 ORDER BY `createtime` DESC LIMIT 9');
					$html = '';
					foreach ($list as $key => $row) {
						$html .= '<tr>
									<td>
										<img src="'.$this->getname($row['rid'], $row['from_user'],'8','avatar').'" alt="" class="user-pic am-img-thumbnail am-circle">
										<a class="user-name" href="'.$this->createWebUrl('provevote', array('status' => '1', 'member' => 1, 'foo' => 'post', 'rid' => $row['rid'], 'from_user' => $row['from_user'])).'">'.$this->getname($row['rid'], $row['from_user'],'4').'</a>
									</td>
									<td style="font-size: 12px;">'.Sec2Time($time - $row['createtime']).'前</td>
									<td style="font-size: 12px;"><a href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'" target="_blank" style="color: #93a2a9;">'.$this->gethdname($row['rid']).'</a></td></tr>';
					}
				break;
				case 'newwsh':
					$list = pdo_fetchall('SELECT uid,rid, from_user,createtime FROM ' . tablename($this -> table_users) . ' WHERE uniacid = '.$uniacid.' AND status != 1 ORDER BY `createtime` DESC LIMIT 9');
					$html = '';
					foreach ($list as $key => $row) {
						$html .= '<tr>
									<td>
										<img src="'.$this->getname($row['rid'], $row['from_user'],'8','avatar').'" alt="" class="user-pic am-img-thumbnail am-circle">
										<a class="user-name" href="'.$this->createWebUrl('provevote', array('status' => '1', 'member' => 1, 'foo' => 'post', 'rid' => $row['rid'], 'from_user' => $row['from_user'])).'">'.$this->getname($row['rid'], $row['from_user'],'4').'</a>
									</td>
									<td style="font-size: 12px;">'.Sec2Time($time - $row['createtime']).'前</td>
									<td style="font-size: 12px;"><a href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'" target="_blank" style="color: #93a2a9;">'.$this->gethdname($row['rid']).'</a></td></tr>';
					}
				break;
				case 'newfans':
					$list = pdo_fetchall('SELECT rid, from_user,createtime FROM ' . tablename($this -> table_voteer) . ' WHERE uniacid = '.$uniacid.'  ORDER BY `createtime` DESC LIMIT 9');
					$html = '';
					foreach ($list as $key => $row) {
						$html .= '<tr>
									<td>
										<img src="'.$this->getname($row['rid'], $row['from_user'],'8','avatar').'" alt="" class="user-pic am-img-thumbnail am-circle">
										<a class="user-name" href="">'.$this->getname($row['rid'], $row['from_user'],'4').'</a>
									</td>
									<td style="font-size: 12px;">'.Sec2Time($time - $row['createtime']).'前</td>
									<td style="font-size: 12px;"><a href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'" target="_blank" style="color: #93a2a9;">'.$this->gethdname($row['rid']).'</a></td>
								</tr>';
					}
				break;
				case 'comment':
					$messages = pdo_fetchall('SELECT * FROM '.tablename($this->table_bbsreply).' WHERE uniacid = '.$uniacid.' AND status != 1 ORDER BY `createtime` DESC LIMIT 200');
					$html = '';
					foreach ($messages as $key => $row) {
						$u = $this->getusercontent($row['rid'], $row['tfrom_user']);
						if ($row['zan'] ==1) {
							$zan = '👍 赞了一个 ~~~';
						}else{
							$zan = $row['content'];
						}
						$html .= '<li class="am-comment">
							<a href="'.$this->createWebUrl('message', array('from_user' => $row['from_user'], 'rid' => $rid, 'keyword' => $row['from_user'])).'" target="_blank"><img src="'.$this->getname($rid, $row['from_user'],'20' , 'avatar').'" alt="fmoons" class="am-comment-avatar am-img-thumbnail am-circle" width="48" height="48"></a>
							<div class="am-comment-main">
								<header class="am-comment-hd">
									<div class="am-comment-meta">
										<a href="'.$this->createWebUrl('message', array('from_user' => $row['from_user'], 'rid' => $rid, 'keyword' => $row['from_user'])).'" class="am-comment-author" target="_blank">'.$this->getname($rid, $row['from_user'],'8').'</a> 评论于 <time datetime="'.$row['createtime'].'" title="'.$row['createtime'].'">'.Sec2Time($time - $row['createtime']).'前</time></div>
								</header>
								<div class="am-comment-bd">
									<p>'.$zan.'</p>
									<blockquote>
										<p>'.$u['photoname'].'</p>
										<small><a class="am-comment-author-reg" href="'.$this->createWebUrl('provevote', array('member' => 1, 'foo' => 'post', 'rid' => $row['rid'], 'from_user' => $row['tfrom_user'])).'" target="_blank"><img src="'.$u['avatar'].'" alt="'.$u['username'].'" class="am-comment-avatar am-img-thumbnail am-circle comment-app-avatar" >'.$u['username'].'</a></small>
									</blockquote>
								</div>
							</div>
						</li>';
					}
				break;
				case 'msgs':
					$msgs = $this->getmsg('','','all');
					$html = '';
					foreach ($msgs['msg'] as $key => $row) {
						$c = $this->getmsgtype($row['type']);
						$html .= '<li>
									<div class="cosC">'.Sec2Time($time - $row['createtime']).'前</div>
									<div class="cosB">'.$this->getname($row['rid'],$row['from_user'],'4').'</div>
									<div class="cosA">
										<span class="cosIco" style="background-color: '.$c['color'].'"><i class="'.$c['icon'].'"></i></span>
										<span> '.$row['content'].'</span>
									</div>
								</li>';
					}


				break;
			default:

					break;
			}

			$data = array(
				'success' => 1,
				'html' => $html,
				'msg' => '成功！'
			);
			echo json_encode($data);
			exit ;

	}
public function doWebGetcdate() {
		global $_GPC,$_W;
		$uniacid = $_W['uniacid'];
		$rid = intval($_GPC['rid']);
		$type = $_GPC['type'];
		$time = time();
		$where = ' `uniacid` = ' . $uniacid;
		if (!empty($rid)) {
			$where .= ' AND `rid` = ' . $rid;
		}
		switch ($type) {
			case 'shebei_list':
				$votelogs = pdo_fetchall('SELECT mobile_info,rid FROM ' . tablename($this -> table_log) . ' WHERE ' . $where . ' AND mobile_info <> ""');

				$data = array();
				//$num = 0;
				foreach ($votelogs as $key => $value) {
					$row = explode(";",$value['mobile_info']);
					if ($row['0'] == 'Linux') {
						$mobile = explode("/",$row['2']);
						$mobile = $mobile['0'];
						$mobile = $mobile['0'];
						$mobile = str_replace(" Build","",$mobile);
						$mobile = str_replace(" ","",$mobile);
						//$data['Android'] ++;
						//$num[$mobile] ++;
						$data[$mobile] ++;


					}elseif ($row['0'] == 'iPhone') {
						//$data['mobile'] .= 'apple';
						$apple = 'iPhone';
						$data[$apple] ++;
						//$numb ++;
						//$data['apple'] = 'apple|'.$numb;
					}else{
						$other = 'other';

						$data[$other] ++;
						//$numc ++;
						//$data['other'] = 'other|'.$numc;
					}
				}
				$dataa = '[';
				$cnum = count($data);
				$i = 0;
				//array_multisort(array_column($data,'value'),SORT_DESC,$data);
				arsort($data);
				$sum = array_sum($data);


				$html = '';
				foreach ($data as $key => $row) {

					$pro = sprintf("%.2f",($row/$sum)*100);
					$html .= '<tr>
								<td class="am-text-truncate">
									<a class="user-name" href="javascript:;">'.$key.'</a>
								</td>
								<td>
		                            	<span class="progress">
		                    				<div class="am-progress tpl-progress am-progress-striped am-active"><div class="am-progress-bar am-progress-bar-warning" style="width:'.$pro.'%"></div></div>
		                				</span>
								</td>
								<td style="font-size: 12px;"><span class="am-badge am-badge-success">'.$row.'</span></td>
							</tr>';
				}
		break;
		case 'reg_list':

			$reply = pdo_fetchall('SELECT title, rid FROM ' . tablename($this -> table_reply) . ' WHERE ' . $where . ' ORDER BY `status` DESC, `end_time` DESC, `id` DESC');

			$html = '';
			foreach ($reply as $key => $row) {

				$t = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE rid = :rid", array(':rid' => $row['rid']));//报名


				$t_ysh = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE rid = :rid AND status = 1", array(':rid' => $row['rid']));//报名已审核
				$t_wsh = $t - $t_ysh;//报名已审核
				if ($t < 1) {
					$t_wcl = 100;//完成率
				}else{
					$t_wcl = sprintf("%.0f",($t_ysh/$t)*100);//完成率
				}
				if ($t_wcl >= 100) {
					$style_bar = 'am-progress-bar-huise';
				}else{
					$style_bar = 'am-progress-bar-warning';
				}
				$html .= '<tr>
								<td class="am-text-truncate">
									<a class="user-name" href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'" target="_blank">'.cutstr($row['title'], '8', '...') .'</a>
								</td>
								<td>
		                            	<span class="progress">
		                    				<div class="am-progress tpl-progress am-progress-striped am-active"><div class="am-progress-bar '.$style_bar.'" style="width:'.$t_wcl.'%">'.$t_wcl.'%</div></div>
		                				</span>
								</td>
								<td style="font-size: 12px;"><span class="am-badge am-badge-success">'.$t_ysh.'</span><span class="am-badge am-badge-warning">'.$t_wsh.'</span></td>
							</tr>';
			}
			break;

		case 'charge_list':
			$reply = pdo_fetchall('SELECT title, rid FROM ' . tablename($this -> table_reply) . ' WHERE ' . $where . ' ORDER BY `status` DESC, `end_time` DESC, `id` DESC');


			foreach ($reply as $key => $row) {

				//$t = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE rid = :rid", array(':rid' => $row['rid']));//报名
				$total = pdo_fetchcolumn("SELECT SUM(price) FROM ".tablename($this->table_order)." WHERE (status = 1 OR ispayvote =6) ORDER BY `id` DESC");
				$total_charge = pdo_fetchcolumn("SELECT SUM(price) FROM ".tablename($this->table_order)." WHERE rid = :rid AND (status = 1 OR ispayvote =6) ORDER BY `id` DESC", array(':rid' => $row['rid']));
				$total_charge = round($total_charge, 2);
				$total_gift = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_user_zsgift)." WHERE rid= :rid ", array(':rid' => $row['rid']));//礼物
				if ($total <= 0) {
					$bl = 100;//完成率
				}else{
					$bl = sprintf("%.0f",($total_charge/$total)*100);//完成率
				}


				if ($bl >= 100) {
					$style_bar = 'am-progress-bar-danger';
				}else{
					$style_bar = 'am-progress-bar-warning';
				}
				$html .= '<tr>
								<td class="am-text-truncate">
									<a class="user-name" href="'.$this->createWebUrl('members', array('rid' => $row['rid'])).'" target="_blank">'.cutstr($row['title'], '8', '...') .'</a>
								</td>
								<td>
		                            	<span class="progress">
		                    				<div class="am-progress tpl-progress am-progress-striped am-active"><div class="am-progress-bar '.$style_bar.'" style="width:'.$bl.'%">'.$bl.'%</div></div>
		                				</span>
								</td>
								<td style="font-size: 12px;"><span class="am-badge am-badge-danger">'.$total_charge.'</span><span class="am-badge am-badge-warning">'.$total_gift.'</span></td>
							</tr>';


			}

			break;
		default:

				break;
		}

		$data = array(
			'success' => 1,
			'html' => $html,
			'msg' => '成功！'
		);
		echo json_encode($data);
		exit ;
	}
	public function doWebGetLocaltion() {
		//global $_GPC,$_W;
		//$uniacid = $_W['uniacid'];
		$earth = json_encode($this->getlocal('1'));
		echo $earth;
		exit ;

	}

	public function doWebGetShebei() {
		//global $_GPC,$_W;
		//$uniacid = $_W['uniacid'];
		$shebei = json_encode($this->getshebei());
		echo $shebei;
		exit ;

	}
	public function doWebGetAlldatab() {

		global $_GPC,$_W;
		$uniacid = $_W['uniacid'];
		$time = date('Y-m-d', time());
		$namearr = array(
					array('name' => '报名', 'value' => $this->getshuju('1')),
					//array('name' => '参与人次', 'value' => $this->getshuju('2')),
					array('name' => '点击量', 'value' => $this->getshuju('3')),
					array('name' => '充值', 'value' => $this->getshuju('4')),
					array('name' => '礼物数', 'value' => $this->getshuju('5')),
					array('name' => '投票数', 'value' => $this->getshuju('6')),
					//array('name' => '人气数', 'value' => $this->getshuju('7')),
					//array('name' => '点赞数', 'value' => $this->getshuju('8')),
					//array('name' => '分享数', 'value' => $this->getshuju('9')),
					array('name' => '粉丝数', 'value' => $this->getshuju('10'))
				);
		$tname = array($namearr[0]['name'],$namearr[1]['name'],$namearr[2]['name'],$namearr[3]['name'],$namearr[4]['name'],$namearr[5]['name']);

		$list = pdo_fetchall('SELECT title,rid FROM '.tablename($this->table_reply).' WHERE uniacid = '.$uniacid.' AND status = 1 ORDER BY `status` DESC, `end_time` DESC, `id` DESC LIMIT 6');
		$d = array();
		$db = array();
		$t = '';

		foreach ($namearr as $key => $row) {
			foreach ($list as $key1 => $value) {
				if ($key == '0') {
					$val = $this->getshuju('1', $value['rid'],'2');
				//}elseif ($key == '1') {
				//	$val = $this->getshuju('2', $value['rid'],'2');
				}elseif ($key == '1') {
					$val = $this->getshuju('3', $value['rid'],'2');
				}elseif ($key == '2') {
					$val = $this->getshuju('4', $value['rid'],'2');
				}elseif ($key == '3') {
					$val = $this->getshuju('5', $value['rid'],'2');
				}elseif ($key == '4') {
					$val = $this->getshuju('6', $value['rid'],'2');
				//}elseif ($key == '4') {
				//	$val = $this->getshuju('7', $value['rid'],'2');
				//}elseif ($key == '5') {
				//	$val = $this->getshuju('8', $value['rid'],'2');
				//}elseif ($key == '6') {
				//	$val = $this->getshuju('9', $value['rid'],'2');
				}elseif ($key == '5') {
					$val = $this->getshuju('10', $value['rid'],'2');
				}
				$d[$key1] = $val;
				$t[$key1] = $value['title'];

			}

			$tt[] = array($t[0],$t[1],$t[2],$t[3],$t[4],$t[5]);
			$da[] = array($d[0],$d[1],$d[2],$d[3],$d[4],$d[5]);
			$title = $tt[$key];
			$db[] = array('value' => $da[$key]);

		}
		$r[] = array('tname' => $tname,'title' => $title, 'value' => $db);
		echo json_encode($r);
		exit ;
	}


	//数据汇总
	public function doWebCalldata() {
		global $_GPC,$_W;
		$uniacid = $_W['uniacid'];
		$time = date('Y-m-d', time());
		$namearr = array(
					array('name' => '报名'),
					array('name' => '参与人次'),
					array('name' => '点击量'),
					array('name' => '充值'),
					array('name' => '礼物数'),
					array('name' => '投票数'),
					array('name' => '人气数'),
					array('name' => '点赞数'),
					array('name' => '分享数'),
					array('name' => '粉丝数')
				);
		//$tname = array($namearr[0]['name'],$namearr[1]['name'],$namearr[2]['name'],$namearr[3]['name'],$namearr[4]['name'],$namearr[5]['name']);
		$rid = intval($_GPC['rid']);
		$type = $_GPC['type'];
		if ($type == 'all') {
			$lnum = '9';
		}else{
			$lnum = empty($_GPC['limit']) ? '6' : intval($_GPC['limit']) ;
		}
		$where = '';

		$limit = '  LIMIT ' . $lnum;
		//print_r($rid);exit;
		if (!empty($rid)) {
			$where .= ' AND rid = ' . $rid;
			$limit = '';
			$list = pdo_fetch('SELECT title,rid FROM '.tablename($this->table_reply).' WHERE uniacid = '.$uniacid.' AND status = 1 ' . $where . ' ');
			$linetype = 'bar';

		}else{
			$list = pdo_fetchall('SELECT title,rid FROM '.tablename($this->table_reply).' WHERE uniacid = '.$uniacid.' AND status = 1 ' . $where . ' ORDER BY `status` DESC, `end_time` DESC, `id` DESC' . $limit);
			$linetype = 'line';
		}

		$d = array();
		$db = '[';
		$t = '';
		if (!empty($rid)) {
			$ta = '';
			$ta .= '[';
			//foreach ($list as $key => $value) {
				$ta .= '"' .$list['title'] . '",';
			//}
			$ta .= ']';
		}else{
			$ta = '';
			$ta .= '[';
			foreach ($list as $key => $value) {
				$ta .= '"' .$value['title'] . '",';
			}
			$ta .= ']';
		}
		$sumlist = count($list);
		$sumname = count($namearr);
		$tname .= '[';
		foreach ($namearr as $key => $row) {

			if (!empty($rid)) {
				$da = '[';
				if ($key == '0') {
					$val = $this->getshuju('1', $rid,'2');
				}elseif ($key == '1') {
					$val = $this->getshuju('2', $rid,'2');
				}elseif ($key == '2') {
					$val = $this->getshuju('3', $rid,'2');
				}elseif ($key == '3') {
					$val = $this->getshuju('4', $rid,'2');
				}elseif ($key == '4') {
					$val = $this->getshuju('5', $rid,'2');
				}elseif ($key == '5') {
					$val = $this->getshuju('6', $rid,'2');
				}elseif ($key == '6') {
					$val = $this->getshuju('7', $rid,'2');
				}elseif ($key == '7') {
					$val = $this->getshuju('8', $rid,'2');
				}elseif ($key == '8') {
					$val = $this->getshuju('9', $rid,'2');
				}elseif ($key == '9') {
					$val = $this->getshuju('10', $rid,'2');
				}
				$da .= '"' .$val . '"';
				$da .= ']';
			}else{
				$da = '[';
				foreach ($list as $key1 => $value) {
					$rida = $value['rid'];
					if ($key == '0') {
						$val = $this->getshuju('1', $rida,'2');
					}elseif ($key == '1') {
						$val = $this->getshuju('2', $rida,'2');
					}elseif ($key == '2') {
						$val = $this->getshuju('3', $rida,'2');
					}elseif ($key == '3') {
						$val = $this->getshuju('4', $rida,'2');
					}elseif ($key == '4') {
						$val = $this->getshuju('5', $rida,'2');
					}elseif ($key == '5') {
						$val = $this->getshuju('6', $rida,'2');
					}elseif ($key == '6') {
						$val = $this->getshuju('7', $rida,'2');
					}elseif ($key == '7') {
						$val = $this->getshuju('8', $rida,'2');
					}elseif ($key == '8') {
						$val = $this->getshuju('9', $rida,'2');
					}elseif ($key == '9') {
						$val = $this->getshuju('10', $rida,'2');
					}
					if ($key1+1 == $sumlist) {
						$da .= '"' .$val . '"';
					}else{
						$da .= '"' .$val . '",';
					}

				}
				$da .= ']';
			}
			if ($key+1 == $sumname) {
				$db .= '{"value":' . $da . '}';
			}else{
				$db .= '{"value":' . $da . '}, ';
			}
			$tname .= '"' .$row['name'] . '",';

		}
		$db .= ']';
		$tname .= ']';



		$r[] = array('tname' => $tname,'title' => $ta, 'value' => $db, 'linetype' => $linetype);
		echo json_encode($r);
		exit ;
	}

	public function doWebCallshebei() {
		global $_GPC,$_W;
		$uniacid = intval($_W['uniacid']);
		$rid = intval($_GPC['rid']);
		//print_r($rid);
		$where = ' `uniacid` = ' . $uniacid;
		if (!empty($rid)) {
			$where .= ' AND `rid` = ' . $rid;
		}
		$votelogs = pdo_fetchall('SELECT mobile_info FROM ' . tablename($this -> table_log) . ' WHERE ' . $where . ' AND mobile_info <> ""');

		$data = array();
		//$num = 0;
		foreach ($votelogs as $key => $value) {
			$row = explode(";",$value['mobile_info']);
			if ($row['0'] == 'Linux') {
					$mobile = explode("/",$row['2']);
					$mobile = $mobile['0'];
				$mobile = str_replace(" Build","",$mobile);
				$mobile = str_replace(" ","",$mobile);
				//$data['Android'] ++;
				//$num[$mobile] ++;
				$data[$mobile] ++;


			}elseif ($row['0'] == 'iPhone') {
				//$data['mobile'] .= 'apple';
				$apple = 'iPhone';
				$data[$apple] ++;
				//$numb ++;
				//$data['apple'] = 'apple|'.$numb;
			}else{
				$other = 'other';

				$data[$other] ++;
				//$numc ++;
				//$data['other'] = 'other|'.$numc;
			}
		}
		$dataa = '[';
		$cnum = count($data);
		$i = 0;
		//array_multisort(array_column($data,'value'),SORT_DESC,$data);
		arsort($data);
		foreach ($data as $key => $value) {
			if ($i == '15') {
				break;
			}
			if ($i+1 == $cnum) {
				$dataa .= '{"value":' . $value . ',"name":"' . $key.'"}';
			}else{
				$dataa .= '{"value":' . $value . ',"name":"' . $key.'"},';
			}
			$i ++ ;

		}
		$dataa .= ']';
		//print_r($dataa);
		if (!empty($data)) {
			echo json_encode($dataa);
			exit ;
		}else{
			echo '';
			exit ;
		}

	}

	public function doWebCallreg() {
		global $_GPC,$_W;
		$uniacid = intval($_W['uniacid']);
		$rid = intval($_GPC['rid']);
		$type = $_GPC['type'];
		//print_r($rid);
		$where = ' `uniacid` = ' . $uniacid;
		if (!empty($rid)) {

			$now = time();
			$starttime = empty($_GPC['start_time']) ?  strtotime(date("Y-m-d H:i", $now - 604799)) : strtotime($_GPC['start_time']);
			$endtime = empty($_GPC['end_time']) ?  strtotime(date("Y-m-d H:i", $now)) : strtotime($_GPC['end_time']);
			if ($type == 'yesterday') {
				$starttime = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
				$endtime = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
			}
			if ($type == 'week') {
				$starttime = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
			}
			if ($type == 'month') {
				$starttime = mktime(0,0,0,date('m'),1,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('t'),date('Y'));
			}
			$day = $this->getdaytotal($starttime, $endtime,$this->table_users,$rid);
			$total = array();
			$ysh = '[';
			$wsh = '[';
			$wcl = '[';
			$title = '[';

			foreach ($day as $key => $row) {
				if ($row['totals'] < 1) {
					$wcl .= '100,';
				}else{
					$wcl .= sprintf("%.0f",($row['totals_ysh']/$row['totals'])*100) . ',';
				}
				$title .= '"' . $row['day'] . '",';
				$ysh .= $row['totals_ysh'] . ',';
				$wsh .= ($row['totals'] - $row['totals_ysh']) . ',';
				//$t_wcl .= sprintf("%.0f",($row['totals_ysh']/$row['totals'])) . ',';
				$total[$key] = $row['totals'];
			}
			$ysh .= ']';
			$wsh .= ']';
			$wcl .= ']';
			$title .= ']';
			arsort($total);
			foreach ($total as $key => $value) {
				$totalmax[] = $value;
			}
			//print_r($day);

		}else{


			$reply = pdo_fetchall('SELECT title, rid FROM ' . tablename($this -> table_reply) . ' WHERE ' . $where . ' ORDER BY `status` DESC, `end_time` DESC, `id` DESC LIMIT 6');

			$total = array();
			$ysh = '[';
			$wsh = '[';
			$wcl = '[';
			$title = '[';
			foreach ($reply as $key => $row) {

				$t = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE rid = :rid", array(':rid' => $row['rid']));//报名



				$t_ysh = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE rid = :rid AND status = 1", array(':rid' => $row['rid']));//报名已审核
				$t_wsh = $t - $t_ysh;//报名已审核
				if ($t < 1) {
					$t_wcl = 100;//完成率
				}else{
					$t_wcl = sprintf("%.0f",($t_ysh/$t)*100);//完成率
				}

				$ysh .= $t_ysh . ',';
				$wsh .= $t_wsh . ',';
				$wcl .= $t_wcl . ',';
				$title .= '"' .$row['title'] . '",';

			}
			$ysh .= ']';
			$wsh .= ']';
			$wcl .= ']';
			$title .= ']';
		}
		$r[] = array('ysh' => $ysh, 'wsh' => $wsh, 'wcl' => $wcl, 'title' => $title);
		echo json_encode($r);
		exit ;

	}

	public function doWebCallcharge() {
		global $_GPC,$_W;
		$uniacid = intval($_W['uniacid']);
		$rid = intval($_GPC['rid']);
		$type = $_GPC['type'];
		//print_r($rid);
		$where = ' `uniacid` = ' . $uniacid;
		if (!empty($rid)) {
			$now = time();

			$starttime = empty($_GPC['start_time']) ?  strtotime(date("Y-m-d H:i", $now - 604799)) : strtotime($_GPC['start_time']);
			$endtime = empty($_GPC['end_time']) ?  strtotime(date("Y-m-d H:i", $now)) : strtotime($_GPC['end_time']);
			if ($type == 'yesterday') {
				$starttime = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
				$endtime = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
			}
			if ($type == 'week') {
				$starttime = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
			}
			if ($type == 'month') {
				$starttime = mktime(0,0,0,date('m'),1,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('t'),date('Y'));
			}

			$days = $this->getdaychargetotal($starttime, $endtime,$this->table_order,$this->table_user_zsgift,$rid);
			$charge = '[';
			$gift = '[';
			$title = '[';
			foreach ($days as $key => $row) {
				$charge .= $row['totals'] . ',';
				$gift .= $row['totals_gift'] . ',';
				$title .= '"' . $row['day'] . '",';

			}
			$charge .= ']';
			$gift .= ']';
			$title .= ']';
			$data = '[';
			$data .= $charge. ',' . $gift;
			$data .= ']';

		}else{


			$reply = pdo_fetchall('SELECT title, rid FROM ' . tablename($this -> table_reply) . ' WHERE ' . $where . ' ORDER BY `status` DESC, `end_time` DESC, `id` DESC LIMIT 7');

			$charge = '[';
			$gift = '[';
			$title = '[';
			foreach ($reply as $key => $row) {

				//$t = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE rid = :rid", array(':rid' => $row['rid']));//报名
				$total_charge = round(pdo_fetchcolumn("SELECT SUM(price) FROM ".tablename($this->table_order)." WHERE rid = :rid AND (status = 1 OR ispayvote =6) ORDER BY `id` DESC", array(':rid' => $row['rid'])), 2);
				$total_gift = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_user_zsgift)." WHERE rid= :rid ", array(':rid' => $row['rid']));//礼物
				$charge .= $total_charge . ',';
				$gift .= $total_gift . ',';
				$wcl .= $t_wcl . ',';
				$title .= '"' .$row['title'] . '",';

			}
			$charge .= ']';
			$gift .= ']';

			$data = '[';
			$data .= $charge. ',' . $gift;
			$data .= ']';
			$title .= ']';
		}
		$r[] = array('data' => $data, 'title' => $title);
		echo json_encode($r);
		exit ;

	}

	public function doWebCallvotes() {
		global $_GPC,$_W;
		$uniacid = intval($_W['uniacid']);
		$rid = intval($_GPC['rid']);
		$type = $_GPC['type'];
		//print_r($rid);
		$now = time();

		$where = ' `uniacid` = ' . $uniacid;
		$con = '';
		if($type == 'yesterday' || $type == 'week' || $type == 'month') {
			$starttime = empty($_GPC['start_time']) ?  strtotime(date("Y-m-d H:i", $now - 604799)) : strtotime($_GPC['start_time']);
			$endtime = empty($_GPC['end_time']) ?  strtotime(date("Y-m-d H:i", $now)) : strtotime($_GPC['end_time']);
			if ($type == 'yesterday') {
				$starttime = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
				$endtime = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
			}
			if ($type == 'week') {
				$starttime = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
			}
			if ($type == 'month') {
				$starttime = mktime(0,0,0,date('m'),1,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('t'),date('Y'));
			}
			$con .= ' AND createtime <'.$endtime;
			$con .= ' AND createtime >'.$starttime;
		}
		if (!empty($rid)) {
			$where .= ' AND `rid` = ' . $rid;
			$reply = pdo_fetch('SELECT iparr FROM ' . tablename($this -> table_reply) . ' WHERE ' . $where . ' AND iparr <> "" ORDER BY id DESC limit 1');

			$mainname = $reply['iparr'];
			if (empty($mainname)) {
				return '';
			}



			$votelogs = pdo_fetchall('SELECT iparr, vote_times FROM ' . tablename($this -> table_log) . ' WHERE ' . $where . $con . ' AND iparr <> ""');
			if (empty($votelogs)) {
				return '';
			}

			$value = '';
			foreach ($votelogs as $key => $row) {
				if ($row['iparr'] != '来自微信会话界面' && $row['iparr'] != '') {
					$iparr = iunserializer($row['iparr']);
					$name = $iparr['province'];


					if ($name == '') {
						$name = $mainname;
						$value[$name] += $row['vote_times'];
					}else{
						$value[$name] += $row['vote_times'];
					}
					//$mapdata .= '[{name: "'.$name.'",value: '.$value[$name].'}, {name: "北京"}],';


				}
			}
			$mapdata = '[';
			foreach ($value as $key => $row) {
				$mapdata .= '[{name: "'.$key.'",value: '.$row.'}, {name: "'.$mainname.'"}],';
			}
			$mapdata .= ']';


		}else{
			$reply = pdo_fetch('SELECT iparr FROM ' . tablename($this -> table_reply) . ' WHERE ' . $where . ' AND iparr <> "" ORDER BY id DESC limit 1');

			$mainname = $reply['iparr'];
			if (empty($mainname)) {
				return '';
			}
			$votelogs = pdo_fetchall('SELECT iparr, vote_times FROM ' . tablename($this -> table_log) . ' WHERE ' . $where . $con . ' AND iparr <> ""');
			if (empty($votelogs)) {
				return '';
			}
			$value = '';
			foreach ($votelogs as $key => $row) {
				if ($row['iparr'] != '来自微信会话界面' && $row['iparr'] != '') {
					$iparr = iunserializer($row['iparr']);
					$name = $iparr['province'];


					if ($name == '') {
						$name = $mainname;
						$value[$name] += $row['vote_times'];
					}else{
						$value[$name] += $row['vote_times'];
					}
					//$mapdata .= '[{name: "'.$name.'",value: '.$value[$name].'}, {name: "北京"}],';


				}
			}
			$mapdata = '[';
			foreach ($value as $key => $row) {
				$mapdata .= '[{name: "'.$key.'",value: '.$row.'}, {name: "'.$mainname.'"}],';
			}
			$mapdata .= ']';

		}
		//print_r($mapdata);

		$r[] = array('mapdata' => $mapdata,'mainname' => $mainname, 'maps' => getmaps());
		echo json_encode($r);
		exit ;
	}

	public function doWebCallcomment() {
		global $_GPC,$_W;
		$uniacid = intval($_W['uniacid']);
		$rid = intval($_GPC['rid']);
		$type = $_GPC['type'];
		//print_r($rid);
		$where = ' `uniacid` = ' . $uniacid;
		if (!empty($rid)) {
			$now = time();

			$starttime = empty($_GPC['start_time']) ?  strtotime(date("Y-m-d H:i", $now - 604799)) : strtotime($_GPC['start_time']);
			$endtime = empty($_GPC['end_time']) ?  strtotime(date("Y-m-d H:i", $now)) : strtotime($_GPC['end_time']);
			if ($type == 'yesterday') {
				$starttime = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
				$endtime = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
			}
			if ($type == 'week') {
				$starttime = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
			}
			if ($type == 'month') {
				$starttime = mktime(0,0,0,date('m'),1,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('t'),date('Y'));
			}

			$days = $this->getdaycommenttotal($starttime, $endtime,$this->table_bbsreply,$rid);
			$t_all = '[';
			$t_ws = '[';
			$t_zan = '[';
			$title = '[';
			foreach ($days as $key => $row) {
				$t_all .= $row['total_all'] . ',';
				$t_ws .= $row['total_ws'] . ',';
				$t_zan .= $row['total_zan'] . ',';
				$title .= '"' . $row['day'] . '",';

			}
			$t_all .= ']';
			$t_ws .= ']';
			$t_zan .= ']';
			$title .= ']';
			$data = '[';
			$data .= $t_all. ',' . $t_ws. ',' . $t_zan;
			$data .= ']';

		}else{


			$reply = pdo_fetchall('SELECT title, rid FROM ' . tablename($this -> table_reply) . ' WHERE ' . $where . ' ORDER BY `status` DESC, `end_time` DESC, `id` DESC LIMIT 7');

			$t_all = '[';
			$t_ws = '[';
			$t_zan = '[';
			$title = '[';
			foreach ($reply as $key => $row) {

				$all = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_bbsreply)." WHERE rid= :rid ", array(':rid' => $row['rid']));
				$ys = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_bbsreply)." WHERE status =1 AND rid= :rid ", array(':rid' => $row['rid']));
				$zan = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_bbsreply)." WHERE zan =1 AND rid= :rid ", array(':rid' => $row['rid']));
				$t_all .= $all . ',';
				$t_ws .= ($all - $ys - $zan) . ',';
				$t_zan .= $zan . ',';
				$title .= '"' .$row['title'] . '",';

			}
			$t_all .= ']';
			$t_ws .= ']';
			$t_zan .= ']';

			$data = '[';
			$data .= $t_all. ',' . $t_ws. ',' . $t_zan;
			$data .= ']';
			$title .= ']';
		}
		$r[] = array('data' => $data, 'title' => $title);
		echo json_encode($r);
		exit ;

	}
	public function doWebCallshare() {
		global $_GPC,$_W;
		$uniacid = intval($_W['uniacid']);
		$rid = intval($_GPC['rid']);
		$type = $_GPC['type'];
		//print_r($rid);
		$where = ' `uniacid` = ' . $uniacid;
		if (!empty($rid)) {
			$now = time();

			$starttime = empty($_GPC['start_time']) ?  strtotime(date("Y-m-d H:i", $now - 604799)) : strtotime($_GPC['start_time']);
			$endtime = empty($_GPC['end_time']) ?  strtotime(date("Y-m-d H:i", $now)) : strtotime($_GPC['end_time']);
			if ($type == 'yesterday') {
				$starttime = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
				$endtime = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
			}
			if ($type == 'week') {
				$starttime = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
			}
			if ($type == 'month') {
				$starttime = mktime(0,0,0,date('m'),1,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('t'),date('Y'));
			}

			$days = $this->getdaysharetotal($starttime, $endtime,$this->table_data,$rid);
			$t_all = '[';
			$title = '[';
			foreach ($days as $key => $row) {
				$t_all .= $row['total_all'] . ',';
				$title .= '"' . $row['day'] . '",';

			}
			$t_all .= ']';
			$title .= ']';
			$data = '[';
			$data .= $t_all;
			$data .= ']';

		}else{


			$reply = pdo_fetchall('SELECT title, rid FROM ' . tablename($this -> table_reply) . ' WHERE ' . $where . ' ORDER BY `status` DESC, `end_time` DESC, `id` DESC LIMIT 7');

			$t_all = '[';
			$t_ws = '[';
			$t_zan = '[';
			$title = '[';
			foreach ($reply as $key => $row) {

				$all = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_data)." WHERE rid= :rid ", array(':rid' => $row['rid']));
				$t_all .= $all . ',';
				$title .= '"' .$row['title'] . '",';

			}
			$t_all .= ']';

			$data = '[';
			$data .= $t_all;
			$data .= ']';
			$title .= ']';
		}
		$r[] = array('data' => $data, 'title' => $title);
		echo json_encode($r);
		exit ;

	}
	public function doWebCallfans() {
		global $_GPC,$_W;
		$uniacid = intval($_W['uniacid']);
		$rid = intval($_GPC['rid']);
		$type = $_GPC['type'];
		//print_r($rid);
		$where = ' `uniacid` = ' . $uniacid;

		$now = time();

		$con = '';
		if($type == 'yesterday' || $type == 'week' || $type == 'month') {
			$starttime = empty($_GPC['start_time']) ?  strtotime(date("Y-m-d H:i", $now - 604799)) : strtotime($_GPC['start_time']);
			$endtime = empty($_GPC['end_time']) ?  strtotime(date("Y-m-d H:i", $now)) : strtotime($_GPC['end_time']);
			if ($type == 'yesterday') {
				$starttime = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
				$endtime = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
			}
			if ($type == 'week') {
				$starttime = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
			}
			if ($type == 'month') {
				$starttime = mktime(0,0,0,date('m'),1,date('Y'));
				$endtime = mktime(23,59,59,date('m'),date('t'),date('Y'));
			}
			$con .= ' AND createtime <'.$endtime;
			$con .= ' AND createtime >'.$starttime;
		}
		if (!empty($rid)) {

			$days = $this->getdayfanstotal($starttime, $endtime,$this->table_voteer,$rid);
			$t_all = '[';
			$title = '[';
			foreach ($days as $key => $row) {
				$t_all .= $row['total_all'] . ',';
				$title .= '"' . $row['day'] . '",';

			}
			$t_all .= ']';
			$title .= ']';
			$data = '[';
			$data .= $t_all;
			$data .= ']';

		}else{


			$reply = pdo_fetchall('SELECT title, rid FROM ' . tablename($this -> table_reply) . ' WHERE ' . $where . ' ORDER BY `status` DESC, `end_time` DESC, `id` DESC LIMIT 7');

			$t_all = '[';
			$t_ws = '[';
			$t_zan = '[';
			$title = '[';
			foreach ($reply as $key => $row) {

				$all = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_voteer)." WHERE rid= :rid '.$con.' ", array(':rid' => $row['rid']));
				$t_all .= $all . ',';
				$title .= '"' .$row['title'] . '",';

			}
			$t_all .= ']';

			$data = '[';
			$data .= $t_all;
			$data .= ']';
			$title .= ']';
		}
		$r[] = array('data' => $data, 'title' => $title);
		echo json_encode($r);
		exit ;

	}
}
