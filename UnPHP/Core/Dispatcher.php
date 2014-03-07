<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 应用调度分配
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
class UnPHP_Dispatcher
{

        private static $_instance = null;
        private $_request = null;
        private $_router = null;
        private $_view = null;
        private $_plugins = array();
        private $_default_module;
        private $_default_controller;
        private $_default_action;

        public function __construct()
        {
                ;
        }

        public function setRequest(UnPHP_Request_Abstract $request)
        {
                $this->_request = $request;
        }

        public function setView()
        {
                
        }

        public function setDefaultModule()
        {
                
        }

        public function setDefaultController()
        {
                
        }

        public function setDefaultAction()
        {
                
        }

        /**
         * 注册分发插件
         */
        public function registerPlugin(UnPHP_Plugin_Abstract $plugin)
        {
                
        }

        public function getRequest()
        {
                return $this->_request;
        }

        public function getRouter()
        {
                if (null === $this->_router)
                {
                        $this->_router = new UnPHP_Router();
                }
                return $this->_router;
        }

        public function returnResponse()
        {
                
        }

        public static function getInstance()
        {
                if (null == self::$_instance)
                {

                        self::$_instance = new self();
                }
                return self::$_instance;
        }

}
