<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
$op = empty($_GPC['op']) ? 'display': $_GPC['op'];
$shebei = json_encode($this->getshebei());
$msgs = $this->getmsg('','','all');

include $this->template('web/home');
