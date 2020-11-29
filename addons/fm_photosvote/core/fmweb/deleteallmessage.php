<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
if (!empty($rid)) {
	if ($_GPC['type'] == 'sec') {
		//pdo_delete($this->table_bbsreply, array('rid' => $rid, ));
		//$this->message('删除成功！', referer(),'success');
	}else{
		pdo_delete($this->table_bbsreply, " rid = ".$rid);
		$this->message('删除成功！', referer(),'success');
	}

}
