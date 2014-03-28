<?php

/**
 * 框架扩展：数据库操作类库
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-27
 * */


/**
    使用方法：
    在配置里添加如下配置参数
    =====================================================
    [ext]
    view = 1   ;开启本扩展

    [view]     ;本扩展基本配置
    ;模板路径
    themespath = {APPLICATION_PATH}/www/themes/default
    ;缓存方式
    cachemode = file
    ;文本缓存，缓存生命周期
    file.lifetime = 3600
    ;缓存总开关
    file.caching = false
    ;文本缓存，编译缓存开关
    file.compileCaching = true
    ;文本缓存，静态缓存开关
    file.htmlCaching = true
    ;缓存目录
    file.tempPath = /tmp
    =====================================================
 */

require __DIR__ . DIRECTORY_SEPARATOR . 'FileSmarty.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'SmartyException.php';

function ext_view_init(UnPHP $app, UnPHP_Dispatcher $dispatcher) {
    $conf = $app->getConfig();
    if (isset($conf['view'])) {
        $c = $conf['view'];
        if (isset($c['themespath']) && isset($c['cachemode'])) {
            $themesPath = $c['themespath'];
            $cachemodeList = explode(",", $c['cachemode']);
            foreach ($cachemodeList as $mode) {
                if (isset($c[$mode])) {
                    $modeConf = $c[$mode];
                    if (isset($modeConf['tempPath'])) {
                        $newSmarty = "Ext_View_".ucfirst($mode)."Smarty";
                        $view = new $newSmarty();
                        if ($view->init($modeConf)) {
                            $view->setScriptPath($themesPath);
                            $dispatcher->setView($view);
                            break;
                        }
                    }
                }
            }
        }
    }
}
