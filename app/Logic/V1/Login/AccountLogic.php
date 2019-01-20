<?php
/**
 * Created by PhpStorm.
 * User: chen
 * Date: 2018/12/8
 * Time: 21:14
 */

namespace App\Logic\V1\Login;


use App\Logic\Exception;
use App\Logic\LoadDataLogic;
use App\Model\V1\User\UserAccountModel;
use App\Model\V1\User\UserBaseModel;

class AccountLogic extends LoadDataLogic
{
    protected $account = '';

    protected $password = '';

    /**
     * 登录
     * @throws Exception
     */
    public function login(){
        $userAccountModel = (new UserAccountModel())->where('account',$this->account)->first();
        if (empty($userAccountModel)){
            throw new Exception('用户账号不存在', 'USER_NOT_FIND');
        }
        $userBaseModel = (new UserBaseModel())->where('uid',$userAccountModel->uid)->first();
        if ($userBaseModel->state <> UserBaseModel::ACCOUNT_START_ENABLE){
            throw new Exception('账号异常,请联系管理员', 'ACCOUNT_ABNORMAL_ERROR');
        }
        // 判断传进来的密码跟基本信息表中的密码是否相等
        if (md5($this->password)!== $userBaseModel->password){
            throw new Exception('密码错误,请重试！', 'USER_PASSWORD_ERROR');
        }
        return $userBaseModel->toHump();
    }
}