<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');
load()->func('file');
load()->model('material');
load()->model('account');
$dos = array('news', 'tomedia', 'addnews', 'upload_material', 'upload_news');
$do = in_array($do, $dos) ? $do : 'news';

permission_check_account_user('platform_material');
if ($do == 'tomedia') {
	iajax('0', tomedia($_GPC['url']), '');
}

if ($do == 'news') {
	$type = trim($_GPC['type']);
	$newsid = intval($_GPC['newsid']);
	$upload_limit = material_upload_limit();
	if (empty($newsid)) {
		if ($type == 'reply') {
			$reply_news_id = intval($_GPC['reply_news_id']);
			$news = pdo_get('news_reply', array(
				'id' => $reply_news_id 
			));
			$news_list = pdo_getall('news_reply', array(
				'parent_id' => $news['id'] 
			), array(), '', ' displayorder ASC');
			$news_list = array_merge(array(
				$news 
			), $news_list);
			if (!empty($news_list)) {
				foreach ($news_list as $key => &$row_news) {
					$row_news = array(
						'uniacid' => $_W['uniacid'],
						'thumb_url' => $row_news['thumb'],
						'title' => $row_news['title'],
						'author' => $row_news['author'],
						'digest' => $row_news['description'],
						'content' => $row_news['content'],
						'url' => $row_news['url'],
						'displayorder' => $key,
						'show_cover_pic' => intval($row_news['incontent']),
						'content_source_url' => $row_news['content_source_url']
					);
				}
				unset($row_news);
			}
		}
	} else {
		$attachment = material_get($newsid);
		if (is_error($attachment)){
			itoast('图文素材不存在，或已删除', url('platform/material'), 'warning');
		}
		$news_list = $attachment['news'];
	}
	if (!empty($_GPC['new_type'])) {
		$new_type = trim($_GPC['new_type']);
		if (!in_array($new_type, array('reply', 'link'))) {
			$new_type = 'reply';
		}
	}
	if (!empty($news_list)) {
		foreach ($news_list as $key => $row_news) {
			if (empty($row_news['author']) && empty($row_news['content'])) {
				$new_type = 'link';
			} else {
				$new_type = 'reply';
			}
		}
	}
    if ($_W['isajax']){
        $wx_url=$_POST['wx_url'];
        if (!empty($wx_url)){
            $result=juhecurl($wx_url);

            $regex_content="/<div class=\"rich_media_content \" id=\"js_content\".*?>(.*?)\<\/div>/s";
            $regex_content_title='/<h2.*?>(.*?)<\/h2>/is';
            $regex_content_author='/<a.*?id=\"js_name\".*?>(.*?)<\/a>/is';
            preg_match_all($regex_content_title,$result,$title);
            preg_match_all($regex_content,$result,$content);
            preg_match_all($regex_content_author,$result,$author);

            $data=array('title'=>trim($title[1][0]),'author'=>trim($author[1][0]),'content'=>str_replace("data-src","src",$content[1][0]));
            echo json_encode($data,JSON_UNESCAPED_UNICODE);
        }
        exit();
    }
	template('platform/material-post');
}

if ($do == 'addnews') {
	$is_sendto_wechat = trim($_GPC['target']) == 'wechat' ? true : false;
	$attach_id = intval($_GPC['attach_id']);
	if (empty($_GPC['news'])) {
		iajax(- 1, '提交内容参数有误');
	}
	$attach_id = material_news_set($_GPC['news'], $attach_id,$_GPC['cats']);
	if (is_error($attach_id)) {
		iajax(-1, $attach_id['message']);
	}
	if (!empty($_GPC['news_rid'])) {
		pdo_update('news_reply', array('media_id' => $attach_id), array('id' => intval($_GPC['news_rid'])));
	}
	if ($is_sendto_wechat) {
		$result = material_local_news_upload($attach_id);
	}
	if (is_error($result)){
		iajax(-1, $result['message']);
	}else{
		iajax(0, '编辑图文素材成功');
	}
}

if ($do == 'upload_material') {
	$material_id = intval($_GPC['material_id']);
	$result = material_local_upload($material_id);
	if (is_error($result)) {
		iajax(1, $result['message']);
	}
	iajax(0, json_encode($result));
}

if ($do == 'upload_news') {
	$material_id = intval($_GPC['material_id']);
	$result = material_local_news_upload($material_id);
	if (is_error($result)){
		iajax(-1, $result['message']);
	} else {
		iajax(0, '转换成功');
	}
}

function juhecurl($url, $params = false, $ispost = 0)
{
    $httpInfo = array();
    $headers = array(
        'Host' => 'mmbiz.qpic.cn',
        'Connection' => 'keep-alive',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'no-cache',
        'Accept' => 'textml,application/xhtml+xml,application/xml;q=0.9,image/webp,/;q=0.8',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36',
        'Accept-Encoding' => 'gzip, deflate, sdch',
        'Accept-Language' => 'zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4'
    );
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params['content']);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    $response = curl_exec($ch);
    if ($response === FALSE) {
        echo curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);
    return $response;
}