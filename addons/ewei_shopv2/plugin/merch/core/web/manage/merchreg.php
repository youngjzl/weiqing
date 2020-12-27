<?php
require EWEI_SHOPV2_PLUGIN . 'merch/core/inc/function.php';
class Merchreg_EweiShopV2Page extends Page
{

    function main() {
        global $_W, $_GPC;
        include $this->template('merch/manage/reg');
    }

    /**
     * 注册信息提交
     *
     */
    public function regiinfo()
    {
        global $_W;
        global $_GPC;
        $data = $this->rf(0);
        return json_encode($data);
    }

    /**
     * 注册和找回密码逻辑
     * @param $type
     * @return array
     */
    protected function rf($type)
    {
        global $_W;
        global $_GPC;
        $_W["uniacid"]=2;
        if (!empty($_GPC["__ewei_shopv2_merch_session_" . $_W["uniacid"]])) {
            show_json(1, "您已登陆过,请勿重新登录");
        }
        if ($_W["isajax"]) {
            $mobile = trim($_GPC["mobile"]);
            $yzm_code_text = trim($_GPC["yzm_code_text"]);
            $pwd = trim($_GPC["pwd"]);
            $code = trim($_GPC["code"]);
            if (empty($mobile)) {
                show_json(0, "请输入正确的手机号");
            }
            if (empty($yzm_code_text)) {
                show_json(0, "请输入验证码");
            }
            if (empty($pwd)) {
                show_json(0, "请输入密码");
            }
            if(empty($code)) {
                show_json(0, "请输入邀请码");
            }
            $key = "__ewei_shopv2_merch_verifycodesession_" . $_W["uniacid"] . "_" . $mobile;
            if (!isset($_SESSION[$key]) || $_SESSION[$key] !== $yzm_code_text || !isset($_SESSION["verifycodesendtime"]) || $_SESSION["verifycodesendtime"] + 600 < time()) {
                show_json(0, "验证码错误或已过期!");
            }
            $merch_user = pdo_fetch("select id,mobile from " . tablename("ewei_shop_merch_user") . " where mobile=:mobile and uniacid=:uniacid limit 1", array(":mobile" => $mobile, ":uniacid" => $_W["uniacid"]));
            if (empty($type)) {
                if (!empty($merch_user)) {
                    show_json(0, "此手机号已注册, 请直接登录");
                }
                $time=time();
                $upass = m('util')->pwd_encrypt($pwd, 'E');
                $salt=random(8);
                $pwd=md5(trim($pwd) . $salt);

                $pluginset=array(
                    'creditshop'=>array('close'=>0),
                    'quick'=>array('close'=>1),
                    'taobao'=>array('close'=>1),
                    'exhelper'=>array('close'=>1),
                    'diypage'=>array('close'=>1),
                );
                $data = array(
                    "uniacid" => $_W["uniacid"],
                    "regid" =>0,
                    "groupid"=>6,
                    "code"=>$code,
                    "mobile" => $mobile,
                    "merchname" => $mobile,
                    "salecate"=>'未知',
                    "status" => 1,
                    "desc" => '商户描述',
                    "realname" => $mobile,
                    "accounttime" => $time,
                    "jointime" => $time,
                    "uname"=>$mobile,
                    "upass"=>$upass,
                    "iscredit"=>1,
                    "iscreditmoney"=>1,
                    "creditrate"=>0,
                    "pluginset"=>serialize($pluginset),
                );
                pdo_insert('ewei_shop_merch_user',$data);
                $id = pdo_insertid();
                if ($id){
                    //主帐号信息
                    $account = array(
                        'uniacid' => $_W['uniacid'],
                        'merchid' => $id,
                        'username' => $mobile,
                        'pwd' => $pwd,
                        'salt' => $salt,
                        'status' => 1,
                        'perms' => serialize(array()),
                        'isfounder' => 1
                    );
                    //主帐号
                    pdo_insert('ewei_shop_merch_account', $account);
                    $accountid = pdo_insertid();
                    pdo_update('ewei_shop_merch_user', array('accountid' => $accountid), array('id' => $id));

                    show_json(1, '注册成功');
                }else{
                    show_json(0, '注册失败');
                }
            }
            show_json(0, '注册失败');
        }
    }

    public function verifycode()
    {
        global $_W;
        global $_GPC;
        $_W['uniacid']=2;
        @session_start();
        $set = pdo_fetch("select * from " . tablename("ewei_shop_sysset").'where uniacid='.$_W['uniacid']);
        $set = unserialize($set['sets']);
        $mobile = trim($_GPC["mobile"]);
        $temp = trim($_GPC["temp"]);
        if (empty($mobile)) {
            show_json(0, "请输入手机号");
        }
        if (empty($temp)) {
            show_json(0, "参数错误");
        }
        if (!empty($_SESSION["verifycodesendtime"]) && time() < $_SESSION["verifycodesendtime"] + 60) {
            show_json(0, "请求频繁请稍后重试");
        }
        $member = pdo_fetch("select id,openid,mobile from " . tablename("ewei_shop_merch_user") . " where mobile=:mobile and uniacid=:uniacid limit 1", array(":mobile" => $mobile, ":uniacid" => $_W["uniacid"]));
        if ($temp == "sms_reg" && !empty($member)) {
            show_json(0, "此手机号已注册，请直接登录");
        }

        $sms_id = $set["wap"][$temp];
        if (empty($sms_id)) {
            show_json(0, "短信发送失败(NOSMSID)");
        }
        $key = "__ewei_shopv2_merch_verifycodesession_" . $_W["uniacid"] . "_" . $mobile;
        @session_start();
        $code = random(5, true);
        $shopname = $_W["shopset"]["shop"]["name"];
        $ret = array("status" => 0, "message" => "发送失败");
        if (com("sms")) {
            $ret = com("sms")->send($mobile, $sms_id, array("验证码" => $code, "商城名称" => !empty($shopname) ? $shopname : "商城名称"));
        }
        if ($ret["status"]) {
            $_SESSION[$key] = $code;
            $_SESSION["verifycodesendtime"] = time();
            show_json(1, "短信发送成功");
        }
        show_json(0, $ret["message"]);
    }
}
