<?php
if ($id == 'end') {
	$template_id = $fmqftemplate['endtemplate'];//消息模板id 微信的模板id
	$body = "";
	$keyword1 = "";
	if (!empty($template_id)) {
		$uname = $this->getname($rid, $from_user, '15');
		$ttime = date('Y-m-d H:i:s', $item['createtime']);
	    $body .= "查看我的信息 ☟ $k2 \n";
	    $reply = pdo_fetch("SELECT title FROM " . tablename($this -> table_reply) . " WHERE rid = :rid LIMIT 1", array(':rid' => $rid));

		$piaoshu = $u['photosnum'] + $u['xnphotosnum'];
		$renqi = $u['hits'] + $u['xnhits'];
		$share = $u['sharenum'];
		$rank = $this->GetPaihangcha($rid, $from_user);
		$k3 = '总得票：'.$piaoshu.'   总人气：'.$renqi.'   排名: '.$rank['rank'];
		$url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('tuser', array('rid' => $rid, 'tfrom_user' => $from_user));
		$url = urldecode($url);
		$title = '活动结果发布：';
		$datas=array(
			'first'=>array('value'=>$title,'color'=>'#1587CD'),
			'keyword1'=>array('value'=>$reply['title'],'color'=>'#1587CD'),
			'keyword2'=>array('value'=>$total,'color'=>'#173177'),
			'keyword3'=>array('value'=>$k3,'color'=>'#173177'),
			'remark'=> array('value'=>$body,'color'=>'#FF9E05')
		);
		$data=json_encode($datas); //发送的消息模板数据
	}

}else{
	$template_id = $fmqftemplate['fmqftemplate'];//消息模板id 微信的模板id
	 $body = "";
	 $keyword1 = "";

	 if (!empty($template_id)) {
		$uname = $this->getname($rid, $from_user, '15');
		$k2 = $item['description'];
		$ttime = date('Y-m-d H:i:s', $item['createtime']);
	  //  $body .= "您的姓名：{$u['nickname']} \n";
	 //   $body .= "被投票ID：{$u['uid']} \n";
	  //  $body .= "被投票用户：$uname \n";
	    $body .= "文章描述：$k2 \n";
	    $body .= "更多精彩的内容正在等着您，快来查看吧 ☟";


		$title = $u['nickname'].'  “'.$item['title'].'”已经发布。';
		$datas=array(
			'first'=>array('value'=>$title,'color'=>'#1587CD'),
			'keyword1'=>array('value'=>$item['title'],'color'=>'#1587CD'),
			'keyword2'=>array('value'=>$ttime,'color'=>'#173177'),
			'remark'=> array('value'=>$body,'color'=>'#FF9E05')
		);
		$data=json_encode($datas); //发送的消息模板数据
	}
}



?>
