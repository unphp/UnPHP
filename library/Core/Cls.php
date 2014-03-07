<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Cls目录下逻辑处理类的基础类
 * 
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_Cls
{
        protected static $_instance;
        public static $err = null;
        public static $UC = null;

        
        protected function __construct()
        {
                self::$UC = Yaf_Registry::get('UC');
                self::$err = Yaf_Registry::get('Error');
        }

                
        /**
         * 静态实例方法
         * @param type $auth
         * @return type
         */
        public static function cls()
        {
                $class = get_called_class();
                if (!(self::$_instance instanceof $class))
                {
                        self::$_instance = new $class();
                }
                return self::$_instance;
        }

}

?>
