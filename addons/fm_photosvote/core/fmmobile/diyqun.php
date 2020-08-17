<?php
defined('IN_IA') or exit('Access Denied');

$templatename = $rbasic['templates'];
if ($templatename != 'default' && $templatename != 'stylebase') {
    require FM_CORE. 'fmmobile/tp.php';
}
$toye = $this->templatec($templatename,$_GPC['do']);
include $this->template($toye);