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
  databases = 1   ;开启本扩展

  [databases]     ;本扩展基本配置
  db1.dsn = "mysql:host=127.0.0.1;dbname=unphp"
  db1.user = root
  db1.password = suxun520
  db1.action = both
  db1.tables = cate
  =====================================================
 */

require __DIR__ . DIRECTORY_SEPARATOR . 'Pdo.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'Mongo.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'Pool.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'Model.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'Validation.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'DatabasesException.php';

function ext_databases_init(UnPHP $app, UnPHP_Dispatcher $dispatcher) {
    $conf = $app->getConfig();
    if (isset($conf['databases'])) {
        $c = $conf['databases'];
        $bothDB = array();
        $otherDB = array();
        foreach ($c as $d) {
            if (isset($d['dsn']) && isset($d['user']) && isset($d['password']) && isset($d['tables']) && isset($d['action'])) {

                switch ($d['action']) {
                    case 'both':
                        $bothDB[] = $d;
                        break;
                    case 'read':
                        $otherDB[] = $d;
                        break;
                    case 'write':
                        $otherDB[] = $d;
                        break;
                }
            }
        }
        foreach ($bothDB as $d) {

            $tables = explode(",", $d['tables']);
            $dsn = $d['dsn'];
            $user = $d['user'];
            $password = $d['password'];
            foreach ($tables as $table) {
                Ext_Databases_Model::regionPool($table, $dsn, $user, $password, Ext_Databases_Model::ACTION_BOTH);
            }
        }
        foreach ($otherDB as $d) {
            $action = $d['action'] == "read" ? Ext_Databases_Model::ACTION_READ : Ext_Databases_Model::ACTION_WRITE;
            $tables = explode(",", $d['tables']);
            $dsn = $d['dsn'];
            $user = $d['user'];
            $password = $d['password'];
            foreach ($tables as $table) {
                Ext_Databases_Model::regionPool($table, $dsn, $user, $password, $action);
            }
        }
    }
}
