<?php

/**
 * 说明：项目控制器基类。
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_Controller extends Yaf_Controller_Abstract
{
        /**
         * 是否关闭自动加载视图模板。
         * 默认情况下，关闭自动加载。
         * @var bool
         */
        protected $disable_view = true;

        /**
         * smarty模板引擎实例
         * @var type
         */
        public $smarty = null;

        /**
         * 初始化
         */
        protected final function init()
        {
                $this->initGlobal();
                $this->initLocal();
        }

        /**
         * 全局初始化
         */
        protected final function initGlobal()
        {
                // 初始视图
                if ($this->disable_view)
                {
                        Yaf_Dispatcher::getInstance()->disableView();
                }
                $this->initView();
                $this->smarty = $this->_view;
                $this->smarty->assign("STATIC",  Pub_Url::get_home_url().'/static');
                // 初始模块配置
        }
        
        /**
         * 应用池初始化
         */
        protected function initLocal()
        {
                
        }

}
?>
