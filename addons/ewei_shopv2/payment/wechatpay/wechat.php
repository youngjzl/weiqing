<?php
class wechat
{
    //公众账号ID
    private $appid;
    //商户号
    private $mch_id;
    //随机字符串
    private $nonce_str;
    //签名
    private $sign;
    //请求类型
    private $method;
    //商品描述
    private $de;
    //商户订单号
    private $out_trade_no;
    //支付总金额
    private $total_fee;
    //终端IP
    private $spbill_create_ip;
    //支付结果回调通知地址
    private $notify_url;
    //交易类型
    private $trade_type;
    //支付密钥
    private $serial_no;
    //证书路径
    private $SSLKEY_PATH;
    //所有参数
    private $params = array();

    /**
     * wechat constructor.
     * @param $appid string 商户appid
     * @param $mch_id string 商户id
     * @param $notify_url string 支付成功跳转链接
     * @param $serial_no string 商户api支付秘钥
     * @param $sslkey_path string 商户证书秘钥路径
     */
    public function __construct($appid, $mch_id, $notify_url, $serial_no,$sslkey_path)
    {
        $this->appid = $appid;
        $this->mch_id = $mch_id;
        $this->notify_url = $notify_url;
        $this->serial_no = $serial_no;
        $this->SSLKEY_PATH=$sslkey_path;
    }
    /**
     * 下单方法
     * @param array $params 下单参数
     */
    public function unifiedOrder( $params ){
        //微信支付链接
        $url='https://api.mch.weixin.qq.com/v3/pay/transactions/native';
        //商户id
        $mchid=$this->mch_id;
        //回调地址
        $notify_url=$this->notify_url;
        //商户appid
        $appid=$this->appid;
        //请求类型
        $this->method='POST';
        //body数据
        $this->params= array(
            'time_expire' => $params['time_expire'],
            'amount' => array(
                'total' => $params['money_total'],
                'currency' => 'CNY',
            ),
            'mchid' => $mchid,
            'description' => $params['des'],
            'notify_url' => $notify_url,
            'out_trade_no' => $params['order_no'],
            'goods_tag' => '',
            'appid' => $appid,
            'attach' => '',
            'detail' => array(
                'invoice_id' => '',
                'goods_detail' => $params['goods_detail'],
                'cost_price' => $params['original_price'],
            ),
            'scene_info' => $params['scene_info']
        );
        //拿到header
        $header=$this->getHeader($url);
        $body=json_encode($this->params);
        //发送请求
        $response=$this->curlPost($url,$body,$this->method,$header);
        $response=json_decode($response,true);
        if( !$response ){
            return false;
        }
        $result =  $response ;
        if( !empty($result['result_code']) && !empty($result['err_code']) ){
            $result['err_msg'] = $this->error_code( $result['err_code'] );
        }
        return $result;
    }

    /**
     * 查询订单信息
     * @param $out_trade_no 订单号
     * @return array
     */
    public function orderQuery( $out_trade_no ){
        $this->params['appid'] = $this->appid;
        $this->params['mch_id'] = $this->mch_id;
        $this->params['nonce_str'] = $this->genRandomString();
        $this->params['out_trade_no'] = $out_trade_no;
        //获取签名数据
        $this->sign = $this->MakeSign( $this->params );
        $this->params['sign'] = $this->sign;
        $xml = $this->data_to_xml($this->params);
        $response = $this->postXmlCurl($xml, self::API_URL_PREFIX.self::ORDERQUERY_URL);
        if( !$response ){
            return false;
        }
        $result = $this->xml_to_data( $response );
        if( !empty($result['result_code']) && !empty($result['err_code']) ){
            $result['err_msg'] = $this->error_code( $result['err_code'] );
        }
        return $result;
    }

    /**
     * 关闭订单
     * @param $out_trade_no 订单号
     * @return array
     */
    public function closeOrder( $out_trade_no ){
        $this->params['appid'] = $this->appid;
        $this->params['mch_id'] = $this->mch_id;
        $this->params['nonce_str'] = $this->genRandomString();
        $this->params['out_trade_no'] = $out_trade_no;
        //获取签名数据
        $this->sign = $this->MakeSign( $this->params );
        $this->params['sign'] = $this->sign;
        $xml = $this->data_to_xml($this->params);
        $response = $this->postXmlCurl($xml, self::API_URL_PREFIX.self::CLOSEORDER_URL);
        if( !$response ){
            return false;
        }
        $result = $this->xml_to_data( $response );
        return $result;
    }

    /**
     * 获取支付结果通知数据
     * return array
     */
    public function getNotifyData(){
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $data = array();
        if( empty($xml) ){
            return false;
        }
        $data = $this->xml_to_data( $xml );
        if( !empty($data['return_code']) ){
            if( $data['return_code'] == 'FAIL' ){
                return false;
            }
        }
        return $data;
    }

    //获取随机数
    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }


    /**
     * 得到header数组
     * @return string[]
     */
    public function getHeader($url)
    {
        if (!in_array('sha256WithRSAEncryption', \openssl_get_md_methods(true))) {
            throw new \RuntimeException("当前PHP环境不支持SHA256withRSA");
        }
        $url_parts = parse_url($url);
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));

        //私钥地址
//        $mch_private_key = 'E:\php\phpstudy_pro\WWW\www.weiqin.com\web\apiclient_key.pem';
        $mch_private_key = $this->SSLKEY_PATH;
        //商户号
        $merchant_id = $this->mch_id;
        //商户支付api秘钥
        $serial_no = $this->serial_no;
        //当前时间戳
        $timestamp = time();
        //随机字符串
        $nonce = $this->createNoncestr();
        //POST请求时
        $body='';
        if ($this->method=='POST'){
            $body = json_encode($this->params,320);
        }
        $message = "$this->method\n" .
            $canonical_url . "\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            $body . "\n";
        //生成签名
        openssl_sign($message, $raw_sign, openssl_get_privatekey(file_get_contents($mch_private_key)), 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        //Authorization 类型
        $schema = 'WECHATPAY2-SHA256-RSA2048';
        //生成token
        $token = sprintf('mchid="%s",serial_no="%s",nonce_str="%s",timestamp="%d",signature="%s"', $merchant_id, $serial_no, $nonce, $timestamp, $sign);

        $header = [
            'Content-Type:application/json',
            'Accept:application/json',
            'User-Agent:'.$merchant_id,
            'Authorization: '.$schema .' '. $token
        ];
        return $header;
    }

    //获取当前服务器ip
    public function get_client_ip(){
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv('REMOTE_ADDR')) {
            $cip = getenv('REMOTE_ADDR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $cip = getenv('HTTP_CLIENT_IP');
        } else {
            $cip = 'unknown';
        }
        return $cip;
    }

    /**
     * 获取毫秒级别的时间戳
     */
    private static function getMillisecond(){
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }

    //发送微信支付请求
    public function curlPost($url = '', $postData = '', $method = 'GET',$headers=array())
    {
//
        $ch = curl_init();
        $curl = curl_init();
        // 设置curl允许执行的最长秒数

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        }

        // 执行操作
        $result = curl_exec($curl);

        curl_close($curl);
        return $result;
    }

    /**
     * 错误代码
     * @param $code 服务器输出的错误代码
     * return string
     */
    public function error_code( $code ){
        $errList = array(
            'NOAUTH' => '商户未开通此接口权限',
            'NOTENOUGH' => '用户帐号余额不足',
            'ORDERNOTEXIST' => '订单号不存在',
            'ORDERPAID' => '商户订单已支付，无需重复操作',
            'ORDERCLOSED' => '当前订单已关闭，无法支付',
            'SYSTEMERROR' => '系统错误!系统超时',
            'APPID_NOT_EXIST' => '参数中缺少APPID',
            'MCHID_NOT_EXIST' => '参数中缺少MCHID',
            'APPID_MCHID_NOT_MATCH' => 'appid和mch_id不匹配',
            'LACK_PARAMS' => '缺少必要的请求参数',
            'OUT_TRADE_NO_USED' => '同一笔交易不能多次提交',
            'SIGNERROR' => '参数签名结果不正确',
            'XML_FORMAT_ERROR' => 'XML格式错误',
            'REQUIRE_POST_METHOD' => '未使用post传递参数 ',
            'POST_DATA_EMPTY' => 'post数据不能为空',
            'NOT_UTF8' => '未使用指定编码格式',
        );
        if( array_key_exists( $code , $errList ) ){
            return $errList[$code];
        }
    }
}