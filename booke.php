<?php
/**
 * @param $url //是请求的链接
 * @param $postdata //是传输的数据，数组格式
 */
define('FILE_APPEND', 1);

if (!function_exists("file_put_contents")) {

    function file_put_contents($n, $d, $flag = false) {
        $mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
        $f = @fopen($n, $mode);
        if ($f === false) {
            return 0;
        } else {
            if (is_array($d)) $d = implode($d);
            $bytes_written = fwrite($f, $d);
            fclose($f);
            return $bytes_written;
        }
    }

}
function jump_curl($url, $postdata=null)
{

    $header = array(
        'Accept: application/json',
        'Content-Type:'.'application/x-www-form-urlencoded; charset=UTF-8',
    );

    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // 超时设置
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    // 超时设置，以毫秒为单位
    // curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500);

    // 设置请求头
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

    if (!empty($postdata)){
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
    }
    //执行命令
    $data = curl_exec($curl);
    // 显示错误信息
    if (curl_error($curl)) {
        return "Error: " . curl_error($curl);
    } else {
        // 打印返回的内容
        curl_close($curl);
        return $data;
    }
}
function cutstr_html($string){
    $string = str_replace("&nbsp;"," ",$string);

    $string = str_replace("<br />","\n",$string);

    $string = strip_tags($string);

    return $string;

}

//笔趣阁小说下载
function biquge(){
    $begin=336633;
    for ($i=$begin;$i<=336639;$i++){
        $url='http://www.xbiquge.la/0/419/'.$i.'.html';
        $content=jump_curl($url);
        preg_match('/<meta name="description" content="(.*?)".*?>/is',$content,$result);
        preg_match('/新笔趣阁提供了(.*?)创作的.*?《(.*?)》/is',$result[1],$result_book_name);//作者和书名
        preg_match('/<h1>(.*?)<\/h1>/is',$content,$title);//每章节标题
        preg_match('/<div id=\"content\">(.*?)<\/div>/is',$content,$data_content);//每章节内容

        //内容
        //1.文章标题
        $title=$title[1];
        //2.文章内容
        if (file_exists($result_book_name[2].'.txt')){
            if($i==$begin){
               exit("已经有此《".$result_book_name[2]."》书籍;请在当前目录删除后再刷新一下网址！");
            }
            file_put_contents($result_book_name[2].'.txt',PHP_EOL.$title.PHP_EOL .cutstr_html($data_content[1]).PHP_EOL, FILE_APPEND);
        }else{
            //作者
            $result_book_author=$result_book_name[1];

            $str='声明：本书由蒋乖自网络收集整理制作，仅供交流学习使用，版权归原作者和出版社所有，如果喜欢，请支持正版。'.PHP_EOL .PHP_EOL ;
            $str.='书名：《'.$result_book_name[2].'》'.PHP_EOL;
            $str.='作者：'.$result_book_author.PHP_EOL .PHP_EOL ;

            file_put_contents($result_book_name[2].'.txt',$str.$title.PHP_EOL .cutstr_html($data_content[1]).PHP_EOL);
        }
    }
}
biquge();
