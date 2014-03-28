<?php
/**
 * 控制器基类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
abstract class UnPHP_Controller_Abstract
{

        protected $_request = null;
        protected $_response;
        protected $_view;
        public $actions = array();

        public final function __construct($request,$view)
        {
                $this->_request = $request;
                $this->_view = $view;
<<<<<<< HEAD
        }

        public function init(){
                
        }

=======
        }

        public function init(){
                
        }

>>>>>>> be3ceb0d38a20221c5cabaf67a91af80f7ce34cf
        

        public function getModuleName()
        {
                return $this->_request->getModuleName();
        }

        public function getRequest()
        {
                return $this->_request;
        }

        public function getResponse()
        {
                
        }

        public function getView()
        {
            return $this->_view;
        }

}
