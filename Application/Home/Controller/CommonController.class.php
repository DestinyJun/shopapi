<?php
namespace Home\Controller;
use Think\Controller;

abstract class CommonController extends Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->getClientIp();
    $this->checkFormId();
  }
  // 检查加密的密钥是否对应，否则拒接客户端请求访问
  public function checkFormId() {
    $formid = I('request.formid');
    if (!$formid) {
      $this->ajaxReturn(array('status'=>1001,'msg'=>'没有访问权限'));
    }
    $formid = authcode(base64_decode($formid),'DECODE');
    if ($formid != 'pc') {
      $this->ajaxReturn(array('status'=>1001,'msg'=>'没有访问权限'));
    }
  }
  public function getClientIp() {
    // 获取客户端的IP地址
    $ip = get_client_ip();
    // 限制允访问的IP
    $allow = array('127.0.0.1','localhost');
    // 根据请求的IP地址检查是否允许访问
    if (!in_array($ip,$allow)) {
      $this->ajaxReturn(array('status'=>1001,'msg'=>'没有访问权限'));
    }
  }
}
