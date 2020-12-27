<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'supplychain/core/inc/page_supplychain.php';

class Wechatpay_EweiShopV2Page extends SupplychainWebPage
{
    function main() {
        global $_W;
        if ($_W['ispost']){
            $this->post();
        }
        $set=pdo_fetch('select wechatpay from'.tablename('ewei_shop_supplychain_sysset').'where supplychainid=:supplychainid',array(':supplychainid'=>$_W['supplychainid']));
        if (!empty($set)){
            $sec=unserialize($set['wechatpay']);
        }

        include $this->template();
    }

    private function post(){
        global $_W;
        global $_GPC;
        $supplychainid=$_W['supplychainid'];
        $uniacid=$_W['uniacid'];

        $sec['app_wechat']['appid'] = trim($_GPC['data']['app_wechat_appid']);
        if (empty($sec['app_wechat']['appid'])){
            show_json(0,'appid未填写');
        }
        if ($_FILES['app_wechat_cert_file']['name']) {
            $sec['app_wechat']['cert'] = $this->upload_cert('app_wechat_cert_file');
        }
        if ($_FILES['app_wechat_key_file']['name']) {
            $sec['app_wechat']['key'] = $this->upload_cert('app_wechat_key_file');
        }

        $sec['app_wechat']['appsecret'] = trim($_GPC['data']['app_wechat_appsecret']);
        $sec['app_wechat']['merchname'] = trim($_GPC['data']['app_wechat_merchname']);
        $sec['app_wechat']['merchid'] = trim($_GPC['data']['app_wechat_merchid']);
        $sec['app_wechat']['apikey'] = trim($_GPC['data']['app_wechat_apikey']);

        if (empty($sec['app_wechat']['cert'])){
            show_json(0,'CERT证书文件未上传');
        }if (empty($sec['app_wechat']['key'])){
            show_json(0,'KEY密钥文件');
        }if (empty($sec['app_wechat']['appsecret'])){
            show_json(0,'appsecret未填写');
        }if (empty($sec['app_wechat']['merchname'])){
            show_json(0,'商户名称未填写');
        }if (empty($sec['app_wechat']['merchid'])){
            show_json(0,'商户id未填写');
        }if (empty($sec['app_wechat']['apikey'])){
            show_json(0,'api秘钥未填写');
        }

        $wechatpay=pdo_fetch('select * from'.tablename('ewei_shop_supplychain_sysset').'where supplychainid=:supplychainid and uniacid=:uniacid',array(':supplychainid'=>$supplychainid,':uniacid'=>$uniacid));
        if (empty($wechatpay)){
            pdo_insert('ewei_shop_supplychain_sysset',array('uniacid'=> $_W['uniacid'],'wechatpay'=>iserializer($sec),'supplychainid'=> $_W['supplychainid']));
            $text='添加';
        }else{
            pdo_update('ewei_shop_supplychain_sysset', array('wechatpay'=>iserializer($sec)), array('uniacid' => $_W['uniacid'],'supplychainid'=> $_W['supplychainid']));
            $text='修改';
        }
        plog('sysset.payset.edit', $text.'系统设置-支付设置');
        show_json(1);
    }

    private function upload_cert($fileinput) {
        global $_GPC;
        $path = IA_ROOT . "/addons/ewei_shopv2/cert/wechat/" . trim($_GPC['data']['app_wechat_appid']);
        load()->func('file');
        if (mkdirs($path)) {
            $filename = $_FILES[$fileinput]['name'];
            $tmp_name = $_FILES[$fileinput]['tmp_name'];
            $file_size = $_FILES[$fileinput]['size'];
            if (!empty($filename) && !empty($tmp_name)) {
                $ext = strtolower(substr($filename, strrpos($filename, '.')));
                if ($ext != '.pem') {
                    $errinput = "";
                    if ($fileinput == 'weixin_cert_file') {
                        $errinput = "CERT文件格式错误";
                    } else if ($fileinput == 'weixin_key_file') {
                        $errinput = 'KEY文件格式错误';
                    }
                    show_json(0, $errinput . ',请重新上传!');
                }
                if ($file_size > 2097152) {
                    $errinput = '文件大小必须不超过2 MB';
                    show_json(0, $errinput . ',请重新上传!');
                }
                move_uploaded_file($tmp_name, $path . "/" . $filename);

                return file_get_contents($path . "/" . $filename);
            }
        }


        return "";
    }
}
