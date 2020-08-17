<?php

/*
 * 人人商城
 *
 * 青岛易联互动网络科技有限公司
 * http://www.we7shop.cn
 * TEL: 4000097827/18661772381/15865546761
 */
if (!defined('IN_IA')) {
    exit('Access Denied');
}
define('IA_ROOT', str_replace("\\", '/', dirname(dirname(__FILE__))));

require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/Reader/CSV.php';
class Taobaocsv_EweiShopV2Page extends PluginWebPage
{
    function main()
    {
        global $_W, $_GPC;
        $uploadStart = '0';
        $uploadnum = '0';

        $excelurl =$_W['siteroot'].'addons/ewei_shopv2/plugin/goodshelper/data/test.xlsx';
        $zipurl =$_W['siteroot'].'addons/ewei_shopv2/plugin/goodshelper/data/test.zip';

        if ($_W['ispost']) {

            $rows = m('excel')->import('excelfile');
            $num = count($rows);

            $i = 0;
            $colsIndex = array();
            foreach ($rows[1] as $cols => $col) {
                if ($col == 'title') {
                    $colsIndex['title'] = $i;
                }
                if ($col == 'sku_barcode') {
                    $colsIndex['sku_barcode'] = $i;
                }
                if ($col == 'price') {
                    $colsIndex['price'] = $i;
                }
                if ($col == 'num') {
                    $colsIndex['num'] = $i;
                }
                if ($col == 'description') {
                    $colsIndex['description'] = $i;
                }
                if ($col == 'skuProps') {
                    $colsIndex['skuProps'] = $i;
                }
                if ($col == 'picture') {
                    $colsIndex['picture'] = $i;
                }
                if ($col == 'propAlias') {
                    $colsIndex['propAlias'] = $i;
                }

                $i++;
            }

            $filename =  $_FILES['excelfile']['name'];
            $filename = substr($filename , 0 ,strpos($filename,'.') );

            $rows = array_slice($rows, 3, count($rows) - 3);
            $items = array();

            $this->get_zip_originalsize($_FILES['zipfile']['tmp_name'],'../attachment/images/'.$_W['uniacid'].'/'.date('Y').'/'.date('m').'/');

            $num = 0;
            foreach ($rows as $rownu => $col) {

                $item = array();
                $item['title'] = $col[$colsIndex[title]];
                $item['marketprice'] = $col[$colsIndex[price]];
                $item['total'] = $col[$colsIndex[num]];
                $item['content'] = $col[$colsIndex[description]];
                $item['goodssn'] = $col[$colsIndex[sku_barcode]];

                $picContents = $col[$colsIndex[ picture]];
                $allpics = explode(';',$picContents);

                $pics = array();
                $optionpics = array();

                //获取上传图片路径
                foreach($allpics as $imgurl)
                {
                    if(empty($imgurl))
                    {
                        continue;
                    }
                    $picDetail = explode('|',$imgurl);
                    $picDetail = explode(':',$picDetail[0]);

                    $imgurl=$_W['siteroot'].'attachment/images/'.$_W['uniacid'].'/'.date('Y').'/'.date('m').'/'.$picDetail[0].'.png';

                    if(@fopen($imgurl, 'r')) {
                        if ($picDetail[1] == 1) {

                            $pics[] = $imgurl;
                        }
                        if ($picDetail[1] == 2) {
                            $optionpics[$picDetail[0]] = $imgurl;
                        }
                    }

                }
                //商品图片
                $item['pics'] =$pics;

                $items[] = $item;
                $num++;
            }

            session_start();
            $_SESSION['taobaoCSV'] = $items;
            m('cache')->set('taobaoCSV',$items,$_W['uniacid']);

            $uploadStart = '1';
            $uploadnum = $num;

        }

        include $this->template();
    }

    function fetch() {
        global $_GPC,$_W;
        set_time_limit(0);

        $num = intval($_GPC['num']);

        $totalnum = intval($_GPC['totalnum']);
        session_start();
        $items = $_SESSION['taobaoCSV'];
        if(empty($items)){
            $items =   m('cache')->get('taobaoCSV',$_W['uniacid']);
        }
        $ret = $this->model->save_taobaocsv_goods($items[$num]);
        plog('taobaoCSV.main', '淘宝CSV宝贝批量导入'.$ret[goodsid]);

        if($num+1>=$totalnum)
        {
            unset($_SESSION['taobaoCSV']);
        }
        die(json_encode($ret));
    }

    function get_zip_originalsize($filename, $path) {
        //先判断待解压的文件是否存在
        if(!file_exists($filename)){
            die("文件 $filename 不存在！");
        }
        //将文件名和路径转成windows系统默认的gb2312编码，否则将会读取不到
        $filename = iconv("utf-8","gb2312",$filename);
        $path = iconv("utf-8","gb2312",$path);
        //打开压缩包
        $resource = zip_open($filename);
        $i = 1;
        //遍历读取压缩包里面的一个个文件
        while ($dir_resource = zip_read($resource)) {
            //如果能打开则继续
            if (zip_entry_open($resource,$dir_resource)) {
                //获取当前项目的名称,即压缩包里面当前对应的文件名
                $file_name = $path.zip_entry_name($dir_resource);

                //以最后一个“/”分割,再用字符串截取出路径部分
                $file_path = substr($file_name,0,strrpos($file_name, "/"));
                //如果路径不存在，则创建一个目录，true表示可以创建多级目录

                if(!is_dir($file_path)){
                    mkdir($file_path,0777,true);
                }
                //如果不是目录，则写入文件
                if(!is_dir($file_name)){
                    //读取这个文件
                    $file_size = zip_entry_filesize($dir_resource);
                    //最大读取6M，如果文件过大，跳过解压，继续下一个

                    if($file_size<(1024*1024*10)){
                        $file_content = zip_entry_read($dir_resource,$file_size);

                        //控制上传文件类型

                        $ext = strrchr($file_name, ".");
                        if($ext == ".png")
                        {
                            file_put_contents($file_name,$file_content);
                        }
                        else if($ext == ".tbi")
                        {
                            $file_name = substr($file_name,0,strlen($file_name)-4);

                            file_put_contents($file_name.'.png',$file_content);
                        }
                    }else{
                        //echo "<p> ".$i++." 此文件已被跳过，原因：文件过大， -> ".iconv("gb2312","utf-8",$file_name)." </p>";
                    }
                }
                //关闭当前
                zip_entry_close($dir_resource);
            }
        }
        //关闭压缩包
        zip_close($resource);
    }
}