<?php
require EWEI_SHOPV2_PLUGIN . 'supplychain/core/inc/function.php';
class Register_EweiShopV2Page extends Page
{
    public function main()
    {
        global $_W;
        global $_GPC;
        if ($_W['isajax']){
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
            $companyAddress=$_GPC['companyAddress'];
            $loginname=trim($_GPC['loginName']);
            $loginpwd=trim($_GPC['loginpwd']);
            $loginpwd2=trim($_GPC['loginpwd2']);
            $regTime=time();
            if (empty($supplyName)&&empty($realName)&&empty($loginname)&&empty($loginpwd)&&empty($email)&&empty($mobile)&&empty($cardNum)&&empty($companyName)&&empty($companyAddress)){
                show_json(0,'请补充填写页面必填项');
            }
            if ($loginpwd!=$loginpwd2){
                show_json(0,'两次密码不一致');
            }
            if (empty($_GPC['userProvinceId'])||empty($_GPC['userCityId'])||empty($_GPC['userDistrictId'])){
                show_json(0,'区域选择请补充完整');
            }
            $citdata_json=json_decode(html_entity_decode($_GPC['cityjson']),true);
            $citdata='';
            foreach ($citdata_json as $item){
                $citdata.=$item['name'].'-';
            }
            $citdata=substr($citdata, 0, -1);
            if ($this->retunloginname($loginname)){
                $data=array(
                    'uniacid'=>2,
                    'supplyName'=>$supplyName,
                    'supplyDesc'=>$supplyDesc,
                    'realName'=>$realName,
                    'qq'=>$qq,
                    'email'=>$email,
                    'mobile'=>$mobile,
                    'cardNum'=>$cardNum,
                    'companyName'=>$companyName,
                    'selectAddress'=>$citdata,
                    'companyAddress'=>$companyAddress,
                    'loginname'=>trim($loginname),
                    'regTime'=>$regTime,
                );
                $pwd=md5(md5('supplychain'.trim($loginpwd).$cardNum));
                $data['pwd']=$pwd;
                //上传图片
                if (!$cardImage1=$this->UploadImage($cardImage1)){
                    show_json(0,'身份证正面上传失败');
                }
                $data['cardImage1']=$cardImage1;
                if (!$cardImage2=$this->UploadImage($cardImage2)){
                    show_json(0,'身份证反面上传失败');
                }
                $data['cardImage2']=$cardImage2;
                if (!$supplyLogo=$this->UploadImage($supplyLogo)){
                    show_json(0,'商户logo上传失败');
                }
                $data['supplyLogo']=$supplyLogo;
                $id=pdo_insert('ewei_shop_supplychain_reg', $data);
                if (empty($id)){
                    //添加日志进去
                    show_json(0,'申请失败');
                }
                show_json(1,'申请成功，等待审核');
            }
            show_json(0,'登录名账户已经存在了，请换一个');
        }
        include $this->template('supplychain/register');
    }
    protected function UploadImage($file, $type = 'image')
    {
        $path=EWEI_SHOPV2_PLUGIN . 'supplychain/static/img/regimgs/';
        $maxsize=10000;
        //判断该路径是否存在是否可写
        if ($this->check($path)) {
            //判断文件的大小、mime、后缀是否符合
            if ($file['size']>$maxsize || ($file['type']!='image/png' && $file['type']!='image/jpg' && $file['type']!='image/jpeg')) {
                return false;
            }
            //得到新的文件名字
            $imgName =time().$file['name'];
            //判断是否是上传文件，并且是移动上传文件
            $tmp_name=$file['tmp_name'];
            if (is_uploaded_file($tmp_name)) {
                if (move_uploaded_file($tmp_name, $path . $imgName)) {
                    return $imgName;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return false;
    }
    /**
     * 检测文件夹是否存在，是否可写
     * @return bool
     */
    protected function check($path)
    {

        //文件夹不存在或者不是目录。创建文件夹
        if (!file_exists($path) || !is_dir($path)) {
            return mkdir($path, 0777, true);
        }

        //判断文件是否可写
        if (!is_writeable($path)) {
            return chmod($path, 0777);
        }
        return true;
    }

    public function retunloginname($loginname){
        global $_GPC;
        global $_W;
        $reg = pdo_fetch('select * from ' . tablename('ewei_shop_supplychain_reg') . ' where loginname=:loginname and uniacid=:uniacid limit 1', array(':loginname' => $_GPC['loginname'], ':uniacid' => $_W['uniacid']));
        if (empty($reg)){
            return true;
        }
        return false;
    }
}
?>