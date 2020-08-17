<?php
defined('IN_IA') or exit('Access Denied');
global $_GPC, $_W;
include_once FMROOT.'core/function/count.php';
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

if ($operation == 'display') {
	$reply = pdo_fetch("SELECT title FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
	$now = time();
	$starttime = empty($_GPC['time']['start']) ?  strtotime(date("Y-m-d H:i", $now - 604799)) : strtotime($_GPC['time']['start']);
	$endtime = empty($_GPC['time']['end']) ?  strtotime(date("Y-m-d H:i", $now)) : strtotime($_GPC['time']['end']);
	$stime = $starttime;
	$etime = $endtime;
	$days = gettongji('day', $stime, $etime,$this->table_log,$rid);
	$users = gettongji('day', $stime, $etime,$this->table_users,$rid);

	$messages = gettongji('day', $stime, $etime,$this->table_bbsreply,$rid);
	$jidus = gettongji('day', $stime, $etime,$this->table_bbsreply,$rid);
	$fanyis = gettongji('day', $stime, $etime,$this->table_bbsreply,$rid);
	$xingjis = gettongji('day', $stime, $etime,$this->table_bbsreply,$rid);

} elseif ($operation == 'hzbb') {

} elseif ($operation == 'delete') {

}
include $this->template('web/fmcount');