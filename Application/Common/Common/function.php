<?php
// 定义项目使用的公共函数，此文件下的函数会自动载入到项目中，并且所有函数可以在项目的任何模块下使用
/**
 * 通过自定义函数生成接口地址配个http_curl访问API接口获取数据
 * @param array $data 请求的接口参数
 * @param string $method 请求方式
 * @return mixed
 */
function get_data($data=array(),$method='get') {
  // 根据当前的情况生成具体的URL地址
  // 如果需要指定具体的接口地址，可以在$data中增加两个参数c代表控制器，a达标方法名
  if(!$data['c']) {
    // 说名没有指定具体的控制器名称
    $data['c'] = CONTROLLER_NAME; // 没有指定就默认设置为当前项目中的控制器名称
  }
  if(!$data['a']) {
    // 说名没有指定具体的方法名称
    $data['a'] = ACTION_NAME; // 没有指定就默认设置为当前项目中的方法名称
  }
  // 需要考虑如果不是访问自己项目中的接口而是第三方的接口时
  if($data['url']) {
    // 访问第三方的接口
    $url = $data['url'];
  } else {
    // 访问自己本项目的接口
    $url = "http://www.shopapi.com/index.php?m=Home&c={$data['c']}&a={$data['a']}";
  }
  // 在具体调用curl请求数据之前，需要去掉不需要的参数c，a，url
  unset($data['c']);
  unset($data['a']);
  unset($data['url']);
  return http_curl($url,$data,$method); // 请求接口
}

/**
 * 封装curl请求
 * @param string $url 请求的接口地址
 * @param array $data 请求的接口参数
 * @param string $method 请求方式
 * @return mixed
 */
function http_curl($url='',$data=array(),$method='get') {
  if (!function_exists('curl_init')) {
    echo 'curl扩展没有开启！'; exit();
  }
  /* 使用curl的相关步骤 */
  // 1、打开会话（可以理解为MySQL建立连接，初始化curl会话）
  $ch = curl_init();
  // 2、增加一个加密之后的参数信息，限制接口访问，提高接口安全
  $data['formid'] = base64_encode(authcode('pc','ENCODE'));
  // 3、设置参数信息，需要指定具体的请求地址、参数以及具体的请求方式
  if ($method == 'post') {
    // post请求
    curl_setopt($ch,CURLOPT_POST,true); // 默认请求方式时get,这里设置为post
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data); // 设置具体的请求参数及请求方式，如请求头等
  } else {
    // get请求拼接请求url
    $url .= '&'.http_build_query($data);
  }
  curl_setopt($ch,CURLOPT_URL,$url); // 设置请求地址
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,$url); // 设置获取到的信息以文件流的形式返回，而不是直接输出
  // 4、执行具体的请求操作
  $res = curl_exec($ch);
  // 5、关闭会话
  curl_close($ch);
  // 6、将具体请求到的数据转化为PHP的数组格式并返回
  return json_decode($res,true);
}

/**
 * 生成商品列表中的链接地址函数
 * @param string $name  排序方式
 * @param string $value 需要排序字段
 * @return string
 */
function myU($name, $value)
{
  $attr = I('get.attr');
  if ($name == 'sort') {
    // 将排序字段保存到$sort中
    $sort = $value;
    $price = I('get.price');
  } elseif ($name == 'price') {
    $price = $value;
    $sort = I('get.sort');
  } elseif ($name == 'attr') {
    // 这里需要注意：实现多个属性值进行筛选查询的情况
    if (!$attr) {
      $attr = $value;
    } else {
      $attr = explode(',', $attr);
      $attr[] = $value;
      $attr = array_unique($attr);
      $attr = implode(',', $attr);
    }
  }
  return U("Category/index") . '?id=' . I('get.id') . '&sort=' . $sort . 'price=' . $price . 'attr=' . $attr;
}

/**
 * 发送模板短信
 * @param string to 手机号码集合,用英文逗号分开
 * @param array datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
 * @param string $tempId 模板Id
 * @return bool
 */
function sendTemplateSMS($to = "18798723901", $datas = array('2345', '60'), $tempId = "1")
{
  include_once("./sms/CCPRestSDK.php");

//主帐号
  $accountSid = '8aaf07086e0115bb016e589512f42ec9';

//主帐号Token
  $accountToken = 'bff682f0aa904315bcac86d47c63db5a';

//应用Id
  $appId = '8a216da86e011fa3016e5994ed343027';

//请求地址，格式如下，不需要写https://
// 生产环境：app.cloopen.com
// 测试环境：sandboxapp.cloopen.com
  $serverIP = 'sandboxapp.cloopen.com';

//请求端口
  $serverPort = '8883';

//REST版本号
  $softVersion = '2013-12-26';
  // 初始化REST SDK
  $rest = new \REST($serverIP, $serverPort, $softVersion);
  $rest->setAccount($accountSid, $accountToken);
  $rest->setAppId($appId);

  // 发送模板短信
  $result = $rest->sendTemplateSMS($to, $datas, $tempId);
  if ($result == NULL) {
    return false;
  }
  if ($result->statusCode != 0) {
    //TODO 添加错误处理逻辑
    echo "error code :" . $result->statusCode . "<br>";
    echo "error msg :" . $result->statusMsg . "<br>";
    return false;
  }
  return true;
}

/**
 * 邮箱认证发送
 * @param string $email
 * @param string $subject
 * @param string $body
 * @return bool
 */
function sendEmail($email, $subject, $body)
{
  include_once './PHPMailer/Exception.php';
  include_once './PHPMailer/SMTP.php';
  include_once './PHPMailer/PHPMailer.php';
  $mail = new PHPMailer\PHPMailer\PHPMailer();
  /* 服务器配置相关信息*/
  $mail->isSMTP(); // 使用smtp方式发生邮件
  $mail->SMTPAuth = true; // 使用用户信息认证
  $mail->Host = 'smtp.163.com'; // 设置发邮件的smtp服务器地址
  $mail->Username = 'wwjwxm0858'; // 发件邮箱的用户名
  $mail->Password = 'lps5815081'; // 发件邮箱的POP3/SMTP/IMAP授权码

  /* 发送的邮件内容信息 */
  $mail->isHTML(true); // 使用html文本格式
  $mail->CharSet = 'UTF-8'; // 内容字符集
  $mail->From = 'wwjwxm0858@163.com'; // 发件人邮件地址
  $mail->FromName = '文君电商'; // 发件人昵称
  $mail->Subject = $subject; // 邮件主题
  $mail->msgHTML($body); // 邮件正文
  $mail->addAddress($email); // 收件人
  $mail->addAttachment('./Uploads/goods/2019-10-24/5db1c4bb3a571.jpg'); // 追加附件
  $res = $mail->send();
  return $res;
}

/**
 * 实现字符串的加密或者解密操作
 * @param string $string 需要加密或者解密的字符串
 * @param string $operation 代表是具体的加密还是解密操作 DECODE解密操作操作  其他为加密操作（一般设置为ENCODE）
 * @param string $key 代表的是具体加密解密的密钥，如果针对某一个字符串进行加密后，在解密时此密钥不对应会导致密文不能正常被解析，对于加密或者解密均设置为同一个密钥
 * @param int $expiry 代表具体密文的有效时间，如果设置了具体的秒数，则表示该密文具备有效时间，超过有效时间，密文就不能被正常的反解出来
 * @return bool|string 具体的返回值
 * 加密后的字符串用base64_encode函数将具体的加密后的字符串转换一下，是为了确保加密后的字符串不会影响具体的参数
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
  // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
  $ckey_length = 4;

  // 密匙
  $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);

  // 密匙a会参与加解密
  $keya = md5(substr($key, 0, 16));
  // 密匙b会用来做数据完整性验证
  $keyb = md5(substr($key, 16, 16));
  // 密匙c用于变化生成的密文
  $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length):
    substr(md5(microtime()), -$ckey_length)) : '';
  // 参与运算的密匙
  $cryptkey = $keya.md5($keya.$keyc);
  $key_length = strlen($cryptkey);
  // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
  //解密时会通过这个密匙验证数据完整性
  // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
  $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
    sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
  $string_length = strlen($string);
  $result = '';
  $box = range(0, 255);
  $rndkey = array();
  // 产生密匙簿
  for($i = 0; $i <= 255; $i++) {
    $rndkey[$i] = ord($cryptkey[$i % $key_length]);
  }
  // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
  for($j = $i = 0; $i < 256; $i++) {
    $j = ($j + $box[$i] + $rndkey[$i]) % 256;
    $tmp = $box[$i];
    $box[$i] = $box[$j];
    $box[$j] = $tmp;
  }
  // 核心加解密部分
  for($a = $j = $i = 0; $i < $string_length; $i++) {
    $a = ($a + 1) % 256;
    $j = ($j + $box[$a]) % 256;
    $tmp = $box[$a];
    $box[$a] = $box[$j];
    $box[$j] = $tmp;
    // 从密匙簿得出密匙进行异或，再转成字符
    $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
  }
  if($operation == 'DECODE') {
    // 验证数据有效性，请看未加密明文的格式
    if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
      substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
      return substr($result, 26);
    } else {
      return '';
    }
  } else {
    // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
    // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
    return $keyc.str_replace('=', '', base64_encode($result));
  }
}
