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
$sql = "select a.*  from " . tablename("account_wxapp") . " a" . " left join " . tablename("wxapp_versions") . " b on b.uniacid=a.uniacid WHERE b.modules LIKE '%{$_GPC['m']}%'  and a.name LIKE :name group by a.uniacid";
$select_sql = $sql . " LIMIT " . ($pageindex - 1) * $pagesize . "," . $pagesize;
$list = pdo_fetchall($select_sql, array(':name' => $where));
$list2 = pdo_fetchall($sql, array(':name' => $where));
//$total = pdo_fetchcolumn("select count(a.*) from " . tablename("account_wxapp") . " a" . " left join " . tablename("wxapp_versions") . " b on b.uniacid=a.uniacid WHERE b.modules LIKE '%{$_GPC['m']}%' and  a.name LIKE :name  group by a.uniacid", array(':name' => $where));
$pager = pagination(count($list2), $pageindex, $pagesize);
include $this->template('web/wxapplist');