<?php

/**
 * 应用入口文件
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
define('APPLICATION_PATH', __DIR__);
// 载入框架入口文件
require APPLICATION_PATH . '/UnPHP/UnPHP.php';
// 设置公共库路径
UnPHP::setLibrary(APPLICATION_PATH . '/library');
// 创建（框架）应用
$UnPHP = new UnPHP(APPLICATION_PATH . '/conf/common.ini');
// 初始化应用，并执行应用
$UnPHP->bootstrap()->run();

