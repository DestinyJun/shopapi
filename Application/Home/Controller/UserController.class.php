<?php
namespace Home\Controller;

class UserController extends CommonController
{
  // 设计一个登陆接口
  // 注意：对于接口，不能有类似项目中的登陆验证
  public function login() {
    $username = I('get.username');
    $password = I('get.password');
    if (!$username || !$password) {
      $this->ajaxReturn(array(
        'status'=>1001,
        'msg'=> '参数错误！',
      ));
    }
    $model = M('client');
    $info = $model->where("username='{$username}'")->find();
    if (!$info) {
      $this->ajaxReturn(array(
        'status'=>1002,
        'msg'=> '用户名或密码错误！',
      ));
    }
    if ($info['status'] != 1) {
      $this->ajaxReturn(array(
        'status'=>1003,
        'msg'=> '该用户名未激活！',
      ));
    }
    $db_password = md5(md5($password).$info['salt']);
    if ($db_password != $info['password']) {
      $this->ajaxReturn(array(
        'status'=>1002,
        'msg'=> '用户名或密码错误！',
      ));
    }
    unset($info['password']);
    $this->ajaxReturn(array(
      'status'=>1000,
      'msg'=> '登陆成功！',
      'data'=>$info
    ));
  }
}
