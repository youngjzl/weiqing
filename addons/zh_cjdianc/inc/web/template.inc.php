<?php
global $_GPC, $_W;
// $action = 'ad';
// $title = $this->actions_titles[$action];
$GLOBALS['frames'] = $this->getMainMenu();
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$item = pdo_get('cjdc_message', array('uniacid' => $_W['uniacid']));
if (checksubmit('submit')) {
	$data['xd_tid'] = trim($_GPC['xd_tid']);
	$data['jd_tid'] = trim($_GPC['jd_tid']);
	// $data['jj_tid'] = trim($_GPC['jj_tid']);
	$data['tk_tid'] = trim($_GPC['tk_tid']);
	$data['cz_tid'] = trim($_GPC['cz_tid']);
	$data['yy_tid'] = trim($_GPC['yy_tid']);
	$data['uniacid'] = trim($_W['uniacid']);

	// $data['shtk_tid'] = trim($_GPC['shtk_tid']);
	// $data['sjyy_tid'] = trim($_GPC['sjyy_tid']);
	// $data['rzsh_tid'] = trim($_GPC['rzsh_tid']);
	// $data['rzcg_tid'] = trim($_GPC['rzcg_tid']);
	// $data['xdd_tid'] = trim($_GPC['xdd_tid']);
	// $data['xdd_tid2'] = trim($_GPC['xdd_tid2']);
	// $data['rush_tid'] = trim($_GPC['rush_tid']);
	// $data['group_tid'] = trim($_GPC['group_tid']);
	// $data['qf_tid'] = trim($_GPC['qf_tid']);
	// $data['qh_tid'] = trim($_GPC['qh_tid']);

	if ($_GPC['id'] == '') {
		$res = pdo_insert('cjdc_message', $data);
		if ($res) {
			message('添加成功', $this->createWebUrl('template', array()), 'success');
		} else {
			message('添加失败', '', 'error');
		}
	} else {
		$res = pdo_update('cjdc_message', $data, array('id' => $_GPC['id']));
		if ($res) {
			message('编辑成功', $this->createWebUrl('template', array()), 'success');
		} else {
			message('编辑失败', '', 'error');
		}
	}
}

if ($_GPC['op'] == 'generate') {
	$item = pdo_get('cjdc_message', array('uniacid' => $_W['uniacid']));
	for ($i = 0; $i < 5; $i++) {
		if ($i == 0) {
			$data['xd_tid'] = $this->generateTemplate('264', ['订单编号', '联系人姓名', '订单金额', '下单时间'], '下单');
		}
		if ($i == 1) {
			$data['jd_tid'] = $this->generateTemplate('3523', ['订单状态', '订单号', '门店名称', '订单金额', '订单类型'], '订单状态');
		}
		if ($i == 2) {
			$data['tk_tid'] = $this->generateTemplate('3335', ['订单编号', '门店名称', '退款金额', '退款时间'], '订单退款');
		}
		if ($i == 3) {
			$data['yy_tid'] = $this->generateTemplate('1342', ['预定门店', '门店电话', '预定单号', '用餐时间'], '预约成功');
		}
		if ($i == 4) {
			$data['cz_tid'] = $this->generateTemplate('3568', ['充值金额', '赠送金额', '充值时间'], '充值');
		}
	}
	// $data['xd_tid'] = $this->generateTemplate('264', ['订单编号', '联系人姓名', '订单金额', '下单时间']);
	// $data['jd_tid'] = $this->generateTemplate('3523', ['订单状态', '订单号', '门店名称', '订单金额', '订单类型']);
	// //$data['jj_tid'] = $this->generateTemplate('AT0375', ['订单号', '拒绝时间', '拒绝原因', '商家名称', '客服电话', '支付金额', '备注']);
	// $data['tk_tid'] = $this->generateTemplate('3335', ['订单编号', '门店名称', '退款金额', '退款时间']);
	// $data['yy_tid'] = $this->generateTemplate('1342', ['预定门店', '门店电话', '预定单号', '用餐时间']);
	// $data['cz_tid'] = $this->generateTemplate('3568', ['充值金额', '赠送金额', '充值时间']);
	//print_R($data);die;
	// $data['qf_tid'] = $this->generateTemplate('AT0480', ['备注', '信息来源', '信息内容', '通知时间']);
	// $data['qh_tid'] = $this->generateTemplate('AT0086', ['排队状态', '排队号码', '桌位类型', '还需等待', '取号时间', '商家名称', '温馨提示']);
	// $data['group_tid'] = $this->generateTemplate('AT0223', ['订单编号', '商品信息', '活动时间', '报名人数']);
	// $data['rush_tid'] = $this->generateTemplate('AT0079', ['订单号', '商品名称', '联系电话', '订单金额', '支付时间', '到期时间']);
	// $data['xdd_tid'] = $this->generateTemplate('AT0079', ['订单内容', '订单类型', '下单时间', '订单金额', '收货人', '联系电话', '收货地址', '订单号']);
	// $data['xdd_tid2'] = $this->generateTemplate('AT0152', ['收款金额', '支付方式', '订单时间', '付款人', '交易单号']);
	// $data['rzcg_tid'] = $this->generateTemplate('AT1709', ['审核结果', '申请时间', '商家名称', '审核备注']);
	// $data['rzsh_tid'] = $this->generateTemplate('AT0444', ['状态', '申请时间', '商家名称', '联系电话', '备注']);
	// $data['sjyy_tid'] = $this->generateTemplate('AT0104', ['预约时间', '订单号', '预约服务', '就餐人数', '联系人', '联系方式']);
	// $data['shtk_tid'] = $this->generateTemplate('AT0637', ['温馨提示', '订单编号', '申请时间', '退款状态', '退款商品', '顾客信息']);
	if ($item) {
		$data['uniacid'] = $_W['uniacid'];
		$res = pdo_update('cjdc_message', $data, array('uniacid' => $_W['uniacid']));
	} else {
		$data['uniacid'] = $_W['uniacid'];
		$res = pdo_insert('cjdc_message', $data);
	}

	if ($res) {
		message('更新成功', $this->createWebUrl('template', array()), 'success');
	} else {
		message('更新失败', '', 'error');
	}
}
include $this->template('web/template');