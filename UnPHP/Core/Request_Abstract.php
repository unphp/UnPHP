<?php
/**
 * 请求处理/分发（基础抽象类）
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
abstract class UnPHP_Request_Abstract
{

        protected $_method = "";
        protected $_default_module = null;
        protected $_default_controller = null;
        protected $_default_action = null;
        protected $_module = "";
        protected $_controller = "";
        protected $_action = "";
        protected $_params = array();
        protected $_language = "";
        protected $_base_uri = "";
        protected $_request_uri = "";
        protected $_dispatched = false; //是否已经“分发”
        protected $_routed = false;     //是否已经“路由”

        public function __construct()
        {
                $url = $_SERVER['HTTP_HOST'] . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
                $this->_request_uri = isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS'] ? 'http://' . $url : 'https://' . $url;
                $this->_base_uri = $_SERVER['REQUEST_URI'];
                $this->_method = $_SERVER['REQUEST_METHOD'];
        }
        
        public function setDefaultModule($name)
        {
                $this->_default_module = $name;
        }
        
        public function setDefaultController($name)
        {
                $this->_default_controller = $name;
        }
        
        public function setDefaultAction($name)
        {
                $this->_default_action = $name;
        }
        
        
        public function getDefaultModule()
        {
                return $this->_default_module;
        }
        
        public function getDefaultController()
        {
                return $this->_default_controller;
        }
        
        public function getDefaultAction()
        {
                return $this->_default_action;
        }
        

        public function getModuleName()
        {
                return $this->_module;
        }

        public function getControllerName()
        {
                return $this->_controller;
        }

        public function getActionName()
        {
                return $this->_action;
        }

        public function setModuleName($name)
        {
                $this->_module = $name;
        }

        public function setControllerName($name)
        {
                $this->_controller = $name;
        }
        

        public function setActionName($name)
        {
                $this->_action = $name;
        }

        public function getParams()
        {
                return $this->_params;
        }

        public function getParam($name, $dafault = NULL)
        {
                $param = $dafault;
                if (isset($this->_params[$name]))
                {
                        $param = $this->_params[$name];
                }
                return $param;
        }

        public function setParam($name, $value)
        {
                $this->_params[$name] = $value;
        }

        public function getMethod()
        {
                
        }

        public function getException()
        {
                
        }

        public function isDispatched()
        {
                return $this->_dispatched;
        }

        public function setDispatched()
        {
                $this->_dispatched = true;
        }

        public function isRouted()
        {
                return $this->_routed;
        }

        public function setRouted()
        {
                $this->_routed = true;
        }

        abstract public function getLanguage();

        abstract public function getQuery($name = NULL);

        abstract public function getPost($name = NULL);

        abstract public function getEnv($name = NULL);

        abstract public function getServer($name = NULL);

        abstract public function getCookie($name = NULL);

        abstract public function getFiles($name = NULL);

        abstract public function isGet();

        abstract public function isPost();

        abstract public function isHead();

        abstract public function isXmlHttpRequest();

        abstract public function isPut();

        abstract public function isDelete();

        abstract public function isOption();

        abstract public function isCli();
}
