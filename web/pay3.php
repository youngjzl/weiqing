<?php


class pay3
{
    const APPID = 'wx6c8cb2ea0ae80e33';
    const MCHID = '1557049901';
    const KEY = 'w0rVKVP7KeVyJl3RRhxzK0UoM0AvDlWM';
    const APIXL = '171BE588FCD2C72FC8206D792505034E844CDEF7';
    const NOTIFYURL = 'http://kefu.lianhebfang.com/api/index.php';//支付成功异步回调地址

    public function createAuthorization($url)
    {
        if (!in_array('sha256WithRSAEncryption', \openssl_get_md_methods(true))) {
            throw new \RuntimeException("当前PHP环境不支持SHA256withRSA");
        }
        $url_parts = parse_url($url);
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));

        //私钥地址
        $mch_private_key = 'E:\php\phpstudy_pro\WWW\www.weiqin.com\web\apiclient_key.pem';
        //商户号
        $merchant_id = self::MCHID;
        //当前时间戳
        $timestamp = time();
        //随机字符串
        $nonce = $this->createNoncestr();
        //POST请求时
        $body = array(
            'time_expire' => date(DATE_RFC3339,time()+3600),
            'amount' => array(
                'total' => 100,
                'currency' => 'CNY',
            ),
            'mchid' => self::MCHID,
            'description' => 'Image形象店-深圳腾大-QQ公仔',
            'notify_url' => self::NOTIFYURL,
            'out_trade_no' => 'wx_1217752501201407',
            'goods_tag' => 'WXG',
            'appid' => self::APPID,
            'attach' => '自定义数据说明',
            'detail' => array(
                'invoice_id' => 'wx123',
                'goods_detail' => array(
                    array(
                        'goods_name' => 'iPhoneX 256G',
                        'wechatpay_goods_id' => '1001',
                        'quantity' => 1,
                        'merchant_goods_id' => 'apple3203',
                        'unit_price' => 828800,
                    ),
                    array(
                        'goods_name' => 'iPhoneX 256G',
                        'wechatpay_goods_id' => '1002',
                        'quantity' => 1,
                        'merchant_goods_id' => 'apple3204',
                        'unit_price' => 828800,
                    ),
                ),
                'cost_price' => 608800,
            ),
            'scene_info' => array(
                'store_info'=>array(
                    'address' => '广东省深圳市南山区科技中一道10000号',
                    'area_code' => '440305',
                    'name' => '腾讯大厦分店',
                    'id' => '0001',
                ),
                'device_id'=>'013467007045764',
                'payer_client_ip'=>$this->get_client_ip()
            ),
        );
        $body = json_encode($body,320);
        $message = "POST\n" .
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
        $token = sprintf('mchid="%s",serial_no="%s",nonce_str="%s",timestamp="%d",signature="%s"', $merchant_id, self::APIXL, $nonce, $timestamp, $sign);

        $header = [
            'Content-Type:application/json',
            'Accept:application/json',
            'User-Agent:'.self::MCHID,
            'Authorization: '.$schema .' '. $token
        ];
        $header['abcd']=$body;
        return $header;
    }

    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
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

    //生成微信支付二维码
    public function wePayQRCode($url)
    {
//        include '../extend/wxPay/example/phpqrcode/phpqrcode.php';
        $value = $url;//二维码内容
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 5;//生成图片大小
        //生成二维码图片
//        $qrcode = m('qrcode')->createQrcode($value);
        var_dump($url);
        die;
        try {
            $filename = 'qrcode/' . 'local' . time() . '.png';
            \QRcode::png($value, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
            $QR = $filename;//已经生成的原始二维码图片文件
            $QR = imagecreatefromstring(file_get_contents($QR));
            //输出图片
            imagepng($QR, 'qrcode/' . 'qrcode.png');
            imagedestroy($QR);
            var_dump($filename);
            die;
            return $filename;
        } catch (Exception $exception) {
            Checking::response($exception->getCode(), $exception->getMessage());
        }

    }
}

//$wx = new pay();
//$money = 1;
//$order_no = 'wx123213213';
//$payUrl = $wx->wxNativePay($money, $order_no);
$wx =new pay3();
$url='https://api.mch.weixin.qq.com/v3/pay/transactions/native';
$header=$wx ->createAuthorization($url);
$body=$header['abcd'];
unset($header['abcd']);
echo '<pre>';var_dump($header);
$res=$wx->curlPost($url,$body,'POST',$header);
var_dump($res);
die;