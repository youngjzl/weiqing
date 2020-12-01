<?php
require EWEI_SHOPV2_PLUGIN . 'supplychain/core/inc/function.php';
class Register_EweiShopV2Page extends Page
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $reg = pdo_fetch('select * from ' . tablename('ewei_shop_supplychain_reg') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
        $user = false;
        if (!(empty($reg['status'])))
        {
            $user = pdo_fetch('select * from ' . tablename('ewei_shop_supplychain_user') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
        }
        if (!(empty($user)) && (1 <= $user['status']))
        {
            $this->message('您已经申请，无需重复申请!', '', 'error');
        }
        if ($_W['isajax']){
            echo '<pre>';
            $cardImage1=$_FILES['cardImage1'];
            $cardImage2=$_FILES['cardImage2'];
            $supplyLogo=$_FILES['supplyLogo'];
            if (empty($cardImage1)&&empty($cardImage2)&&empty($supplyLogo)){
                show_json(0,'请上传图片');
            }
            $supplyName=$_GPC['supplyName'];
            $supplyDesc=$_GPC['supplyDesc'];
            $realName=$_GPC['realname'];
            $qq=$_GPC['qq'];
            $email=$_GPC['email'];
            $mobile=$_GPC['mobile'];
            $cardNum=$_GPC['cardNum'];
            $companyName=$_GPC['companyName'];
            $selectAddress=$_GPC['selectAddress'];
            $companyAddress=$_GPC['companyAddress'];
            if (empty($supplyName)&&empty($realName)&&empty($email)&&empty($mobile)&&empty($cardNum)&&empty($companyName)&&empty($selectAddress)&&empty($companyAddress)){
                show_json(0,'请补充填写页面必填项');
            }
            die;
        }
        include $this->template('supplychain/register');
    }
}
?>