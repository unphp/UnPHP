<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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

        public final function __construct($request)
        {
                $this->_request = $request;
        }

        public function getModuleName()
        {
                
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
                
        }

}
