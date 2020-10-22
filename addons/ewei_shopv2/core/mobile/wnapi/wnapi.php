<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Wnapi_EweiShopV2Page extends MobilePage
{
    //    测试
//    public $app_key = "1e55002198db11ea865a7cd30adfe8ac";
//    public $app_parenter = "6000933_6000772";
//  正式
    public $app_key = "dc2455bee0dd4cadb6f2b89603af144d";
    public $app_parenter = "47663_13267";

    public function __construct()
    {
        global $_GPC;
        $hash = 'f4a3ae4bab11c3345b8d40e444614e5a';
        if ($_GPC['hash'] !== $hash || !isset($_GPC['hash'])) {
            exit();
        }
    }
    //-------------------------------------------维妮供应链 begin----------------------------------------------
    //更新维妮分类表
    public function main()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');    //内存无限

        $interfacename = 'GoodsCategorySynchro';
        //leve1 一级分类
        $content = array(
            'Level' => '1',
            'ParentId' => '0'
        );
        $contentjson = json_encode($content);
        $res = $this->_common_api($interfacename, $contentjson);
        if (!empty($res)) {
            //插入数据表
            $this->_insert_cat($res);
            foreach ($res as $key => $cat) {
                //leve2  二级分类
                $content = array(
                    'Level' => '2',
                    'ParentId' => $cat['Id']
                );
                sleep(1);
                $cat['child'] = $this->_common_api($interfacename, json_encode($content));
                if (!empty($cat['child'])) {
                    //插入数据表
                    $this->_insert_cat($cat['child']);
                    foreach ($cat['child'] as $key2 => $child_leve2) {
                        //leve3  三级分类
                        $content = array(
                            'Level' => '3',
                            'ParentId' => $child_leve2['Id']
                        );
                        sleep(1);
                        $child_leve2['child'] = $this->_common_api($interfacename, json_encode($content));
                        if (!empty($child_leve2['child'])) {
                            //插入数据表
                            $this->_insert_cat($child_leve2['child']);
                        }
                    }
                }
            }
            exit();
        } else {
            exit();
        }
    }
    private function _insert_cat($cat_list)
    {
        foreach ($cat_list as $item) {
            $data = array();
            $data['ParentId'] = $item['ParentId'];
            $data['Name'] = $item['Name'];
            $data['Level'] = $item['Level'];
            $data['wn_id'] = $item['Id'];
            $cat = pdo_fetch("select * from " . tablename('ewei_shop_category_wn') . " WHERE wn_id = :id AND Level = :level", array(':id' => $item['Id'], ':level' => $item['Level']));
            if ($cat == false) {
                $is_up = pdo_insert('ewei_shop_category_wn', $data);
                $str = '新增:--' . $item['Id'] . '--' . $item['Level'] . '分类--';
                $str .= empty($is_up) ? '失败' : '成功';
                $str .= '时间:' . $this->getMsecToMescdate();
                file_put_contents('sitet.txt', var_export($str, true) . PHP_EOL, FILE_APPEND);
            }
        }
    }

    //更新维妮商品
    public function wn_goods_list(){
        set_time_limit(0);
        ini_set('memory_limit', '-1');    //内存无限
        $this->_wn_cron_goodsList(1);
    }
    //获取商品列表信息
    private function _wn_cron_goodsList($page)
    {
        /*查询全部商品*/
        $interfacename = 'SkuListSynchro';
        $content = array(
            'PageNo' => $page,
            'PageNum' => 100,
        );
        $contentjson = json_encode($content);
        $result = $this->_common_api($interfacename, $contentjson);
        if (!empty($result)){
            $this->_insert_goods($result);
            $count=ceil($result['TotalCount']/100);
            if ($page+1>$count+1){
                file_put_contents('./wnlog/sitet.txt',var_export('结束',true).PHP_EOL,FILE_APPEND);
                $this->wn_goods_num();//更新产品库存+商品上下架
                exit('success');
            }
            for ($i=$page;$i<=$count;$i++){
                file_put_contents('./wnlog/sitet.txt',var_export($page,true).PHP_EOL,FILE_APPEND);
                sleep(1);
                $this->_wn_cron_goodsList($page+1);
            }
        }else{
            file_put_contents('./wnlog/sitet.txt',var_export('空了',true).PHP_EOL,FILE_APPEND);
            exit('error');
        }
        return $result;
    }
    //维妮商品插入
    private function _insert_goods($goods_list)
    {
        //组装商品
        foreach ($goods_list['SkuList'] as $skulist) {
            $result=array();
            $result['SkuNo'] = $skulist['SkuNo'];
            $result['SkuName'] = $skulist['SkuName'];
            $result['BarCode'] = $skulist['BarCode'];
            $result['SettlePrice'] = $skulist['SettlePrice'];
            $result['RetailPrice'] = $skulist['RetailPrice'];
            $result['Brand'] = $skulist['Brand'];
            $result['Country'] = $skulist['Country'];
            $result['Category'] = $skulist['Category'];
            $result['TwoCategory'] = $skulist['TwoCategory'];
            $result['ThreeCategory'] = $skulist['ThreeCategory'];
            $result['Details'] = $skulist['Details'];
            $result['Rate'] = $skulist['Rate'];
            $result['DeliveryCode'] = $skulist['DeliveryCode'];
            $result['SaleType'] = $skulist['SaleType'];
            $result['Weight'] = $skulist['Weight'];
            $result['displayImgUrls'] = $skulist['displayImgUrls'];
            $result['detailImgUrls'] = $skulist['detailImgUrls'];
            $result['DeliveryCity'] = $skulist['DeliveryCity'];
            $result['goodsNo'] = $skulist['goodsNo'];
            $result['Spec'] = $skulist['Spec'];
            $result['LimitNum'] = $skulist['LimitNum'];
            $result['ValidDay'] = strtotime($skulist['ValidDay']);
            $result['IsLimitPrice'] = $skulist['IsLimitPrice'];
            $result['IfInvoice'] = $skulist['IfInvoice'];
            $result['Status'] = $skulist['Status']=='上架' ? 1 : 0;
            $result['ShelfTime'] = strtotime($skulist['ShelfTime']);
            $result['UpdateTime'] = strtotime($skulist['UpdateTime']);

            $goods = pdo_fetch("select * from " . tablename('ewei_shop_goods_wn') . " WHERE SkuNo = :skuno", array(':skuno' => $skulist['SkuNo']));
            if (empty($goods)) {
                $is_up=pdo_insert('ewei_shop_goods_wn', $result);
                $this->_insert_file('api.goods.status.txt',$is_up,$skulist['SkuNo'],'增加');
            }else{
                if ($goods['SettlePrice']!=$skulist['SettlePrice'] || $goods['Rate']!=$skulist['Rate'] || $goods['Status']!=$result['Status'] || $goods['LimitNum']!=$skulist['LimitNum']){
                    $is_up=pdo_update('ewei_shop_goods_wn', array('Status'=>$result['Status'],'SettlePrice'=>$skulist['SettlePrice'],'Rate'=>$skulist['Rate']),array('SkuNo'=>$skulist['SkuNo']));
                    $this->_insert_file('api.goods.status.txt',$is_up,$skulist['SkuNo'],'修改');
                }
            }
        }
    }

    //维妮库存更新+商品上下架
    public function wn_goods_num(){
        /*查询全部商品*/
        $goods = pdo_fetchall("select * from " . tablename('ewei_shop_goods_wn'));
        $count = ceil(count($goods)/2000);
        for ($i=1;$i<=$count;$i++){
            //定义页码
            $pindex = $i;
            //定义记录数
            $psize = 2000;

            $goodsList = pdo_fetchall('SELECT id,SkuNo,Quantity FROM ' . tablename('ewei_shop_goods_wn') . " ORDER BY id DESC LIMIT " . ($pindex -1) * $psize . ", {$psize}");
            $this->_goods_num($goodsList);
        }
        foreach ($goods as $list){
            $status= $list['Quantity']>0 ? 1 : 0;
            $is_up=pdo_update('ewei_shop_goods_wn', array('Status'=>$status),array('SkuNo'=>$list['SkuNo']));
            $this->_insert_file('Api.goodsnum.txt',$is_up,$list['SkuNo'],'商品上下架');
        }
        exit();
    }
    private function _goods_num($GoodsAttr){
        foreach ($GoodsAttr as $k=>$skuno){
            $id[]=$skuno['SkuNo'];
        }
        $interfacename = 'StockSynchro';
        $content = array(
            'StockReqs' => $id,
        );
        $contentjson = json_encode($content);
        $result = $this->_common_api($interfacename, $contentjson);
        if (!empty($result)){
            foreach ($result as $list){
                $is_up=pdo_update('ewei_shop_goods_wn', array('Quantity'=>$list['Quantity']),array('SkuNo'=>$list['SkuNo']));
                $this->_insert_file('Api.goodsnum.txt',$is_up,$list['SkuNo'],'修改库存');
            }
        }
    }
    //-------------------------------------------维妮供应链 end----------------------------------------------


    /**
     * 商品单位获取+商品名称
     * @param $goodsname
     * @return array|string[]
     */
    private function get_unit($goodsname)
    {
        if (!empty($goodsname)) {
            //商品名称
            $preg_name = "/\[([^\]]*)\]/i";
            preg_match($preg_name, $goodsname, $arrMatches);
            $name = substr_replace($goodsname, "", strpos($goodsname, $arrMatches[0]), strlen($arrMatches[0]));

            //单位
            $preg_unit = "/(罐|块|瓶|盒|件|卷|套|片|箱|张|支|包|把|个|双|扎)/i";
            preg_match($preg_unit, $arrMatches[1], $unit);

            $res = array(
                'goodsname' => $name,
                'unit' => empty($unit[0]) ? '件' : $unit[0],
            );
            return $res;
        }
        return [ 'goodsname' => '', 'unit' => ''];
    }

    /**
     * 查询分类
     * @param $cat_name
     * @param $level
     * @return |null
     */
    private function cats($cat_name,$level){
        $id=pdo_fetch("select id from " . tablename('ewei_shop_category'). " WHERE name=:name AND level=:level", array(':name'=>$cat_name,':level'=>$level));
        if ($id){
            return $id['id'];
        }
        return null;
    }

    /**
     * 去掉详情字符的方法
     * @param array $detail
     * @return string|string[]
     */
    private function _goods_detail($detail,$detail_txt){
        $detail=explode(';',$detail);

        $html='<div style="margin: 0px auto; padding: 40px 0px 0px; text-align: center; max-width: 1000px; color: rgb(77, 77, 77); font-family: &quot;Microsoft YaHei&quot;, tahoma, arial, &quot;Hiragino Sans GB&quot;, 宋体, sans-serif; white-space: normal; background-color: rgb(255, 255, 255);">';
        $html.=$detail_txt;
        if (!empty($detail)){
            foreach ($detail as $list){
                $html.="<img src=\"$list\"/>";
            }
        }
        $html.='</div><p><br/></p>';
        return $html;
    }

    /**
     *增加图片前缀
     * @param $arr
     * @return mixed
     */
    private function array_images($arr) {
        $arr=explode(';',$arr);
        foreach ($arr as &$a) {
            $a = save_media($a);
        }
        unset($a);
        return $arr;
    }

    /**
     * 指定插入文件和内容
     * @param $file //文件名
     * @param $status //状态
     * @param $id //id
     */
    private function _insert_file($file,$status,$id,$content){
        $status=empty($status) ? '失败' : '成功';
        $str=$id.':'.$content.$status.'--'.date('Y-m-d H:i:s',time());
        file_put_contents("./wnlog/$file",$str.PHP_EOL,FILE_APPEND);
    }

    /**
     * 毫秒转日期
     */
    private function getMsecToMescdate()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $msectime = $msectime * 0.001;
        if (strstr($msectime, '.')) {
            sprintf("%01.3f", $msectime);
            list($usec, $sec) = explode(".", $msectime);
            $sec = str_pad($sec, 3, "0", STR_PAD_RIGHT);
        } else {
            $usec = $msectime;
            $sec = "000";
        }
        $date = date("Y-m-d H:i:s.x", $usec);
        return $mescdate = str_replace('x', $sec, $date);
    }

    /**
     * 公共api获取内容
     * @param $interfacename
     * @param $contentjson
     * @return array|bool|float|int|mixed|stdClass|string|null
     */
    private function _common_api($interfacename, $contentjson)
    {
        //vip.nysochina.com
        //121.41.84.251:9090
        $url = "vip.nysochina.com/api/" . $interfacename . '.shtml';
        $strTime = date('Y-m-d', time());
        //组装token
        $token = md5($this->app_key . $strTime . $interfacename . $contentjson);

        $params = array(
            'interfacename' => $interfacename,
            'parenter' => $this->app_parenter,
            'token' => $token,
            'content' => $contentjson
        );

        $content = $this->juhecurl($url, $params, 1);

        $result = json_decode($content, true);
        if (!isset($result['success'])) {
            return $result;
        }

        if ($result) {
            if ($result['success'] == true) {
                return $result['result'];
            }
        }
        return null;
    }

    /**
     * 请求接口返回内容
     * @param string $url [请求的URL地址]
     * @param string $params [请求的参数]
     * @param int $ipost [是否采用POST形式]
     * @return  string
     */
    private function juhecurl($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $headers = array(
            "Content-type:application/json;charset=utf-8",
            "Accept:application/json",
            'interfacename:' . $params['interfacename'],
            'parenter:' . $this->app_parenter,
            'token:' . $params['token'],
        );
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
}


?>