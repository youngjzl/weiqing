<?php
/**
 * 本破解程序由资源邦提供
 * 资源邦www.wazyb.com
 * QQ:993424780  承接网站建设、公众号搭建、小程序建设、企业网站
 */
$sql = "
	drop table `ims_fm_photosvote_advs`;
	drop table `ims_fm_photosvote_announce`;
	drop table `ims_fm_photosvote_banners`;
	drop table `ims_fm_photosvote_bbsreply`;
	drop table `ims_fm_photosvote_data`;
	drop table `ims_fm_photosvote_iplist`;
	drop table `ims_fm_photosvote_iplistlog`;
	drop table `ims_fm_photosvote_provevote`;
	drop table `ims_fm_photosvote_provevote_picarr`;
	drop table `ims_fm_photosvote_provevote_videoarr`;
	drop table `ims_fm_photosvote_provevote_name`;
	drop table `ims_fm_photosvote_provevote_voice`;
	drop table `ims_fm_photosvote_tags`;
	drop table `ims_fm_photosvote_reply`;
	drop table `ims_fm_photosvote_reply_share`;
	drop table `ims_fm_photosvote_reply_huihua`;
	drop table `ims_fm_photosvote_reply_display`;
	drop table `ims_fm_photosvote_reply_vote`;
	drop table `ims_fm_photosvote_reply_body`;
	drop table `ims_fm_photosvote_templates`;
	drop table `ims_fm_photosvote_templates_designer`;
	drop table `ims_fm_photosvote_templates_designer_menu`;
	drop table `ims_fm_photosvote_votelog`;
	drop table `ims_fm_photosvote_order`;
	drop table `ims_fm_photosvote_voteer`;
	drop table `ims_fm_photosvote_onlyoauth`;
	drop table `ims_fm_photosvote_qunfa`;
	drop table `ims_fm_photosvote_vote_shuapiao`;
	drop table `ims_fm_photosvote_pnametag`;
	drop table `ims_fm_photosvote_jifen`;
	drop table `ims_fm_photosvote_jifen_gift`;
	drop table `ims_fm_photosvote_user_gift`;
	drop table `ims_fm_photosvote_user_zsgift`;
	drop table `ims_fm_photosvote_message`;
	drop table `ims_fm_photosvote_orderlog`;
	drop table `ims_fm_photosvote_counter`;
	drop table `ims_fm_photosvote_qrcode`;
	drop table `ims_fm_photosvote_answer`;
	drop table `ims_fm_photosvote_source`;
	drop table `ims_fm_photosvote_school`;
";
pdo_run($sql);