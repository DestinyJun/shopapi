<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 只要是开发阶段开启，设置为true 部署阶段注释或者设为false
define('APP_DEBUG',true);

// 定义目录分隔符
define('DS',DIRECTORY_SEPARATOR);

// 项目根物理路径
define('PROJECT_ROOT',str_replace('/','\\',$_SERVER['DOCUMENT_ROOT']).DS);

// 定义应用存储目录
define('APP_PATH','./Application'.DS);

// 设置应用编码
header('content-type:text/html;charset=utf-8');

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单
