<?php
global $_GPC, $_W;
$GLOBALS['frames'] = $this->getMainMenu();
if ($_GPC['keywords']) {
	$op = $_GPC['keywords'];
	$where = "%$op%";
} else {
	$where = '%%';
}
$pageindex = max(1, intval($_GPC['page']));
$pagesize = 10;
$sql = "select a.user_id,a.money,a.time,a.pay_type,b.name,b.img  from " . tablename("cjdc_hyorder") . " a left join " . tablename("cjdc_user") . " b on b.id=a.user_id WHERE  b.name LIKE :name  and b.uniacid=:uniacid and a.state=2 order by a.id DESC";
$select_sql = $sql . " LIMIT " . ($pageindex - 1) * $pagesize . "," . $pagesize;
$list = pdo_fetchall($select_sql, array(':uniacid' => $_W['uniacid'], ':name' => $where));
$total = pdo_fetchcolumn("select count(*) from " . tablename("cjdc_hyorder") . " a left join " . tablename("cjdc_user") . " b on b.id=a.user_id WHERE  b.name LIKE :name  and b.uniacid=:uniacid  and a.state=2", array(':uniacid' => $_W['uniacid'], ':name' => $where));
$pager = pagination($total, $pageindex, $pagesize);

$order = "select SUM(a.money) as money  from " . tablename("cjdc_hyorder") . " a left join " . tablename("cjdc_user") . " b on b.id=a.user_id WHERE b.uniacid=" . $_W['uniacid'] . " and a.state=2";
$orderMoney = pdo_fetch($order);
include $this->template('web/hyorder');