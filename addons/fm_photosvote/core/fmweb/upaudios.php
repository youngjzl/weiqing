<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
$from_user = $_GPC['from_user'];//

		$reply = pdo_fetch('SELECT * FROM '.tablename($this->table_reply).' WHERE rid =:rid ', array(':rid' => $rid));
		$rvote = pdo_fetch('SELECT * FROM '.tablename($this->table_reply_vote).' WHERE rid =:rid ', array(':rid' => $rid));

		$dreply = pdo_fetch("SELECT csrs_total FROM ".tablename($this->table_reply_display)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));

		$qiniu = iunserializer($reply['qiniu']);

		load()->func('file');
		if(!empty($from_user)) {
			$mygift = pdo_fetch("SELECT * FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));
			$username = pdo_fetch("SELECT * FROM ".tablename($this->table_users_name)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));
		}
		$uid = pdo_fetch("SELECT uid FROM ".tablename($this->table_users)." WHERE rid = :rid ORDER BY uid DESC, id DESC LIMIT 1", array(':rid' => $rid));
		if (empty($mygift)) {
			$insertdata = array(
				'rid'       => $rid,
				'uid'       => $uid['uid'] + 1,
				'uniacid'      => $uniacid,
				'from_user' => $from_user,
				'avatar'    => $avatar,
				'nickname'  => $nickname,
				'sex'  => $sex,
				'photo'  => '',
				'description'  => '',
				'photoname'  => '',
				'realname'  => '',
				'mobile'  => '',
				'weixin'  => '',
				'qqhao'  => '',
				'email'  => '',
				'job'  => '',
				'xingqu'  => '',
				'address'  => '',
				'photosnum'  => '0',
				'xnphotosnum'  => '0',
				'hits'  => '1',
				'xnhits'  => '1',
				'yaoqingnum'  => '0',
				'createip' => getip(),
				'lastip' => getip(),
				'status'  => '2',
				'sharetime' => $now,
				'createtime'  => $now,
			);
			$insertdata['iparr'] = getiparr($insertdata['lastip']);

			pdo_insert($this->table_users, $insertdata);
			pdo_update($this->table_reply_display, array('csrs_total' => $dreply['csrs_total']+1), array('rid' => $rid));

		   if($reply['isfans']){
				if($myavatar){
					fans_update($from_user, array(
						'avatar' => $myavatar,
					));
				}
				if($mynickname){
					fans_update($from_user, array(
						'nickname' => $mynickname,
					));
				}

				if($reply['isrealname']){
					fans_update($from_user, array(
						'realname' => $realname,
					));
				}
				if($reply['ismobile']){
					fans_update($from_user, array(
						'mobile' => $mobile,
					));
				}
				if($reply['isqqhao']){
					fans_update($from_user, array(
						'qq' => $qqhao,
					));
				}
				if($reply['isemail']){
					fans_update($from_user, array(
						'email' => $email,
					));
				}
				if($reply['isaddress']){
					fans_update($from_user, array(
						'address' => $address,
					));
				}
			}
		}


		if ($_GPC['upaudios'] == 'start') {
			//var_dump($_FILES);
			$audiotype = $_GPC['audiotype'];
			$upmediatmp = $_FILES['files']["tmp_name"];
			if ($qiniu['videologo']) {
				$qiniu['videologo'] = toimage($qiniu['videologo']);
			}
			$mygift = pdo_fetch("SELECT from_user, avatar, vedio, voice, music  FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));

			if($upmediatmp){
				$ext = $_FILES['files']["type"];
				$nfilename = 'FM'.date('YmdHis').random(8).$_FILES['files']["name"];

				$updir = '../attachment/audios/'.$uniacid.'/'.date("Y").'/'.date("m").'/';
				mkdirs($updir);
				/**if ($mygift[$audiotype]) {
					file_delete($mygift[$audiotype]);
				}**/
				$music = fm_file_upload($_FILES['files'], $audiotype);
				if (!$music['success']) {
					$fmdata = array(
						"success" => $music['errno'],
						"msg" => $music['message'],
					);
					echo json_encode($fmdata);
					exit();
				}
				$mid = $_GPC['mid'];

				$photosarrnum = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_users_videoarr)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));
				$videopath = $music['path'];

				if ($audiotype !== 'vedio') {
					if ($qiniu['isqiniu']) {	//开启七牛存储

						$upmediatmp = $_W['siteroot'].'attachment/'.$videopath;
						$qiniuaudios = $this->fmqnaudios($nfilename, $qiniu, $upmediatmp, $audiotype, $username);

						$nfilenamefop = $qiniuaudios['nfilenamefop'];
						if ($qiniuaudios['success'] == '-1') {
						//	var_dump($err);
							$fmdata = array(
								"success" => -1,
								"msg" => $qiniuaudios['msg'],
							);
							echo json_encode($fmdata);
							exit();
						} else {
							$insertdata = array();

							if ($qiniuaudios['success'] == '-2') {
								//var_dump($err);
								$fmdata = array(
										"success" => -1,
										"msg" => $err,
									);
									echo json_encode($fmdata);
									exit();
							} else {
								$insertdata[$audiotype] = $qiniuaudios[$audiotype];
								pdo_update($this->table_users, $insertdata, array('from_user'=>$from_user, 'rid' => $rid));
								if ($username) {
									$insertdataname = array();
									$insertdataname[$audiotype.'name'] = $nfilename;
									$insertdataname[$audiotype.'namefop'] = $nfilenamefop;
									pdo_update($this->table_users_name, $insertdataname, array('from_user'=>$from_user, 'rid' => $rid));
								}else {
									$insertdataname = array(
										'rid'       => $rid,
										'uniacid'      => $uniacid,
										'from_user' => $from_user,
									);
									$insertdataname[$audiotype.'name'] = $nfilename;
									$insertdataname[$audiotype.'namefop'] = $nfilenamefop;
									pdo_insert($this->table_users_name, $insertdataname);
								}
								$fmimage = $this->getpicarr($uniacid,$rid, $mygift['from_user'],1);
								$pimage = $this->getphotos($fmimage['photos'], $mygift['avatar'], $rbasic['picture']);
								$fmdata = array(
									"success" => 1,
									"pimage" => $pimage,
									"imgurl" => $insertdata[$audiotype],
									"msg" => '上传成功！',

								);
								echo json_encode($fmdata);
								exit();

							}
						}
					}else {
						$insertdata = array();
						$insertdata[$audiotype] = $music['path'];

						pdo_update($this->table_users, $insertdata, array('from_user'=>$from_user, 'rid' => $rid));
						$fmimage = $this->getpicarr($uniacid,$rid, $mygift['from_user'],1);
						$pimage = $this->getphotos($fmimage['photos'], $mygift['avatar'], $rbasic['picture']);
						$fmdata = array(
							"success" => 1,
							"pimage" => $pimage,
							"imgurl" => $insertdata[$audiotype],
							"msg" => '上传成功！',
						);
						echo json_encode($fmdata);
						exit();
					}

				}else{
					if ($qiniu['isqiniu']) {	//开启七牛存储

						$upmediatmp = $_W['siteroot'].'attachment/'.$videopath;
						$qiniuaudios = $this->fmqnaudios($nfilename, $qiniu, $upmediatmp, $audiotype, $username);

						$nfilenamefop = $qiniuaudios['nfilenamefop'];
						if ($qiniuaudios['success'] == '-1') {
						//	var_dump($err);
							$fmdata = array(
								"success" => -1,
								"msg" => $qiniuaudios['msg'],
							);
							echo json_encode($fmdata);
							exit();
						} else {
							$insertdata = array();

							if ($qiniuaudios['success'] == '-2') {
								//var_dump($err);
								$fmdata = array(
										"success" => -1,
										"msg" => $err,
									);
									echo json_encode($fmdata);
									exit();
							} else {
								$videourl = '../attachment/'.$music['path'];

								if ($photosarrnum >= $rvote['tpxz_video']) {
									$fmdata = array(
										"success" => -1,
										"msg" => '抱歉，你只能上传 '.$rvote['tpxz_video'].' 个视频。',
									);
									echo json_encode($fmdata);
									exit;
								}
								$insertdata = array(
									'rid'       => $rid,
									'uniacid'      => $uniacid,
									'from_user' => $from_user,
									'videoname' => $music['fname'],
									'videonamefop' => $nfilenamefop,
									'status' => 1,
									'createtime' => $now,
									'videopath' => $qiniuaudios[$audiotype],
								);

								$insertdata['video'] = $qiniuaudios[$audiotype];
								pdo_insert($this->table_users_videoarr, $insertdata);
								$lastmid = pdo_insertid();
								pdo_update($this->table_users_videoarr, array('mid' => $lastmid), array('rid' => $rid,'from_user' => $from_user, 'id'=>$lastmid));


							$addlastmid = $lastmid + 1;
							$photosarrnum = $photosarrnum + 1;

							$fmdata = array(
								"success" => 1,
								"lastmid" => $lastmid,
								"addlastmid" => $addlastmid,
								"photosarrnum" => $photosarrnum,
								"msg" => '上传成功！',
								"imgurl" => $qiniuaudios[$audiotype],
							);



								if ($username) {
									$insertdataname = array();
									$insertdataname[$audiotype.'name'] = $nfilename;
									$insertdataname[$audiotype.'namefop'] = $nfilenamefop;
									pdo_update($this->table_users_name, $insertdataname, array('from_user'=>$from_user, 'rid' => $rid));
								}else {
									$insertdataname = array(
										'rid'       => $rid,
										'uniacid'      => $uniacid,
										'from_user' => $from_user,
									);
									$insertdataname[$audiotype.'name'] = $nfilename;
									$insertdataname[$audiotype.'namefop'] = $nfilenamefop;
									pdo_insert($this->table_users_name, $insertdataname);
								}
								$fmimage = $this->getpicarr($uniacid,$rid, $mygift['from_user'],1);
								$pimage = $this->getphotos($fmimage['photos'], $mygift['avatar'], $rbasic['picture']);
								$fmdata = array(
									"success" => 1,
									"pimage" => $pimage,
									"imgurl" => $insertdata[$audiotype],
									"msg" => '上传成功！',

								);
								echo json_encode($fmdata);
								exit();

							}
						}
					}else {
						$insertdata = array();
						$insertdata[$audiotype] = $music['path'];

						$videourl = '../attachment/'.$music['path'];

								if ($photosarrnum >= $rvote['tpxz_video']) {
									$fmdata = array(
										"success" => -1,
										"msg" => '抱歉，你只能上传 '.$rvote['tpxz_video'].' 个视频。',
									);
									echo json_encode($fmdata);
									exit;
								}
								$insertdata = array(
									'rid'       => $rid,
									'uniacid'      => $uniacid,
									'from_user' => $from_user,
									'videoname' => $music['fname'],
									'status' => 1,
									'createtime' => $now,
									'videopath' => $videourl,
								);

								$insertdata['video'] = $videourl;
								pdo_insert($this->table_users_videoarr, $insertdata);
								$lastmid = pdo_insertid();
								pdo_update($this->table_users_videoarr, array('mid' => $lastmid), array('rid' => $rid,'from_user' => $from_user, 'id'=>$lastmid));


							$addlastmid = $lastmid + 1;
							$photosarrnum = $photosarrnum + 1;

							$fmdata = array(
								"success" => 1,
								"lastmid" => $lastmid,
								"addlastmid" => $addlastmid,
								"photosarrnum" => $photosarrnum,
								"msg" => '上传成功！',
								"imgurl" => $videourl,
							);
							echo json_encode($fmdata);
							exit();
					}
				}
			}else{
				if ($_GPC[$audiotype] && stristr($username[$audiotype.'namefop'],$_GPC[$audiotype])) {
					if ($qiniu['isqiniu']) {	//开启七牛存储

						$upurl = $_GPC[$audiotype];
						$qiniuaudios = $this->fmqnaudios($nfilename, $qiniu, $upurl,$audiotype, $username);
						$nfilenamefop = $qiniuaudios['nfilenamefop'];
						if ($qiniuaudios['success'] == '-1') {
							//	var_dump($err);
								$fmdata = array(
									"success" => -1,
									"msg" => $qiniuaudios['msg'],
								);
								echo json_encode($fmdata);
								exit();
							} else {
								if ($qiniuaudios['success'] == '-2') {
									//var_dump($err);
									$fmdata = array(
										"success" => -1,
										"msg" => $err,
									);
									echo json_encode($fmdata);
									exit();
								} else {
									//var_dump($ret);
									$insertdata[$audiotype] = $qiniuaudios[$audiotype];
									pdo_update($this->table_users, $insertdata, array('from_user'=>$from_user, 'rid' => $rid));
									if ($username) {
										$insertdataname = array();
										$insertdataname[$audiotype.'name'] = $nfilename;
										$insertdataname[$audiotype.'namefop'] = $nfilenamefop;
										pdo_update($this->table_users_name, $insertdataname, array('from_user'=>$from_user, 'rid' => $rid));
									}else {
										$insertdataname = array(
											'rid'       => $rid,
											'uniacid'      => $uniacid,
											'from_user' => $from_user,
										);
										$insertdataname[$audiotype.'name'] = $nfilename;
										$insertdataname[$audiotype.'namefop'] = $nfilenamefop;
										pdo_insert($this->table_users_name, $insertdataname);
									}

									$fmimage = $this->getpicarr($uniacid,$rid, $mygift['from_user'],1);
									$pimage = $this->getphotos($fmimage['photos'], $mygift['avatar'], $reply['picture']);
									$fmdata = array(
										"success" => 1,
										"pimage" => $pimage,
										"imgurl" => $insertdata[$audiotype],
										"msg" => '上传成功！',
									);
									echo json_encode($fmdata);
									exit();

								}
							}
					}else {
						$insertdata = array();
						$insertdata[$audiotype] = $_GPC[$audiotype];
						pdo_update($this->table_users, $insertdata, array('from_user'=>$from_user, 'rid' => $rid));
						$fmimage = $this->getpicarr($uniacid,$rid, $mygift['from_user'],1);
						$pimage = $this->getphotos($fmimage['photos'], $mygift['avatar'], $reply['picture']);
						$fmdata = array(
							"success" => 1,
							"pimage" => $pimage,
							"imgurl" => $_GPC[$audiotype],
							"msg" => '上传成功！',
						);
						echo json_encode($fmdata);
						exit();
					}




				}else {
					if ($audiotype == 'music') {
						$msg = '请上传音频或者填写远程音频地址';
					}elseif ($audiotype == 'vedio') {
						$msg = '请上传视频或者填写远程视频地址';
					}

					$fmdata = array(
						"success" => -1,
						"msg" => $msg,
					);
					echo json_encode($fmdata);
					die;
				}
			}
		}


