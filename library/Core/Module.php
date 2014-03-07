<?php
/**
 * 说明：项目模块基类。
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_Module
{
        
        protected $_request;
        
        protected $_response;

        public final function __construct($request,$response)
        {
                $this->_request = $request;
                $this->_response = $response;
                $this->init();
        }
        
        /**
         * 模块子类初始化方法。
         */
        public function init(){
                
        }
        
}

?>
