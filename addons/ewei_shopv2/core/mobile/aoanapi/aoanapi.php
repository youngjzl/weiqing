<?php
//-------------------------------------------澳安供应链 begin----------------------------------------------
//-------------------------------------------澳安供应链 end----------------------------------------------

if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Aoanapi_EweiShopV2Page extends MobilePage
{
    public $appkey = "93029013";
    public $secret = "a190791ef13f49a39be08b25255edb98";
    public $username = "13860493992";
    public $password = "123123";

    /**
     * 获取分类和产品信息
     */
    public function main() {
        set_time_limit(0);
        ini_set('memory_limit', '-1');    //内存无限
        $this->_cron_category(0);
        $this->_cron_goodsList(1);
    }
    /*获取更新分类信息*/
    private function _cron_category($catId = 0){
        $login = $this->access_token();
        $time = $this->getMillisecond();
        /*查询全部分类*/
        $url = "http://api.shangaoan.com/api/v2/goods/getTopCategory";
        $params = array(
            'accountId' => $login['accountId'],
            'memberId' => $login['memberId'],
            'token' => $login['token'],
            'appkey' => $this->appkey,
            'timestamp' => $time,
            'catId' => $catId,
            'listShow' => 0,
        );
        ksort($params);
        $source = utf8_encode(http_build_query($params));
        $signature = sha1($this->secret.$source.$this->secret);
        $params['topSign'] = strtoupper($signature);
        $paramstring = http_build_query($params);
        $content = $this->juhecurl($url, $paramstring);
        $result = json_decode($content, true);
        if($result){
            if($result['success'] == true && $result['data']['totalCount'] > 0){
                foreach($result['data']['result'] as $val){
                    $cat = pdo_fetch("select * from " . tablename('ewei_shop_category_aoan') . " WHERE aoan_id = :id", array(':id' => $val['id']));
                    if (!$cat) {
                        $cat_data = array(
                            'aoan_id' => $val['id'],
                            'name' => $val['name'],
                            'parentId' => $val['parentId'],
                            'image' => $val['image'],
                        );
                        $is_up = pdo_insert('ewei_shop_category_aoan', $cat_data);
                        $this->_insert_file('./aoanlog/cat_log.txt',$is_up,$val['id'],'添加分类');
                        sleep(1);
                        $this->_cron_category($val['id']);
                    }
                }
            }else{
                $this->_insert_file('./aoanlog/cat_log.txt',0,0,'数据错误');
            }
        }
    }


    /*获取商品信息列表*/
    private function _cron_goodsList($page){
        $login = $this->access_token();
        $time = $this->getMillisecond();
        /*查询全部商品*/
        $url = "http://api.shangaoan.com/api/v2/goods/searchingGoods ";
        $params = array(
            'accountId' => $login['accountId'],
            'memberId' => $login['memberId'],
            'token' => $login['token'],
            'appkey' => $this->appkey,
            'timestamp' => $time,
            'pageSize' => 50,
            'pageNum' => $page,
            'ignoreStock' => 0
        );
        ksort($params);
        $source = utf8_encode(http_build_query($params));
        $signature = sha1($this->secret.$source.$this->secret);
        $params['topSign'] = strtoupper($signature);
        $paramstring = http_build_query($params);
        $content = $this->juhecurl($url, $paramstring);

        $result = json_decode($content, true);
        if($result){
            if($result['success'] == true && $result['data']['totalCount'] > 0){
                foreach ($result['data']['result'] as $val) {
                    $goods = pdo_fetch("select * from " . tablename('ewei_shop_goods_aoan') . " WHERE goodsId = :goodsId", array(':goodsId' => $val['goodsId']));
                    if ($goods) {
                        //商品数据
                        $goods_data = array(
                            'activeEnableStore' => $val['activeEnableStore'],
                            'activitySort' => $val['activitySort'],
                            'barCode' => $val['barCode'],
                            'big' => $val['big'],
                            'brandId' => $val['brandId'],
                            'brandName' => $val['brandName'],
                            'buyNum' => $val['buyNum'],
                            'catId' => $val['catId'],
                            'catName' => $val['catName'],
                            'countryId' => $val['countryId'],
                            'countryName' => $val['countryName'],
                            'displayProgress' => $val['displayProgress'],
                            'enableStore' => $val['enableStore'],
                            'goodsId' => $val['goodsId'],
                            'grantAuthGoodsId' => $val['grantAuthGoodsId'],
                            'grantAuthStatus' => $val['grantAuthStatus'],
                            'hotSort' => $val['hotSort'],
                            'isBuyLink' => $val['isBuyLink'],
                            'isExclusively' => $val['isExclusively'],
                            'isFreepost' => $val['isFreepost'],
                            'labelType' => $val['labelType'],
                            'labelValue' => $val['labelValue'],
                            'life' => $val['life'],
                            'lvList' => $val['lvList'],
                            'memberStatus' => $val['memberStatus'],
                            'minEarnings' => $val['minEarnings'],
                            'mktprice' => $val['mktprice'],
                            'name' => $val['name'],
                            'nextActivityPrice' => $val['nextActivityPrice'],
                            'orderTagId' => $val['orderTagId'],
                            'originalPrice' => $val['originalPrice'],
                            'pCatId' => $val['pCatId'],
                            'pCatName' => $val['pCatName'],
                            'preferentialPrice' => $val['preferentialPrice'],
                            'price' => $val['price'],
                            'salePrice' => $val['salePrice'],
                            'small' => $val['small'],
                            'sn' => $val['sn'],
                            'specList' => json_encode($val['specList']),
                            'tagBigLogo' => $val['tagBigLogo'],
                            'tagId' => $val['tagId'],
                            'tagLogo' => $val['tagLogo'],
                            'tagName' => $val['tagName'],
                            'tagSort' => $val['tagSort'],
                            'tagValid' => $val['tagValid'],
                            'tax' => $val['tax'],
                            'thumbnail' => $val['thumbnail'],
                            'tiny' => $val['tiny'],
                            'tradeType' => $val['tradeType'],
                            'tradeTypeName' => $val['tradeTypeName'],
                            'typeId' => $val['typeId'],
                            'unitPrice' => $val['unitPrice'],
                        );
                        $details=$this->goodsInfo($val['goodsId']);
                        $details=!empty($details)&&empty($details['success']==true) ? $details['data']['intro'] : '';
                        $goods_data['Details']=$details;

                        $is_up = pdo_insert('ewei_shop_goods_aoan', $goods_data);
                        file_put_contents('./aoanlog/goods_log.txt', var_export($is_up, true) . PHP_EOL, FILE_APPEND);
                        if (!empty($is_up)){
                            //多规格列表
                            $spec=$this->_cron_goods_specList($val['goodsId']);
                            if ($spec['success'] == true){
                                foreach ($spec['data'] as $list){
                                    $is_add_spec = pdo_fetch("select * from " . tablename('ewei_shop_goods_aoan_spec') . " WHERE goodsId = :goodsId and productName = :productName", array(':goodsId' => $val['goodsId'],':productName'=>$val['productName']));
                                    if (!empty($is_add_spec)){
                                        continue;
                                    }
                                    $data = array(
                                        'productName' =>$list['productName'],
                                        'average' =>$list['average'],
                                        'tax' =>$list['tax'],
                                        'weight' =>$list['weight'],
                                        'productNum' =>$list['productNum'],
                                        'price' =>$list['price'],
                                        'unit' =>$list['unit'],
                                        'isFreePost' =>$list['isFreePost'],
                                        'goodsId' =>$val['goodsId'],
                                    );
                                    $is_up = pdo_insert('ewei_shop_goods_aoan_spec', $data);
                                    $str = '新增多规格:--' . $val['goodsId'];
                                    $str .= empty($is_up) ? '失败' : '成功';
                                    $str .= ';--时间:' . $this->getMsecToMescdate();
                                    file_put_contents('./aoanlog/goods_log.txt', var_export($str, true) . PHP_EOL, FILE_APPEND);
                                }
                            }
                            $str = '新增商品:--' . $val['id'] . '--' . $val['name'];
                            $str .= empty($is_up) ? '失败' : '成功';
                            $str .= ';--时间:' . $this->getMsecToMescdate();
                            file_put_contents('./aoanlog/goods_log.txt', var_export($str, true) . PHP_EOL, FILE_APPEND);
                        }
                    }
                }
                $page = $page + 1;
                if($page <= $result['data']['totalPageCount']){
                    $this->_cron_goodsList($page);
                }
            }
        }
    }
    /*获取商品详细单个*/
    private function goodsInfo($goodsId){
        $login = $this->access_token();
        $time = $this->getMillisecond();
        $url = 'http://api.shangaoan.com/api/v2/goods/getGoodsInfo';
        $params = array(
            'accountId' => $login['accountId'],
            'memberId' => $login['memberId'],
            'token' => $login['token'],
            'appkey' => $this->appkey,
            'timestamp' => $time,
            'goodsId' => $goodsId
        );
        ksort($params);
        $source = utf8_encode(http_build_query($params));
        $signature = sha1($this->secret.$source.$this->secret);
        $params['topSign'] = strtoupper($signature);
        $paramstring = http_build_query($params);
        $content = $this->juhecurl($url, $paramstring);
        $result = json_decode($content, true);
        file_put_contents('./aoanlog/goodsdetails_log.txt', var_export($result, true) . PHP_EOL, FILE_APPEND);
        return $result;
    }
    //商品多规格
    private function _cron_goods_specList($goodsId){
        $login = $this->access_token();
        $time = $this->getMillisecond();
        /*查询全部分类*/
        $url = "http://api.shangaoan.com/api/v2/goods/getGoodsSpecs";
        $params = array(
            'accountId' => $login['accountId'],
            'memberId' => $login['memberId'],
            'token' => $login['token'],
            'appkey' => $this->appkey,
            'timestamp' => $time,
            'goodsId'=>$goodsId
        );
        ksort($params);
        $source = utf8_encode(http_build_query($params));
        $signature = sha1($this->secret.$source.$this->secret);
        $params['topSign'] = strtoupper($signature);
        $paramstring = http_build_query($params);
        usleep(500000);
        $content = $this->juhecurl($url, $paramstring);

        $result = json_decode($content, true);
        file_put_contents('./aoanlog/goodsspec_log.txt', var_export($result, true) . PHP_EOL, FILE_APPEND);
        return $result;
    }

    //更新商品信息
    public function up_goodsinfo(){
        $goods = pdo_fetchall("select goodsId from " . tablename('ewei_shop_goods_aoan'));
        $data=array();
        foreach ($goods as $goodsid){
            $goodsinfo=$this->goodsInfo($goodsid['goodsId']);
            if (!empty($goodsinfo)&&$goodsinfo['success']==true){
                if (!empty($goodsinfo['data']['specs'])){
                    foreach ($goodsinfo['data']['specs'] as $list){
                        $goods_speclist=pdo_fetch("select * from " . tablename('ewei_shop_goods_aoan_spec')." where goodsId=:goodsId and productName=:productName",array(':goodsId'=>$goodsid['goodsId'],':productName'=>$list['specValue']));
                        if (empty($goods_speclist)){
                            $is_up = pdo_insert('ewei_shop_goods_aoan_spec', array(
                                'productName' =>$list['specValue'],
                                'tax' =>$list['tax'],
                                'weight' =>$list['weight'],
                                'productNum' =>$list['num'],
                                'price' =>$list['price'],
                                'unit' =>$list['unit'],
                                'isFreePost' =>$list['isFreePost'],
                                'goodsId' =>$goodsid['goodsId'],
                            ));
                            $text='增加规格';
                        }else{
                            $is_up = pdo_update('ewei_shop_goods_aoan_spec', array(
                                'isFreePost'=>$list['isFreePost'],
                                'weight' =>$list['weight'],
                                'productName' =>$list['specValue'],
                                'productNum' =>$list['num'],
                                'unit' =>$list['unit'],
                                'tax'=>$list['tax'],
                                'price'=>$list['price'],
                            ),array('goodsId'=>$goodsid['goodsId'],'productName'=>$list['specValue']));
                            $text='修改规格';
                        }
                        $this->_insert_file('upgoods.txt',$is_up,$goodsid['goodsId'],$text);
                    }
                }
                $data['isFreePost'] = $goodsinfo['data']['isFreePost'];
                $data['Details'] = $goodsinfo['data']['intro'];
                $data['tax'] = $goodsinfo['data']['tax'];
                $data['price'] = $goodsinfo['data']['price'];
                $data['mktprice'] = $goodsinfo['data']['mktprice'];
                $is_up = pdo_update('ewei_shop_goods_aoan', $data, array('goodsId' => $goodsid['goodsId']));
                $text = '修改商品信息';
                $this->_insert_file('upgoods.txt',$is_up,$goodsid['goodsId'],$text);
            }
        }
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
        file_put_contents("./aoanlog/$file",$str.PHP_EOL,FILE_APPEND);
    }
    /* php 获取13位时间戳 */
    public function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
    private function access_token(){
        $time = $this->getMillisecond();
        $url = "http://api.shangaoan.com/ssoapi/v2/login/login";
        $params = array(
            'appkey' => $this->appkey,
            'password' => md5($this->password),
            'timestamp' => $time,
            'username' => $this->username
        );
        $source = $this->secret.utf8_encode(http_build_query($params)).$this->secret;
        $signature = sha1($source);
        $params['topSign'] = strtoupper($signature);
        $paramstring = http_build_query($params);
        $content = $this->juhecurl($url, $paramstring);
        $result = json_decode($content, true);
        if($result){
            if($result['success']==true){
                return $result['data'];
            }
        }
        return null;
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
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    private function juhecurl($url, $params=false, $ispost=0){
        $httpInfo = array();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt($ch, CURLOPT_USERAGENT , 'JuheData' );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 60 );
        curl_setopt($ch, CURLOPT_TIMEOUT , 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , true );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
        if($ispost)
        {
            curl_setopt($ch , CURLOPT_POST , true );
            curl_setopt($ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt($ch , CURLOPT_URL , $url );
        }
        else
        {
            if($params){
                curl_setopt($ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt($ch , CURLOPT_URL , $url);
            }
        }
        $response = curl_exec($ch );
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge($httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }
}
?>