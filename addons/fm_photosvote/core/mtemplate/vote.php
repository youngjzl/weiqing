<?php
//$messagetemplate = iunserializer($reply['mtemplates']);
 //$template_id = $mt;//消息模板id 微信的模板id
 $body = "";
 $keyword1 = "";
 if (!empty($template_id)) {

	if (!empty($u['realname'])) {
		$uname = $u['realname'];
	}else {
		$uname = $this->getname($rid,$tuservote['tfrom_user'], '15');
	}

	$ttime = date('Y-m-d H:i:s', $tuservote['createtime']);
    $body .= "您的姓名：{$tuservote['nickname']} \n";
    $body .= "被投票ID：{$u['uid']} \n";
    $body .= "被投票用户：$uname \n";
    $body .= "投票时间：$ttime \n";
    $body .= "恭喜您成功投了 ".$tuservote['vote_times']." 票，想要让更多的人认识你吗，那就来赶快报名吧！";


	$title = '投票成功!';
	$datas=array(
		'name'=>array('value'=>$_W['account']['name'],'color'=>'#173177'),
		'first'=>array('value'=>$reply['title'],'color'=>'#173177'),
		'keyword1'=>array('value'=>$title,'color'=>'#173177'),
		'keyword2'=>array('value'=>$ttime,'color'=>'#173177'),
		'remark'=> array('value'=>$body,'color'=>'#000000')
	);
	$data=json_encode($datas); //发送的消息模板数据
}
?>
