<?php
defined('IN_IA') or exit('Access Denied');
global $_GPC, $_W;
include_once FMROOT.'core/function/count.php';
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

if ($operation == 'display') {
	$replyall = pdo_fetchall("SELECT title,rid FROM ".tablename($this->table_reply)." WHERE uniacid = :uniacid ORDER BY `status` DESC, `end_time` DESC, `id` DESC", array(':uniacid' => $uniacid));

} elseif ($operation == 'hzbb') {

} elseif ($operation == 'delete') {

}
include $this->template('web/statistics');