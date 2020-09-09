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
        $this->_cron_category(0);
        $this->_cron_goodsList(1);
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
                        $str = '新增:--' . $val['id'] . '--' . $val['name'];
                        $str .= empty($is_up) ? '失败' : '成功';
                        $str .= ';--时间:' . $this->getMsecToMescdate();
                        file_put_contents('./aoanlog/cat_log.txt', var_export($str, true) . PHP_EOL, FILE_APPEND);

                        $this->_cron_category($val['id']);
                    }
                }
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
    /*获取商品信息*/
    private function _cron_goodsList($page){
        $login = $this->access_token();
        $time = $this->getMillisecond();
        /*查询全部分类*/
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
                    if (!$goods) {
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
                        $is_up = pdo_insert('ewei_shop_goods_aoan', $goods_data);

                        $str = '新增商品:--' . $val['id'] . '--' . $val['name'];
                        $str .= empty($is_up) ? '失败' : '成功';
                        $str .= ';--时间:' . $this->getMsecToMescdate();
                        file_put_contents('./aoanlog/goods_log.txt', var_export($str, true) . PHP_EOL, FILE_APPEND);
                    }
                }
                $page = $page + 1;
                if($page <= $result['data']['totalPageCount']){
                    $this->_cron_goodsList($page);
                    echo $page.'|'.$result['data']['totalPageCount']."<br>";
                }
            }
        }
    }
    /*更新商品详细*/
    private function _cron_goodsInfo($goodsId, $catId = 0, $login){
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
        $content = $this->juhecurl($url, $paramstring);echo $content."<br>";
        $result = json_decode($content, true);
        if($result){
            if($result['success'] == true){
                echo $result['data'];
            }
        }

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