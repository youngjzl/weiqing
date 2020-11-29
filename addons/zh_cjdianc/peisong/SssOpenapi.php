<?php

/**

 * 闪时送开放平台接口调用工具类

 * 详情：签名，接口调用

 * 版本：1.0

 * 日期：2019-11-25

 * 说明：

 * 以下代码只是为了方便对接商户测试而提供的样例代码，对接商户可以根据自己的需求，按照技术文档编写，代码仅供参考。

 */



class SssOpenapi{

    

    /**

     * 闪时送门店id

     */

    private $sss_shop_id;

    /**

     * 门店绑定验证码

     */

    private $sss_shop_token;



    /**

     * api url地址

     */

    private $url;







    /**

     * 签名生成signature

     */

    public function getSign($data,$appSecret){

   /*     foreach ($param as $k => $v) {
            $data[$k] = $v;
        }*/
        ksort($data);
        $str = $appSecret;
        foreach ($data as $k => $v) {
            if ($v === false) $v = 'false';
            if ($v === true) $v = 'true';
            if (empty($v) && $v != 0) continue;
            $str .= $k . $v;
        }
        return strtolower(sha1($str));
    }

    /**
     * 发送请求,POST
     * @param $url 指定URL完整路径地址
     * @param $data 请求的数据
     */
    public function requestWithPost($url, $data){
        // json

        $headers = array(
            'Content-Type: application/json',
            );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }



   



}

