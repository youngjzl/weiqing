<?php
 goto AzuPG; RBqN3: define('POSETERH_ROOT', IA_ROOT . '/addons/zofui_posterhelp/'); goto phxA5; phxA5: define('POSETERH_URL', $_W['siteroot'] . '/addons/zofui_posterhelp/'); goto kBrEQ; AzuPG: global $_W; goto Vop0M; kBrEQ: define('MODULE', 'zofui_posterhelp'); goto ccNla; ccNla: require_once POSETERH_ROOT . 'class/autoload.php'; goto QqmAP; Vop0M: defined('IN_IA') or exit('Access Denied'); goto RBqN3; QqmAP: class Zofui_posterhelpModule extends WeModule { public function settingsDisplay($settings) { goto ZXuyY; ZZ1mf: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_helplist') . ' ADD INDEX helper(`helper`);'); goto jBIWB; Pww37: k2SRX: goto dL5aW; E1ydy: wzw9G: goto itgxQ; fKHNE: G7aCu: goto EU8J7; Lijth: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `prizestart` int(11) unsigned NOT NULL DEFAULT \'0\';'); goto L8LQe; reato: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `przieslider` varchar(3000) DEFAULT NULL COMMENT \'兑换奖品页面轮播图\';'); goto sfA2A; D13Fh: Util::rmdirs(POSETERH_ROOT . 'poster/' . $_W['uniacid'] . '/'); goto RtHxl; yamss: l6z9f: goto dTGIf; wcBBR: $rule = pdo_get('rule', array("uniacid" => $_W['uniacid'], "name" => "处理帮助关键字", "module" => MODULE)); goto R7kac; IdNH1: if (empty($_GPC['key'])) { goto Vt_3L; } goto ow9nd; FN8mX: if (pdo_fieldexists('zofui_posterhelp_act', 'przieslider')) { goto HdmJi; } goto reato; i0qoA: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_user') . ' ADD INDEX status(`status`);'); goto EghSI; ppM8Q: qzoPo: goto IdNH1; MuHsi: Yjpq9: goto C8Qq3; wG8hK: message('保存成功', 'refresh'); goto zyDX6; qAoHV: if (pdo_fieldexists('zofui_posterhelp_act', 'jftype')) { goto G7aCu; } goto qCuiA; E_ZDZ: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_qrcode') . ' ADD INDEX openid(`openid`);'); goto laUob; N5ygA: cgND2: goto FxA62; OA3nj: if (pdo_fieldexists('zofui_posterhelp_act', 'helparr')) { goto ID_Km; } goto ft_vt; mkElk: if (pdo_indexexists('zofui_posterhelp_auth', 'authopenid')) { goto wzw9G; } goto KjT9s; mGxJV: if (pdo_indexexists('zofui_posterhelp_user', 'code')) { goto GN3LY; } goto Hbn7v; coUxo: if (pdo_fieldexists('zofui_posterhelp_act', 'isminus')) { goto k09Yg; } goto FMVbH; h8xAd: if (pdo_indexexists('zofui_posterhelp_qrcode', 'qrcodeid')) { goto hTPRJ; } goto FSusE; D7qF1: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `prizerule` text;'); goto N5ygA; a6tRc: if (pdo_fieldexists('zofui_posterhelp_act', 'linkmess')) { goto J2ENI; } goto HtVN4; itG1J: if (!($_W['account']['level'] == 3)) { goto TNYZj; } goto qBOZ3; C8Qq3: if (pdo_indexexists('zofui_posterhelp_helplist', 'helper')) { goto ZYsPF; } goto ZZ1mf; EghSI: CuQZ5: goto W_J_P; wc3T5: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `islinkmess` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0不发，1发\';'); goto kguw1; EqcqH: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_auth') . ' ADD INDEX uniacid(`uniacid`);'); goto pndw3; X7gov: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_helplist') . ' ADD `isminus` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0未减 1已减\';'); goto T5p9s; IIUxk: z54lh: goto QjWiv; W_J_P: if (pdo_indexexists('zofui_posterhelp_user', 'openid')) { goto k2SRX; } goto egRW3; qU10i: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_user') . ' ADD INDEX actid(`actid`);'); goto KIg0p; DBRaA: if (pdo_indexexists('zofui_posterhelp_act', 'end')) { goto RrqBT; } goto ekYn_; V7hxT: include $this->template('web/setting'); goto Vy_sJ; QjWiv: if (pdo_indexexists('zofui_posterhelp_qrcode', 'openid')) { goto Caoef; } goto E_ZDZ; qCuiA: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `jftype` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0自带积分 1微擎积分\';'); goto fKHNE; FSusE: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_qrcode') . ' ADD INDEX qrcodeid(`qrcodeid`);'); goto koHSa; NI7L3: J9VVs: goto TgnZt; nxUPo: load()->func('file'); goto imsad; Wd_NH: if (pdo_fieldexists('zofui_posterhelp_geted', 'waitpay')) { goto B0jIg; } goto jQxgR; yrJJB: if (!($key['content'] != $mykey)) { goto Nzzkk; } goto jFN1K; MBYn3: $mykey = '^' . $_GPC['head'] . '[0-9]'; goto oy0tV; hKcvY: if (pdo_fieldexists('zofui_posterhelp_prize', 'isdetail')) { goto Hx_fK; } goto sDLY0; dtWWl: $r = $r && $ret; goto ppM8Q; xhzBQ: kO2mo: goto VeUTB; RtHxl: Nzzkk: goto dYUn8; oy0tV: sjzOI: goto wcBBR; YPMtI: pdo_query('CREATE TABLE IF NOT EXISTS ' . tablename('zofui_posterhelp_invite') . ' (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `uniacid` int(11) unsigned NOT NULL DEFAULT \'0\',
		  `unionid` varchar(64) DEFAULT NULL,
		  `status` tinyint(3) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0进行中 1已发奖\',
		  `uid` int(11) unsigned NOT NULL DEFAULT \'0\',
		  `endtime` int(11) unsigned NOT NULL DEFAULT \'0\' COMMENT \'有效期结束时间\',
		  `actid` int(11) unsigned NOT NULL DEFAULT \'0\',
		  PRIMARY KEY (`id`),
		  KEY `uniacid` (`uniacid`),
		  KEY `unionid` (`unionid`),
		  KEY `endtime` (`endtime`),
		  KEY `actid` (`actid`),
		  KEY `uid` (`uid`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;'); goto Cckfh; dp2qz: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_user') . ' ADD `unionid` varchar(64) DEFAULT NULL COMMENT \'链接\';'); goto oTunc; ZXuyY: global $_W, $_GPC; goto coUxo; ff_uk: KS2KM: goto v95Cz; MqTz5: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_auth') . ' ADD INDEX openid(`openid`);'); goto wy0Cp; UpR3f: RKkHd: goto uGDWB; jmSMq: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_helplist') . ' ADD INDEX helped(`helped`);'); goto NI7L3; Lb73Z: $ret = file_put_contents(POSETERH_ROOT . 'cert/' . $_W['uniacid'] . '/apiclient_cert.pem', trim($_GPC['cert'])); goto dtWWl; RqC7k: J0xqc: goto qAoHV; pndw3: EUafK: goto R0V9t; Ri_ie: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_auth') . ' ADD INDEX actid(`actid`);'); goto McOi0; XqwN0: if (pdo_fieldexists('zofui_posterhelp_prize', 'number')) { goto dPrz0; } goto KpEaZ; ikhaM: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_qrcode') . ' ADD INDEX sence(`sence`);'); goto MRZ3T; V2DS3: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_user') . ' ADD `issendlink` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0未发link，1已发\';'); goto r19fy; KjT9s: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_auth') . ' ADD INDEX authopenid(`authopenid`);'); goto E1ydy; mCzuy: if (pdo_indexexists('zofui_posterhelp_helplist', 'helped')) { goto J9VVs; } goto jmSMq; CQ0cD: if (pdo_indexexists('zofui_posterhelp_qrcode', 'sence')) { goto DFKC3; } goto ikhaM; SG2OM: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_prize') . ' ADD  `tips` varchar(555) DEFAULT NULL;'); goto ff_uk; TgnZt: if (pdo_indexexists('zofui_posterhelp_helplist', 'actid')) { goto z54lh; } goto qKbno; VeUTB: if ($r) { goto kQazv; } goto i6q2e; Hd0Ku: xp90i: goto DBRaA; EU8J7: if (pdo_fieldexists('zofui_posterhelp_prize', 'tips')) { goto KS2KM; } goto SG2OM; imsad: $r = mkdirs(POSETERH_ROOT . 'cert/' . $_W['uniacid']); goto Zsmtu; RaOhl: if (pdo_fieldexists('zofui_posterhelp_act', 'prizeend')) { goto J87SF; } goto zBslp; dTGIf: if (pdo_fieldexists('zofui_posterhelp_act', 'prizerule')) { goto cgND2; } goto D7qF1; BZjMT: $r = $r && $ret; goto f2Cp7; Cckfh: if (pdo_fieldexists('zofui_posterhelp_user', 'unionid')) { goto xjINd; } goto dp2qz; SZRLp: RrqBT: goto ivQL8; hfkUR: $mykey = '[0-9]'; goto tZTNb; Dw9kE: if (!$this->saveSettings($dat)) { goto JbQVX; } goto wG8hK; CRCFR: if (pdo_fieldexists('zofui_posterhelp_act', 'linklink')) { goto D3VVy; } goto xWlB4; jQxgR: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_geted') . ' ADD `waitpay` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0已支付  1未支付\';'); goto X68qy; qKbno: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_helplist') . ' ADD INDEX actid(`actid`);'); goto IIUxk; jBIWB: ZYsPF: goto mCzuy; zBslp: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `prizeend` int(11) unsigned NOT NULL DEFAULT \'0\';'); goto vAnHW; K1tSq: dPrz0: goto YPMtI; RgRbj: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `prizelim` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0不限制兑奖 1限制兑奖时间\';'); goto tIV_t; crbnu: J2ENI: goto CRCFR; Jjpr2: D3VVy: goto tJxlK; ow9nd: $ret = file_put_contents(POSETERH_ROOT . 'cert/' . $_W['uniacid'] . '/apiclient_key.pem', trim($_GPC['key'])); goto BZjMT; GY6M3: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_qrcode') . ' ADD INDEX actid(`actid`);'); goto t700W; P_p8d: if (pdo_indexexists('zofui_posterhelp_user', 'isstart')) { goto gl0KB; } goto v60NF; WYVuR: if (pdo_indexexists('zofui_posterhelp_act', 'start')) { goto xp90i; } goto GvSMd; McOi0: L6k6X: goto mkElk; X68qy: B0jIg: goto FN8mX; HtVN4: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `linkmess` varchar(3000) DEFAULT NULL COMMENT \'消息内容\';'); goto crbnu; LYori: if (!checksubmit('submit')) { goto d6t97; } goto q_xA3; MYcio: C2TUu: goto SJ6lo; tJxlK: if (pdo_fieldexists('zofui_posterhelp_user', 'issendlink')) { goto W_dZd; } goto V2DS3; urzZd: if (pdo_fieldexists('zofui_posterhelp_act', 'islink')) { goto a4M5Q; } goto izLB_; GvSMd: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD INDEX start(`start`);'); goto Hd0Ku; KIg0p: MTo8l: goto mGxJV; r3Xz4: if (pdo_indexexists('zofui_posterhelp_geted', 'prizeid')) { goto Yjpq9; } goto eGSj4; egRW3: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_user') . ' ADD INDEX openid(`openid`);'); goto Pww37; TPhzI: if (pdo_fieldexists('zofui_posterhelp_helplist', 'isminus')) { goto qU806; } goto X7gov; eGSj4: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_geted') . ' ADD INDEX prizeid(`prizeid`);'); goto MuHsi; xWlB4: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `linklink` varchar(500) DEFAULT NULL COMMENT \'链接\';'); goto Jjpr2; SjLqG: if (pdo_fieldexists('zofui_posterhelp_act', 'indextype')) { goto l6z9f; } goto JOKeh; laUob: Caoef: goto idm5W; L84MD: message('助力编号前缀不能是数字'); goto XNL8t; gCFyD: a4M5Q: goto ykWs0; FRSXe: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `linkleast` int(11) unsigned NOT NULL DEFAULT \'0\' COMMENT \'发链接消息的最小值\';'); goto hSJxW; POYtP: if (pdo_fieldexists('zofui_posterhelp_act', 'prizelim')) { goto Yitvp; } goto RgRbj; KpEaZ: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_prize') . ' ADD `number` int(11) unsigned NOT NULL DEFAULT \'0\' COMMENT \'越大越前\';'); goto K1tSq; byFoE: ID_Km: goto POYtP; MF7XQ: k09Yg: goto TPhzI; ykWs0: if (pdo_fieldexists('zofui_posterhelp_act', 'linkleast')) { goto HXjVe; } goto FRSXe; q_xA3: $_GPC = Util::trimWithArray($_GPC); goto nxUPo; oTunc: xjINd: goto cKDz7; hSJxW: HXjVe: goto a6tRc; SDztD: pdo_query('CREATE TABLE IF NOT EXISTS ' . tablename('zofui_posterhelp_uu') . ' (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `uniacid` int(11) unsigned NOT NULL DEFAULT \'0\',
		  `openid` varchar(64) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `uniacid` (`uniacid`),
		  KEY `openid` (`openid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;'); goto OA3nj; uGDWB: if (pdo_fieldexists('zofui_posterhelp_act', 'isverifyh')) { goto S3P1m; } goto sVY26; PaOzz: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_user') . ' ADD INDEX unionid(`unionid`);'); goto RqC7k; hirT3: if (pdo_indexexists('zofui_posterhelp_user', 'unionid')) { goto J0xqc; } goto PaOzz; T5p9s: qU806: goto LYori; HM0b2: if (!is_numeric($_GPC['head'])) { goto sX5Ss; } goto L84MD; R7kac: $key = pdo_get('rule_keyword', array("uniacid" => $_W['uniacid'], "rid" => $rule['id'])); goto yrJJB; kjjiU: TNYZj: goto WYVuR; MUl9Q: yRJnV: goto MBYn3; agDTB: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_prize') . ' ADD `detail` mediumtext;'); goto UpR3f; ivQL8: if (pdo_indexexists('zofui_posterhelp_auth', 'uniacid')) { goto EUafK; } goto EqcqH; zm62g: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_form') . ' ADD INDEX openid(`openid`);'); goto uMLL5; ad0rE: $dat = array("falsescan" => intval($_GPC['falsescan']), "falsejoin" => intval($_GPC['falsejoin']), "maxhelp" => intval($_GPC['maxhelp']), "ak" => $_GPC['ak'], "omessage" => $_GPC['omessage'], "sharetitle" => $_GPC['sharetitle'], "sharedesc" => $_GPC['sharedesc'], "shareimg" => $_GPC['shareimg'], "sharegoodt" => $_GPC['sharegoodt'], "sharegoodd" => $_GPC['sharegoodd'], "paytype" => $_GPC['paytype'], "appid" => $_GPC['appid'], "mchid" => $_GPC['mchid'], "apikey" => $_GPC['apikey'], "secret" => $_GPC['secret'], "mid" => $_GPC['mid'], "head" => $_GPC['head'], "font1" => $_GPC['font1'], "font1font" => $_GPC['font1font'], "font1voice" => $_GPC['font1voice'], "font2" => $_GPC['font2'], "font2font" => $_GPC['font2font'], "font2voice" => $_GPC['font2voice'], "shuatime" => intval($_GPC['shuatime']), "shuatimes" => intval($_GPC['shuatimes']), "getposterfont" => $_GPC['getposterfont'], "helptype" => $_GPC['helptype'], "subbg" => $_GPC['subbg'], "subtxt" => $_GPC['subtxt'], "aakk" => $_GPC['aakk']); goto Dw9kE; JOKeh: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `indextype` tinyint(1) unsigned NOT NULL DEFAULT \'0\';'); goto yamss; jFN1K: pdo_delete('rule_keyword', array("uniacid" => $_W['uniacid'], "rid" => $rule['id'])); goto D13Fh; IWeQc: $ret = file_put_contents(POSETERH_ROOT . 'cert/' . $_W['uniacid'] . '/rootca.pem', trim($_GPC['rootca'])); goto S01Zy; izLB_: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `islink` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0关闭链接消息\';'); goto gCFyD; koHSa: hTPRJ: goto CQ0cD; sDLY0: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_prize') . ' ADD `isdetail` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0不加详情 1加详情\';'); goto MgHSu; LSQRf: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD  `postkey` varchar(64) DEFAULT NULL;'); goto Tr3Ug; mgEvW: if (pdo_indexexists('zofui_posterhelp_geted', 'actid')) { goto C2TUu; } goto PoYNK; Zsmtu: if (empty($_GPC['cert'])) { goto qzoPo; } goto Lb73Z; zyDX6: JbQVX: goto GqknE; Hbn7v: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_user') . ' ADD INDEX code(`code`);'); goto efrHy; GqknE: d6t97: goto itG1J; i6q2e: message('证书保存失败, 请保证 /addons/zofui_posterhelp/cert/ 目录可写，如果无法解决请使用上传工具将证书文件上传至' . POSETERH_ROOT . 'cert/' . $_W['uniacid'] . '目录下，并修改目录和文件权限为0755'); goto nT2Df; f2Cp7: Vt_3L: goto Yvfqw; ft_vt: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `helparr` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0已全部活动计算 1以当前活动计算\';'); goto byFoE; tZTNb: goto sjzOI; goto MUl9Q; kguw1: F__y3: goto SDztD; wy0Cp: Uy2Y2: goto mQnVu; PoYNK: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_geted') . ' ADD INDEX actid(`actid`);'); goto MYcio; S01Zy: $r = $r && $ret; goto xhzBQ; itgxQ: if (pdo_indexexists('zofui_posterhelp_auth', 'openid')) { goto Uy2Y2; } goto MqTz5; R0V9t: if (pdo_indexexists('zofui_posterhelp_auth', 'actid')) { goto L6k6X; } goto Ri_ie; tIV_t: Yitvp: goto Km9_Y; idm5W: if (pdo_indexexists('zofui_posterhelp_qrcode', 'actid')) { goto ZltIH; } goto GY6M3; qBOZ3: if (!empty($_GPC['head'])) { goto yRJnV; } goto hfkUR; Yvfqw: if (empty($_GPC['rootca'])) { goto kO2mo; } goto IWeQc; SJ6lo: if (pdo_indexexists('zofui_posterhelp_geted', 'openid')) { goto TO2lO; } goto XYuYq; efrHy: GN3LY: goto P_p8d; FMVbH: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `isminus` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0取关后不减积分 1减积分\';'); goto MF7XQ; Qo8sW: TO2lO: goto r3Xz4; sfA2A: HdmJi: goto urzZd; Km9_Y: if (pdo_fieldexists('zofui_posterhelp_act', 'prizestart')) { goto JbEWC; } goto Lijth; mQnVu: if (pdo_indexexists('zofui_posterhelp_form', 'openid')) { goto JBd2m; } goto zm62g; FxA62: if (pdo_indexexists('zofui_posterhelp_user', 'status')) { goto CuQZ5; } goto i0qoA; v95Cz: if (pdo_fieldexists('zofui_posterhelp_act', 'postkey')) { goto Ma0rS; } goto LSQRf; MgHSu: Hx_fK: goto yK0Ry; v60NF: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_user') . ' ADD INDEX isstart(`isstart`);'); goto FPAEp; nT2Df: kQazv: goto HM0b2; XYuYq: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_geted') . ' ADD INDEX openid(`openid`);'); goto Qo8sW; yK0Ry: if (pdo_fieldexists('zofui_posterhelp_prize', 'detail')) { goto RKkHd; } goto agDTB; MRZ3T: DFKC3: goto hKcvY; ACCfM: S3P1m: goto Wd_NH; ekYn_: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD INDEX end(`end`);'); goto SZRLp; FPAEp: gl0KB: goto hirT3; r19fy: W_dZd: goto XqwN0; t700W: ZltIH: goto h8xAd; sVY26: pdo_query('ALTER TABLE ' . tablename('zofui_posterhelp_act') . ' ADD `isverifyh` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'0直接发红包 1审核后再发\';'); goto ACCfM; Tr3Ug: Ma0rS: goto V7hxT; dYUn8: $rid = WebCommon::doRule('处理帮助关键字', $mykey, 3); goto kjjiU; L8LQe: JbEWC: goto RaOhl; uMLL5: JBd2m: goto mgEvW; dL5aW: if (pdo_indexexists('zofui_posterhelp_user', 'actid')) { goto MTo8l; } goto qU10i; cKDz7: if (pdo_fieldexists('zofui_posterhelp_act', 'islinkmess')) { goto F__y3; } goto wc3T5; vAnHW: J87SF: goto SjLqG; XNL8t: sX5Ss: goto ad0rE; Vy_sJ: } } ?>